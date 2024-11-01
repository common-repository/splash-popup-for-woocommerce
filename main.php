<?php
define( "BeRocket_splash_popup_domain", 'splash-popup-for-woocommerce'); 
define( "splash_popup_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
load_plugin_textdomain('splash-popup-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once(plugin_dir_path( __FILE__ ).'berocket/framework.php');
foreach (glob(__DIR__ . "/includes/*.php") as $filename)
{
    include_once($filename);
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class BeRocket_splash_popup extends BeRocket_Framework {
    public static $settings_name = 'br-splash_popup-options';
    public $info, $defaults, $values, $notice_array, $conditions;
    public $is_logged_in, $orders, $page_id, $current_page_id, $title;
    protected $plugin_version_capability = 15;
    public $close_js = '';
    protected static $instance;
    public static $error_log = array();
    public static $debug_mode = FALSE;
    protected $disable_settings_for_admin = array(
        array('script', 'js_page_load'),
        array('script', 'before_popup'),
        array('script', 'close_popup'),
    );
    protected $check_init_array = array(
        array(
            'check' => 'woocommerce_version',
            'data' => array(
                'version' => '3.0',
                'operator' => '>=',
                'notice'   => 'Plugin WooCommerce Terms and Conditions Popup required WooCommerce version 3.0 or higher'
            )
        ),
        array(
            'check' => 'framework_version',
            'data' => array(
                'version' => '2.1',
                'operator' => '>=',
                'notice'   => 'Please update all BeRocket plugins to the most recent version. WooCommerce Terms and Conditions Popup is not working correctly with older versions.'
            )
        ),
    );
    function __construct () {
        $this->info = array(
            'id'          => 15,
            'lic_id'      => 83,
            'version'     => BeRocket_splash_popup_version,
            'plugin'      => '',
            'slug'        => '',
            'key'         => '',
            'name'        => '',
            'plugin_name' => 'splash_popup',
            'full_name'   => 'WooCommerce Splash Popup',
            'norm_name'   => 'Splash Popup',
            'price'       => '',
            'domain'      => 'splash-popup-for-woocommerce',
            'templates'   => splash_popup_TEMPLATE_PATH,
            'plugin_file' => BeRocket_splash_popup_file,
            'plugin_dir'  => __DIR__,
        );
        $this->defaults = array(
            'display_popup_type'=> 'close',
            'not_logged_page'   => array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => ''),
            'logged_page'       => array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => ''),
            'customer_page'     => array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => ''),
            'not_logged_text'   => array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => ''),
            'logged_text'       => array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => ''),
            'customer_text'     => array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => ''),
            'open_timer'        => '0',
            'popup_width'       => '',
            'popup_height'      => '',
            'timer'             => '0',
            'expire'            => '7',
            'login_cookie'      => '1',
            'force_popup'       => '',
            'old_style_adding'  => '',
            'custom_css'        => '',
            'script'            => array(
                'js_page_load'      => '',
                'before_popup'      => '',
                'close_popup'       => '',
            ),
        );
        $this->values = array(
            'settings_name' => 'br-splash_popup-options',
            'option_page'   => 'br-splash_popup',
            'premium_slug'  => 'woocommerce-splash-popup',
            'free_slug'     => 'splash-popup-for-woocommerce',
            'hpos_comp'     => true
        );
        $this->feature_list = array();
        $this->active_libraries = array('addons', 'popup', 'templates');
        if( method_exists($this, 'include_once_files') ) {
            $this->include_once_files();
        }
        if ( $this->init_validation() ) {
            new BeRocket_splash_popup_post();
        }
        parent::__construct( $this );

        if ( $this->init_validation() ) {
            $last_version = get_option("berocket_{$this->info['plugin_name']}_version");
            if( $last_version === FALSE ) $last_version = 0;
            if ( version_compare($last_version, $this->info['version'], '<') ) {
                $this->update_from_older ( $last_version );
            }
            unset($last_version);
            $options = $this->get_option();
            
            if( $options['login_cookie'] ) {
                add_action( "wp_login", array ( $this, 'login_reset' ) );
                add_action( "wp_logout", array ( $this, 'login_reset' ) );
            }
            if ( ! is_admin() ) {
                add_action( 'wp_footer', array( $this, 'wp_enqueue_scripts' ), 1 );
            }
            add_filter ( 'BeRocket_updater_menu_order_custom_post', array($this, 'menu_order_custom_post') );
            add_filter('berocket_splash_popup_pages_contents', array($this, 'get_splash_popup_array'), 3);
            add_filter( 'the_content', array( $this, 'pre_get_posts' ), 1000 );
            add_action( 'wp_footer', array( $this, 'wp_footer' ), 1000 );
            if( class_exists('BeRocket_updater') && property_exists('BeRocket_updater', 'debug_mode') ) {
                self::$debug_mode = ! empty(BeRocket_updater::$debug_mode);
            }
            add_filter( 'BeRocket_updater_error_log', array( $this, 'add_error_log' ) );
        }
    }
    function init_validation() {
        return parent::init_validation() && ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && 
            br_get_woocommerce_version() >= 2.1 );
    }
    public function update_from_older($last_version) {
        global $wpdb;
        if( $last_version == 0 ) {
            $has_old_style = false;
            $search_string = 'a:6:{s:15:"not_logged_page";a:8:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";i:5;s:0:"";i:6;s:0:"";i:7;s:0:"";}s:15:"not_logged_text";a:8:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";i:5;s:0:"";i:6;s:0:"";i:7;s:0:"";}s:11:"logged_page";a:8:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";i:5;s:0:"";i:6;s:0:"";i:7;s:0:"";}s:11:"logged_text";a:8:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";i:5;s:0:"";i:6;s:0:"";i:7;s:0:"";}s:13:"customer_page";a:8:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";i:5;s:0:"";i:6;s:0:"";i:7;s:0:"";}s:13:"customer_text";a:8:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";i:5;s:0:"";i:6;s:0:"";i:7;s:0:"";}}';
            $query_string = "SELECT count(meta_key) as count FROM {$wpdb->postmeta} WHERE meta_key LIKE 'br-splash_popup-options' AND meta_value NOT LIKE '{$search_string}'";
            $matched_count = $wpdb->get_var( $query_string );
            $has_old_style = ($matched_count > 0);
            $options = $this->get_option();
            if( ! $has_old_style ) {
                if( isset($options['not_logged_page']) && is_array($options['not_logged_page']) ) {
                    foreach($options['not_logged_page'] as $page) {
                        if( ! empty($page) ) {
                            $has_old_style = true;
                        }
                    } 
                }
            }
            if( ! $has_old_style ) {
                if( isset($options['logged_page']) && is_array($options['logged_page']) ) {
                    foreach($options['logged_page'] as $page) {
                        if( ! empty($page) ) {
                            $has_old_style = true;
                        }
                    } 
                }
            }
            if( ! $has_old_style ) {
                if( isset($options['customer_page']) && is_array($options['customer_page']) ) {
                    foreach($options['customer_page'] as $page) {
                        if( ! empty($page) ) {
                            $has_old_style = true;
                        }
                    } 
                }
            }
            $options['addons'] = array('/deprecated_old_popup/deprecated_old_popup.php');
            update_option($this->values['settings_name'], $options);
            wp_cache_delete( $this->values[ 'settings_name' ], 'berocket_framework_option' );
        }
        update_option("berocket_{$this->info['plugin_name']}_version", $this->info['version']);
    }
    public function pre_get_posts($content) {
        $page_id = false;
        global $post, $wp_query;
        $wp_post = $wp_query;
        if( $wp_post->is_main_query() ) {
            if( function_exists('is_shop') && is_shop() ) {
                $page_id = get_option('woocommerce_shop_page_id');
            } elseif( $wp_post->is_single || $wp_post->is_page ) {
                $page_id = $post->ID;
            } else {
                $page_id = 'main';
            }
        }
        if( $page_id !== false ) {
            $this->current_page_id = $page_id;
        }
        return $content;
    }
    public function init () {
        parent::init();
    }
    public function set_styles() {
        parent::set_styles();
        if( is_woocommerce() ) {
            if( is_shop() ) {
                $this->current_page_id = get_option('woocommerce_shop_page_id');
            } elseif( is_product_taxonomy() ) {
                global $wp_query;
                $this->current_page_id = 't'.$wp_query->get_queried_object_id();
            }
        }
    }
    public function login_reset() {
        $option = $this->get_option();
        $expire_time = time()+60*60*24*$option['expire'];
        setcookie('br_popup', 'open', $expire_time, '/');
    }
    public function admin_settings( $tabs_info = array(), $data = array() ) {
        parent::admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                ),
                'Templates' => array(
                    'icon' => 'files-o',
                ),
                'Popups' => array(
                    'icon' => 'plus-square',
                    'link' => admin_url( 'edit.php?post_type=br_popups' ),
                ),
                'Addons' => array(
                    'icon' => 'plus',
                ),
                'CSS/JavaScript' => array(
                    'icon' => 'css3'
                ),
                'License' => array(
                    'icon' => 'unlock-alt',
                    'link' => admin_url( 'admin.php?page=berocket_account' )
                ),
            ),
            array(
            'General' => array(
                'display_popup_type' => array(
                    "label"     => __('Display popup type', 'splash-popup-for-woocommerce'),
                    "name"     => "display_popup_type",   
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => 'close', 'text' => __('Display popup one time', 'splash-popup-for-woocommerce')),
                        array('value' => 'each_content', 'text' => __('Display popup one time for each different content', 'splash-popup-for-woocommerce')),
                        array('value' => 'each_page', 'text' => __('Display popup one time for each page', 'splash-popup-for-woocommerce')),
                    ),
                    "value"    => 'close',
                ),
                'popup_width' => array(
                    "label"     => __('Popup Width', 'splash-popup-for-woocommerce'),
                    "type"      => "text",
                    "name"      => "popup_width",
                    "value"     => '1',
                ),
                'popup_height' => array(
                    "label"     => __('Popup Height', 'splash-popup-for-woocommerce'),
                    "type"      => "text",
                    "name"      => "popup_height",
                    "value"     => '1',
                ),
                'open_timer' => array(
                    "label"     => __('Timer', 'splash-popup-for-woocommerce'),
                    "label_for" => __('second(s)', 'splash-popup-for-woocommerce'),
                    "type"      => "number",
                    "name"      => "open_timer",
                    "value"     => '0',
                    "extra"     => 'min="0"',
                ),
                'timer' => array(
                    "label"     => __('Close Timer', 'splash-popup-for-woocommerce'),
                    "label_for" => __('second(s)', 'splash-popup-for-woocommerce'),
                    "type"      => "number",
                    "name"      => "timer",
                    "value"     => '0',
                    "extra"     => 'min="0"',
                ),
                'expire' => array(
                    "label"     => __('Cookie Expire', 'splash-popup-for-woocommerce'),
                    "label_for" => __('day(s)', 'splash-popup-for-woocommerce'),
                    "type"      => "number",
                    "name"      => "expire",
                    "value"     => '0',
                    "extra"     => 'min="-1"',
                ),
                'login_cookie' => array(
                    "label"     => __('Reset Cookie on Login / Logout', 'splash-popup-for-woocommerce'),
                    "type"      => "checkbox",
                    "name"      => "login_cookie",
                    "value"     => '1',
                ),
                'force_popup' => array(
                    "label"     => __('Force Popup', 'splash-popup-for-woocommerce'),
                    "type"      => "checkbox",
                    "name"      => "force_popup",
                    "value"     => '1',
                ),
            ),
            'CSS/JavaScript' => array(
                array(
                    "label"   => "Custom CSS",
                    "name"    => "custom_css",
                    "type"    => "textarea",
                    "value"   => "",
                ),
                array(
                    "label"   => 'JavaScript ' . __('On Page Load', 'splash-popup-for-woocommerce'),
                    "name"    => array("script", "js_page_load"),
                    "type"    => "textarea",
                    "value"   => "",
                ),
                array(
                    "label"   => 'JavaScript ' . __('Before Popup Open', 'splash-popup-for-woocommerce'),
                    "name"    => array("script", "before_popup"),
                    "type"    => "textarea",
                    "value"   => "",
                ),
                array(
                    "label"   => 'JavaScript ' . __('On Popup Close', 'splash-popup-for-woocommerce'),
                    "name"    => array("script", "close_popup"),
                    "type"    => "textarea",
                    "value"   => "",
                ),
            ),
            'Templates' => array(
                array(
                    'label' => '',
                    'section' => 'templates',
                )
            ),
            'Addons' => array(
                array(
                    'label' => '',
                    'section' => 'addons',
                )
            ),
        ) );
    }
    public function admin_init () {
        parent::admin_init();
        wp_enqueue_script( 'berocket_splash_popup_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_splash_popup_version );
        wp_register_style( 'berocket_splash_popup_admin_style', plugins_url( 'css/admin.css', __FILE__ ), "", BeRocket_splash_popup_version );
        wp_enqueue_style( 'berocket_splash_popup_admin_style' );
    }
    public function menu_order_custom_post($compatibility) {
        $compatibility['br_popups'] = 'br-splash_popup';
        return $compatibility;
    }
    public function wp_enqueue_scripts ($force = false) {
        $options = $this->get_option();
        $BeRocket_splash_popup_post = BeRocket_splash_popup_post::getInstance();
        $popups = $BeRocket_splash_popup_post->get_custom_posts_frontend();
        $plugin_options = $this->get_option();
        $timer  = (int)$plugin_options['timer'];
        $theme_template = '';
        if( ! empty($plugin_options['template']) ) {
            $template_data = $this->libraries->libraries_class['templates']->get_active_template_info($plugin_options['template']);
            $theme_template = $template_data['class'];
            do_action('berocket_init_template_'.$this->info['plugin_name'], $plugin_options['template']);
        }
        //SET POPUP GLOBAL SETTINGS
        $popup_options = array(
            'close_delay'      => '0',
            'theme'            => $theme_template,
            'hide_body_scroll' => empty( $options['hide_body_scroll'] ) ? false : true,
            'print_button'     => empty( $options['print_button'] ) ? false : true
        );
        if( ! empty($options['popup_width']) ) {
            if( is_numeric($options['popup_width']) ) {
                $options['popup_width'] = $options['popup_width'].'px';
            }
            $popup_options['width'] = $options['popup_width'];
        }
        if( ! empty($options['popup_height']) ) {
            if( is_numeric($options['popup_height']) ) {
                $options['popup_height'] = $options['popup_height'].'px';
            }
            $popup_options['height'] = $options['popup_height'];
        }
        if( ! empty($options['timer']) ) {
            $popup_options['close_delay'] = $options['timer'];
        }

        //GET TERMS AND CONDITIONS PAGE DATA
        $popup_pages = apply_filters('berocket_splash_popup_pages_contents', array(), $plugin_options);
        if( self::$debug_mode ) {
            self::$error_log['1_settings']      = $options;
            self::$error_log['2_popup_options'] = $popup_options;
            self::$error_log['3_popup_pages']   = $popup_pages;
        }
        if( empty($popup_pages) ) {
            return false;
        }
        //ADD POPUP TO THE PAGE
        foreach($popup_pages as $popup_id => $popup_page) {
            if( ! empty($popup_page['title']) || ! empty($popup_page['content']) ) {
                $temp_popup_options = array_merge($popup_options, $popup_page['popup_options']);
                $popup_id = BeRocket_popup_display::add_popup($temp_popup_options, $popup_page['content'], $popup_page['popup_open']);
                $this->close_js .= 'jQuery(document).on("br_popup-hide_popup", "#br_popup_'.$popup_id.'", function() {
                    jQuery.cookie( "splash", "'.$popup_page['new_cookie'].'", { expires: '.(int)$options['expire'].', path: "/" } );
                });';
            }
        }
        wp_enqueue_script( 'jquery-cookie', plugins_url( 'js/jquery.cookie.js', __FILE__ ), array( 'jquery' ) );
    }
    public function get_splash_popup_array($popup_pages) {
        //SET BASE OPTION
        $options = $this->get_option();
        $BeRocket_splash_popup_post = BeRocket_splash_popup_post::getInstance();
        $popups = $BeRocket_splash_popup_post->get_custom_posts_frontend();
        //GET CORRECT POPUP
        $new_cookie = '';
        foreach($popups as $popup_id) {
            $popup_data = $BeRocket_splash_popup_post->get_option($popup_id);
            if( ! empty($popup_data['data']) && ! BeRocket_conditions::check($popup_data['data'], $BeRocket_splash_popup_post->hook_name) ) {
                continue;
            }
            $new_cookie = $this->check_popup_is_closed($popup_id);
            if( ! empty($options['force_popup']) || $new_cookie !== FALSE ) {
                $page_id = $popup_id;
                break;
            }
        }
        
        $page_data = $this->get_page_content_array($page_id);
        $page_data['new_cookie'] = $new_cookie;
        $popup_pages['splash_popup_'.$page_id] = $page_data;
        return $popup_pages;
    }
    public function get_page_content_array(&$page_id = false) {
        //SET BASE OPTION
        $plugin_options = $this->get_option();
        $open_timer = (int)$plugin_options['open_timer'] * 1000;
        $timer      = (int)$plugin_options['timer'];
        $BeRocket_splash_popup_post = BeRocket_splash_popup_post::getInstance();
        $page_content = array('title' => '', 'content' => '', 'page' => false, 'popup_options' => array('close_delay' => $timer), 'popup_open' => array('page_open' => array('type' => 'page_open', 'timer' => $open_timer)));
        //GET DATA FROM POPUP
        if( ! empty( $page_id ) && $page_id > 0 ) {
            $page = get_post( $page_id );
            $popup_data = $BeRocket_splash_popup_post->get_option($page_id);
            $page_content['page'] = $page;
            $content = $page->post_content;
            $content = apply_filters( 'br_terms_cond_the_content', $content );
            $content = $this->convert_content($content);
            $page_content['content'] = $content;
            $page_content['title'] = $page->post_title;
            $page_content['popup_options']['title'] = $page->post_title;
            if( isset($popup_data['timer']) && $popup_data['timer'] != '' ) {
                $page_content['popup_options']['close_delay'] = (int)$popup_data['timer'];
            }
            if( isset($popup_data['open_timer']) && $popup_data['open_timer'] != '' ) {
                $page_content['popup_open']['page_open']['timer'] = (int)$popup_data['open_timer'] * 1000;
            }
            if( isset($popup_data['popup_width']) && $popup_data['popup_width'] != '' ) {
                if( is_numeric($popup_data['popup_width']) ) {
                    $popup_data['popup_width'] = $popup_data['popup_width'].'px';
                }
                $page_content['popup_options']['width'] = $popup_data['popup_width'];
            }
            if( isset($popup_data['popup_height']) && $popup_data['popup_height'] != '' ) {
                if( is_numeric($popup_data['popup_height']) ) {
                    $popup_data['popup_height'] = $popup_data['popup_height'].'px';
                }
                $page_content['popup_options']['height'] = $popup_data['popup_height'];
            }
        }
        return $page_content;
    }
    public function check_popup_is_closed($page_id) {
        $options = $this->get_option();
        $cookie = br_get_value_from_array($_COOKIE, array("splash"));
        $new_cookie = $cookie;
        $hide_popup = false;
        if( in_array($options['display_popup_type'], array('each_content', 'each_page')) ) {
            if( ! $cookie || $cookie == 'open' || $cookie == 'close' ) {
                $new_cookie = array();
            } else {
                $new_cookie = explode( ',', $cookie );
            }
            if( $options['display_popup_type'] == 'each_page' ) {
                if( empty($this->current_page_id) ) {
                    $page_id = 'main';
                } else {
                    $page_id = $this->current_page_id;
                }
            }
            if( in_array( $page_id, $new_cookie ) ) {
                $hide_popup = true;
            } else {
                $new_cookie[] = $page_id;
                $new_cookie = array_filter ( $new_cookie );
            }
            $new_cookie = implode( ',', $new_cookie );
        } else {
            if( $cookie == 'close' ) {
                $hide_popup = true;
            } else {
                $new_cookie = 'close';
            }
        }
        if( $hide_popup ) {
            return false;
        } else {
            return $new_cookie;
        }
    }
    public function convert_content($post_content) {
        global $wp_embed;
        $post_content = do_blocks($post_content);
        $post_content = $wp_embed->run_shortcode($post_content);
        $post_content = do_shortcode($post_content);
        $post_content = $wp_embed->autoembed($post_content);
        $post_content = wptexturize($post_content);
        $post_content = wpautop($post_content);
        $post_content = shortcode_unautop($post_content);
        $post_content = prepend_attachment($post_content);
        $wp_filter_content_tags = function_exists('wp_filter_content_tags') ? 'wp_filter_content_tags' : 'wp_make_content_images_responsive';
        $post_content = $wp_filter_content_tags($post_content);
        $post_content = convert_smilies($post_content);
        return $post_content;
    }
    public function wp_footer() {
        echo '<script type="text/javascript">
        ' . $this->close_js . '
        </script>';
    }
    public function add_error_log( $error_log ) {
        $error_log[plugin_basename( __FILE__ )] =  self::$error_log;
        return $error_log;
    }
}

new BeRocket_splash_popup;
