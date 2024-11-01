<?php
class BeRocket_splash_popup_deprecated extends BeRocket_plugin_variations  {
    public $plugin_name = 'splash_popup';
    public $version_number = 5;
    public $is_logged_in, $orders, $page_id, $current_page_id, $title;
    function __construct() {
        $this->info = array(
            'id'          => 15,
            'lic_id'      => 83,
            'version'     => BeRocket_splash_popup_version,
            'plugin_name' => 'splash_popup',
            'templates'   => splash_popup_TEMPLATE_PATH,
            'plugin_file' => BeRocket_splash_popup_file,
        );
        $this->values = array(
            'settings_name' => 'br-splash_popup-options',
            'option_page'   => 'br-splash_popup',
            'premium_slug'  => 'woocommerce-splash-popup',
            'free_slug'     => 'splash-popup-for-woocommerce',
        );
        $this->default = array();
        parent::__construct();
        add_action( 'wp_head', array( $this, 'set_styles' ) );
        add_action( "init", array ( $this, 'splash_popup' ) );
        add_action( "admin_init", array ( $this, 'admin_init' ) );
        add_filter('brfr_splash_popup_popup_pages', array($this, 'section_popup_pages'), $this->version_number, 3);
        add_filter( 'berocket_splash_popup_page_id', array($this, 'old_style_pages') );
        add_filter( 'berocket_splash_popup_page_title', array($this, 'old_style_title') );
        add_action( 'save_post', array( $this, 'wc_save_product' ) );
        add_filter( 'wp_head', array( $this, 'get_current_page' ), 1000 );
        add_filter('berocket_splash_popup_pages_contents', array($this, 'splash_popup_pages_contents'), 99999);
    }
    public function splash_popup_pages_contents() {
        return array();
    }
    public function get_current_page($force = false) {
        if( ! empty($this->current_page_id) && ! $force ) {
            return $this->current_page_id;
        }
        if( is_shop() ) {
            $this->current_page_id = 'shop';
            return;
        }
        $queried_object = get_queried_object();
        $queried_object_id = get_queried_object_id();
        $page_types = get_option('BeRocket_splash_popup_page_types');
        if( ! is_array($page_types) ) {
            $page_types = array();
        }
        if( is_object($queried_object) ) {
            $current_type = get_class($queried_object);
        } else {
            $current_type = 'main';
        }
        if( ! in_array($current_type, $page_types) ) {
            $page_types[] = $current_type;
        }
        update_option('BeRocket_splash_popup_page_types', $page_types);
        $type_id = array_search($current_type, $page_types);
        $this->current_page_id = $type_id.'_'.$queried_object_id;
        return $type_id.'_'.$queried_object_id;
    }
    function admin_init() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
    }
    public function add_meta_boxes() {
        add_meta_box( 'br_splash_popup', __( 'Splash popup', 'splash-popup-for-woocommerce' ), array( $this, 'meta_box_settings' ), array('page', 'post', 'product'), 'normal', 'high' );
    }
    public function meta_box_settings($post) {
        set_query_var( 'meta_post', $post );
        $BeRocket_splash_popup = BeRocket_splash_popup::getInstance();
        $BeRocket_splash_popup->br_get_template_part("pages");
    }
    function settings_tabs($tabs) {
        $tabs = berocket_insert_to_array($tabs, 'General', array(
            'Styles' => array(
                'icon' => 'eye',
            ),
        ));
        return $tabs;
    }
    function settings_page($data) {
        $data['Styles'] = array(
            'height_paddings' => array(
                "label"     => __('Height paddings', 'splash-popup-for-woocommerce'),
                "type"      => "text",
                "name"      => array('styles', 'height_paddings'),
                "value"     => '',
            ),
            'width_paddings' => array(
                "label"     => __('Width paddings', 'splash-popup-for-woocommerce'),
                "type"      => "text",
                "name"      => array('styles', 'width_paddings'),
                "value"     => '',
            ),
            'border_width' => array(
                "label"     => __('Border width', 'splash-popup-for-woocommerce'),
                "type"      => "number",
                "name"      => array('styles', 'border_width'),
                "value"     => '',
            ),
            'border_color' => array(
                "label"     => __('Border color', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'border_color'),
                "value"     => -1,
            ),
            'back_color' => array(
                "label"     => __('Background color', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'back_color'),
                "value"     => -1,
            ),
            'title_back_color' => array(
                "label"     => __('Title background color', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'title_back_color'),
                "value"     => -1,
            ),
            'title_font_color' => array(
                "label"     => __('Title font color', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'title_font_color'),
                "value"     => -1,
            ),
            'title_font_size' => array(
                "label"     => __('Title font size', 'splash-popup-for-woocommerce'),
                "type"      => "number",
                "name"      => array('styles', 'title_font_size'),
                "value"     => '',
            ),
            'title_height' => array(
                "label"     => __('Title height', 'splash-popup-for-woocommerce'),
                "type"      => "number",
                "name"      => array('styles', 'title_height'),
                "value"     => '',
            ),
            'close_size' => array(
                "label"     => __('Close button size', 'splash-popup-for-woocommerce'),
                "type"      => "number",
                "name"      => array('styles', 'close_size'),
                "value"     => '',
            ),
            'close_color' => array(
                "label"     => __('Close button color', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'close_color'),
                "value"     => -1,
            ),
            'close_color_hover' => array(
                "label"     => __('Close button color on mouse over', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'close_color_hover'),
                "value"     => -1,
            ),
            'content_back_color' => array(
                "label"     => __('Content background color', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'content_back_color'),
                "value"     => -1,
            ),
            'content_font_color' => array(
                "label"     => __('Content font color', 'splash-popup-for-woocommerce'),
                "type"      => "color",
                "name"      => array('styles', 'content_font_color'),
                "value"     => -1,
            ),
        );
        $data['General']['autoselector_set'] = array(
            "section"   => "popup_pages",
            "value"     => "",
        );
        return $data;
    }
    public function section_popup_pages($html, $item, $options) {
        do_action('BeRocket_wizard_javascript');
        $html = '<tr><th>';
        $pages = get_pages();
        $week_days = array(
            '0' => __('All', 'splash-popup-for-woocommerce'),
            '1' => __('Monday', 'splash-popup-for-woocommerce'),
            '2' => __('Tuesday', 'splash-popup-for-woocommerce'),
            '3' => __('Wednesday', 'splash-popup-for-woocommerce'),
            '4' => __('Thursday', 'splash-popup-for-woocommerce'),
            '5' => __('Friday', 'splash-popup-for-woocommerce'),
            '6' => __('Saturday', 'splash-popup-for-woocommerce'),
            '7' => __('Sunday', 'splash-popup-for-woocommerce'),
        );
        
        foreach($week_days as $week_id => $week_day) {
            $html .= '<a class="br_week_select br_week_select_'.$week_id.($week_id == 0 ? ' active' : '').'" href="#'.$week_day.'" data-id="'.$week_id.'">'.$week_day.'</a>';
        }
        $html .= '</th>
            <td>';
        foreach($week_days as $week_id => $week_day) {
            $html .= '<div class="br_week_block br_week_block_'.$week_id.($week_id == 0 ? ' active' : '').'">';
            $html .= '<p>';
            $html .= '<h4>' .__('Not Logged In Page', 'splash-popup-for-woocommerce').'</h4>';
            $html .= '<span style="width: 70px;">' . __('Page', 'splash-popup-for-woocommerce') . '</span>
            <select name="br-splash_popup-options[not_logged_page]['.$week_id.']">
                <option value=""></option>';
                foreach ( $pages as $page ) {
                    $html .= '<option value="'.$page->ID.'"'.( ( @ $options['not_logged_page'][$week_id] == $page->ID ) ? ' selected' : '' ).'>'.$page->post_title.'</option>';
                }
            $html .= '</select>
            <span style="width: 70px;">' . __('Title', 'splash-popup-for-woocommerce') . '</span>
            <input type="text" name="br-splash_popup-options[not_logged_text][' . $week_id . ']" value="' . @ $options['not_logged_text'][$week_id] . '">
            </p><p>';
            $html .= '<h4>' .__('Logged In Page', 'splash-popup-for-woocommerce').'</h4>';
            $html .= '<span style="width: 70px;">' . __('Page', 'splash-popup-for-woocommerce') . '</span>
            <select name="br-splash_popup-options[logged_page][' . $week_id . ']">
                <option value=""></option>';
                foreach ( $pages as $page ) {
                    $html .= '<option value="'.$page->ID.'"'.( ( @ $options['logged_page'][$week_id] == $page->ID ) ? ' selected' : '' ).'>'.$page->post_title.'</option>';
                }
            $html .= '</select>
            <span style="width: 70px;">' . __('Title', 'splash-popup-for-woocommerce') . '</span>
            <input type="text" name="br-splash_popup-options[logged_text][' . $week_id . ']" value="' . @ $options['logged_text'][$week_id] . '">
            </p><p>';
            $html .= '<h4>' .__('Logged In Customers Page', 'splash-popup-for-woocommerce').'</h4>';
            $html .= '<span style="width: 70px;">' . __('Page', 'splash-popup-for-woocommerce') . '</span>
            <select name="br-splash_popup-options[customer_page][' . $week_id . ']">
                <option value=""></option>';
                foreach ( $pages as $page ) {
                    $html .= '<option value="'.$page->ID.'"'.( ( @ $options['customer_page'][$week_id] == $page->ID ) ? ' selected' : '' ).'>'.$page->post_title.'</option>';
                }
            $html .= '</select>
            <span style="width: 70px;">' . __('Title', 'splash-popup-for-woocommerce') . '</span>
            <input type="text" name="br-splash_popup-options[customer_text][' . $week_id . ']" value="' . @ $options['customer_text'][$week_id] . '">
            </p>
            </div>';
        }
        $html .= '   </td>
        </tr>';
        return $html;
    }
    public function set_styles() {
        $BeRocket_splash_popup = BeRocket_splash_popup::getInstance();
        $options = $BeRocket_splash_popup->get_option();
        echo '<style>';
        echo '.br_splash_popup_window {';
        if(@ $options['styles']['border_width']) {
            echo 'border-width:'.$options['styles']['border_width'].'px;';
        }
        if(@ $options['styles']['border_color']) {
            echo 'border-color:'.($options['styles']['border_color'][0] != '#' ? '#' : '').$options['styles']['border_color'].';';
        }
        echo 'border-style:solid;}';
        echo '.br_splash_popup_window_bg{';
        if(@ $options['styles']['back_color']) {
            echo 'background-color:'.($options['styles']['back_color'][0] != '#' ? '#' : '').$options['styles']['back_color'].'!important;';
        }
        echo '}';
        echo '.br_splash_popup_window #TB_title {';
        if(@ $options['styles']['back_color']) {
            echo 'background-color:'.($options['styles']['title_back_color'][0] != '#' ? '#' : '').$options['styles']['title_back_color'].'!important;';
        }
        if(@ $options['styles']['back_color']) {
            echo 'color:'.($options['styles']['title_font_color'][0] != '#' ? '#' : '').$options['styles']['title_font_color'].'!important;';
        }
        if(@ $options['styles']['title_height']) {
            echo 'height:'.$options['styles']['title_height'].'px;';
        }
        if(@ $options['styles']['title_font_size']) {
            echo 'font-size:'.$options['styles']['title_font_size'].'px;';
            echo 'line-height:'.$options['styles']['title_font_size'].'px;';
        }
        echo '}';
        echo '.br_splash_popup_window #TB_title #TB_ajaxWindowTitle{line-height: inherit;';
        if(@ $options['styles']['close_size']) {
            echo 'width: calc(100% - '.($options['styles']['close_size']*1.25).'px)!important;';
        }
        echo '}';
        echo '.br_splash_popup_window #TB_title #TB_closeWindowButton{';
        if(@ $options['styles']['close_size']) {
            echo 'height:'.$options['styles']['close_size'].'px!important;';
            echo 'width:'.$options['styles']['close_size'].'px!important;';
            echo 'line-height:'.$options['styles']['close_size'].'px!important;';
        }
        echo '}';
        echo '.br_splash_popup_window #TB_title #TB_closeWindowButton,
        .br_splash_popup_window #TB_title #TB_closeWindowButton .tb-close-icon,
        .br_splash_popup_window #TB_title #TB_closeWindowButton .tb-close-icon:before,
        .br_splash_popup_window #TB_title .br_timer{';
        if(@ $options['styles']['close_size']) {
            echo 'height:'.$options['styles']['close_size'].'px!important;';
            echo 'width:'.$options['styles']['close_size'].'px!important;';
            echo 'line-height:'.$options['styles']['close_size'].'px!important;';
            echo 'font-size:'.$options['styles']['close_size'].'px!important;';
        }
        if(@ $options['styles']['close_color']) {
            echo 'color:'.($options['styles']['close_color'][0] != '#' ? '#' : '').$options['styles']['close_color'].'!important;';
        }
        echo '}';
        echo '.br_splash_popup_window #TB_title #TB_closeWindowButton:hover,
        .br_splash_popup_window #TB_title #TB_closeWindowButton:hover .tb-close-icon,
        .br_splash_popup_window #TB_title #TB_closeWindowButton:hover .tb-close-icon:before,
        .br_splash_popup_window #TB_title #TB_closeWindowButton .tb-close-icon:hover,
        .br_splash_popup_window #TB_title #TB_closeWindowButton .tb-close-icon:hover:before{';
        if(@ $options['styles']['close_color_hover']) {
            echo 'color:'.($options['styles']['close_color_hover'][0] != '#' ? '#' : '').$options['styles']['close_color_hover'].'!important;';
        }
        echo '}';
        echo '.br_splash_popup_window #TB_ajaxContent {width: initial!important;';
        if(@ $options['styles']['content_back_color']) {
            echo 'background-color:'.($options['styles']['content_back_color'][0] != '#' ? '#' : '').$options['styles']['content_back_color'].'!important;';
        }
        if(@ $options['styles']['content_font_color']) {
            echo 'color:'.($options['styles']['content_font_color'][0] != '#' ? '#' : '').$options['styles']['content_font_color'].'!important;';
        }
        echo '}';
        echo '</style>';
    }
    public function wp_enqueue_scripts() {
        $BeRocket_splash_popup = BeRocket_splash_popup::getInstance();
        $options = $BeRocket_splash_popup->get_option();
        wp_enqueue_script("jquery");
        wp_enqueue_script( 'berocket_splash_popup_main', 
            plugins_url( 'frontend.js', __FILE__ ), 
            array( 'jquery' ), 
            BeRocket_splash_popup_version );

        wp_localize_script(
            'berocket_splash_popup_main',
            'the_splash_popup_js_data',
            array(
                'script' => apply_filters( 'berocket_splash_popup_user_func', $options['script'] ),
                'expire' => $options['expire'],
                'timer'  => (int)$options['timer'],
                'open_timer' => (int)$options['open_timer'],
                'styles' => array(
                    'height_paddings' => @ $options['styles']['height_paddings'],
                    'width_paddings'  => @ $options['styles']['width_paddings']
                ),
            )
        );
        return false;
    }
    public function splash_popup() {
        $BeRocket_splash_popup = BeRocket_splash_popup::getInstance();
        $options = $BeRocket_splash_popup->get_option();
        add_filter( 'the_content', array( $this, 'pre_get_posts' ), 1000 );
        $orders = get_posts( array(
            'meta_key'    => '_customer_user',
            'meta_value'  => get_current_user_id(),
            'post_type'   => 'shop_order',
            'post_status' => array( 'wc-processing', 'wc-completed' ),
        ) );
        $is_logged_in = is_user_logged_in();
        $page_id = '';
        if( ! $is_logged_in ) {
            $page_id = @ $options['not_logged_page'][0];
            $title = @ $options['not_logged_text'][0];
            if( isset($options['not_logged_page'][date('N')]) && $options['not_logged_page'][date('N')] ) {
                $page_id = @ $options['not_logged_page'][date('N')];
                $title = @ $options['not_logged_text'][date('N')];
            }
        } elseif( $orders ) {
            $page_id = @ $options['customer_page'][0];
            $title = @ $options['customer_text'][0];
            if( isset($options['customer_page'][date('N')]) && $options['customer_page'][date('N')] ) {
                $page_id = @ $options['customer_page'][date('N')];
                $title = @ $options['customer_text'][date('N')];
            }
        } else {
            $page_id = @ $options['logged_page'][0];
            $title = @ $options['logged_text'][0];
            if( isset($options['logged_page'][date('N')]) && $options['logged_page'][date('N')] ) {
                $page_id = @ $options['logged_page'][date('N')];
                $title = @ $options['logged_text'][date('N')];
            }
        }
        $this->is_logged_in = $is_logged_in;
        $this->orders = $orders;
        $this->page_id = $page_id;
        if( ! isset( $title ) || ! $title ) {
            $title = get_the_title($page_id);
            $title = apply_filters( 'the_title', $title );
        }
        $this->title = $title;
        $this->wp_enqueue_scripts();
        add_action( "wp_footer", array ( $this, 'wp_footer_popup' ) );
        add_thickbox();
    }
    public function wp_footer_popup() {
        $BeRocket_splash_popup = BeRocket_splash_popup::getInstance();
        $options = $BeRocket_splash_popup->get_option();
        set_query_var( 'BeRocket_splash_popup_option', $options );
        $BeRocket_splash_popup->br_get_template_part("wp_footer_popup");
    }
    public function old_style_title($title) {
        if( ! empty($this->title) ) {
            $title = $this->title;
        }
        return $title;
    }
    public function old_style_pages($page_id) {
        if( $page_id == 0 ) {
            $BeRocket_splash_popup = BeRocket_splash_popup::getInstance();
            $page_id = $this->page_id;
            $title = $this->title;
            if( ! $this->current_page_id ) {
                $this->pre_get_posts('');
            }
            $post_own_popup = false;
            if( $this->current_page_id > 0 ) {
                $options = get_post_meta( $this->current_page_id, 'br-splash_popup-options', true );
                if( $options ) {
                    if( ! $this->is_logged_in ) {
                        if( ! empty($options['not_logged_page'][0]) ) {
                            $page_id = @ $options['not_logged_page'][0];
                            $title = @ $options['not_logged_text'][0];
                            if( isset($options['not_logged_page'][date('N')]) && $options['not_logged_page'][date('N')] ) {
                                $page_id = @ $options['not_logged_page'][date('N')];
                                $title = @ $options['not_logged_text'][date('N')];
                            }
                            if( ! isset( $title ) || ! $title ) {
                                $title = get_the_title($page_id);
                                $title = apply_filters( 'the_title', $title );
                            }
                            $post_own_popup = true;
                        }
                    } elseif( $this->orders ) {
                        if( ! empty($options['customer_page'][0]) ) {
                            $page_id = @ $options['customer_page'][0];
                            $title = @ $options['customer_text'][0];
                            if( isset($options['customer_page'][date('N')]) && $options['customer_page'][date('N')] ) {
                                $page_id = @ $options['customer_page'][date('N')];
                                $title = @ $options['customer_text'][date('N')];
                            }
                            if( ! isset( $title ) || ! $title ) {
                                $title = get_the_title($page_id);
                                $title = apply_filters( 'the_title', $title );
                            }
                            $post_own_popup = true;
                        }
                    } else {
                        if( ! empty($options['logged_page'][0]) ) {
                            $page_id = @ $options['logged_page'][0];
                            $title = @ $options['logged_text'][0];
                            if( isset($options['logged_page'][date('N')]) && $options['logged_page'][date('N')] ) {
                                $page_id = @ $options['logged_page'][date('N')];
                                $title = @ $options['logged_text'][date('N')];
                            }
                            if( ! isset( $title ) || ! $title ) {
                                $title = get_the_title($page_id);
                                $title = apply_filters( 'the_title', $title );
                            }
                            $post_own_popup = true;
                        }
                    }
                    if( $page_id == 'remove' ) {
                        $page_id = '';
                    }
                }
            }
            $this->title = $title;
            set_query_var( 'title', $title );
        }
        return $page_id;
    }
    public function wc_save_product( $product_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['br-splash_popup-options'] ) ) {
            update_post_meta( $product_id, 'br-splash_popup-options', $_POST['br-splash_popup-options'] );
        } else {
            delete_post_meta( $product_id, 'br-splash_popup-options' );
        }
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
            }
        }
        if( $page_id !== false ) {
            $this->current_page_id = $page_id;
        }
        return $content;
    }
}
new BeRocket_splash_popup_deprecated();
