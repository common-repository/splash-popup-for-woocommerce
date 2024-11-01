<?php
class BeRocket_conditions_splash_popup extends BeRocket_conditions {
    public static function get_conditions() {
        $conditions = parent::get_conditions();
        $conditions['condition_week_day'] = array(
            'func' => 'check_condition_week_day',
            'type' => 'week_day',
            'name' => __('Week Day', 'splash-popup-for-woocommerce')
        );
        $conditions['condition_user_status'] = array(
            'func' => 'check_condition_user_status',
            'type' => 'user_status',
            'name' => __('User Status', 'splash-popup-for-woocommerce')
        );
        return $conditions;
    }
    public static function check_condition_product($show, $condition, $additional) {
        $additional['product_id'] = get_queried_object_id();
        $show = parent::check_condition_product($show, $condition, $additional);
        return $show;
    }
    public static function condition_page_id($html, $name, $options) {
        $def_options = array('pages' => array());
        $options = array_merge($def_options, $options);
        $html .= br_supcondition_equal($name, $options);
        $pages = get_pages();
        $html .= '<div style="max-height:150px;overflow:auto;border:1px solid #ccc;padding: 5px;">';
        $woo_pages = array(
            'thank_you'     => '[THANK YOU]',
            'shop'          => '[SHOP PAGE]',
            'product'       => '[PRODUCT PAGE]',
            'category'      => '[PRODUCT CATEGORY PAGE]',
            'taxonomies'    => '[PRODUCT TAXONOMIES]',
            'tags'          => '[PRODUCT TAGS]',
        );
        foreach($woo_pages as $page_id => $page_name) {
            $html .= '<div><label><input name="' . $name . '[pages][]" type="checkbox" value="' . $page_id . '"'.(in_array($page_id, $options['pages']) ? ' checked' : '').'>' . $page_name . '</label></div>';
        }
        foreach($pages as $page) {
            $html .= '<div><label><input name="' . $name . '[pages][]" type="checkbox" value="'.$page->ID.'"'.(in_array($page->ID, $options['pages']) ? ' checked' : '').'>'.$page->post_title.' (ID: '.$page->ID.')</label></div>';
        }
        $html .= '</div>';
        return $html;
    }
    public static function check_condition_page_id($show, $condition, $additional) {
        $def_options = array('pages' => array());
        $condition = array_merge($def_options, $condition);
        if( ($thank_you = array_search('thank_you', $condition['pages'])) !== FALSE ) {
            unset($condition['pages'][$thank_you]);
            if( is_checkout() && get_query_var('order-received') ) {
                return true;
            }
        }
        $show = parent::check_condition_page_id($show, $condition, $additional);
        return $show;
    }
    public static function condition_week_day($html, $name, $options) {
        $def_options = array('day1' => '', 'day2' => '', 'day3' => '', 'day4' => '', 'day5' => '', 'day6' => '', 'day7' => '');
        $options = array_merge($def_options, $options);
        $html .= '<p>
            <label><input type="checkbox" name="'.$name.'[day1]"'.(empty($options['day1']) ? '' : ' checked').'>'.__('Monday', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[day2]"'.(empty($options['day2']) ? '' : ' checked').'>'.__('Tuesday', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[day3]"'.(empty($options['day3']) ? '' : ' checked').'>'.__('Wednesday', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[day4]"'.(empty($options['day4']) ? '' : ' checked').'>'.__('Thursday', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[day5]"'.(empty($options['day5']) ? '' : ' checked').'>'.__('Friday', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[day6]"'.(empty($options['day6']) ? '' : ' checked').'>'.__('Saturday', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[day7]"'.(empty($options['day7']) ? '' : ' checked').'>'.__('Sunday', 'splash-popup-for-woocommerce').'</label>
        </p>';
        return $html;
    }
    public static function check_condition_week_day($show, $condition, $additional) {
        $week_day = date('N');
        $show = ! empty($condition['day'.$week_day]);
        return $show;
    }
    public static function condition_user_status($html, $name, $options) {
        $def_options = array('not_logged_page' => '', 'customer_page' => '', 'logged_page' => '');
        $options = array_merge($def_options, $options);
        $html .= '<p>
            <label><input type="checkbox" name="'.$name.'[not_logged_page]"'.(empty($options['not_logged_page']) ? '' : ' checked').'>'.__('Not Logged In', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[customer_page]"'.(empty($options['customer_page']) ? '' : ' checked').'>'.__('Logged In Customers', 'splash-popup-for-woocommerce').'</label>
            <label><input type="checkbox" name="'.$name.'[logged_page]"'.(empty($options['logged_page']) ? '' : ' checked').'>'.__('Logged In', 'splash-popup-for-woocommerce').'</label>
        </p>';
        return $html;
    }
    public static function check_condition_user_status($show, $condition, $additional) {
        $orders = get_posts( array(
            'meta_key'    => '_customer_user',
            'meta_value'  => get_current_user_id(),
            'post_type'   => 'shop_order',
            'post_status' => array( 'wc-processing', 'wc-completed' ),
        ) );
        $is_logged_in = is_user_logged_in();
        if( ! $is_logged_in ) {
            $show = ! empty($condition['not_logged_page']);
        } elseif( $orders ) {
            $show = ! empty($condition['customer_page']);
        } else {
            $show = ! empty($condition['logged_page']);
        }
        return $show;
    }
}
class BeRocket_splash_popup_post extends BeRocket_custom_post_class {
    public $hook_name = 'berocket_splash_popup_post';
    public $conditions;
    public $post_type_parameters = array(
        'sortable' => true,
        'can_be_disabled' => true
    );
    protected static $instance;
    function __construct() {
        add_action('splash_popup_framework_construct', array($this, 'init_conditions'));
        $this->post_name = 'br_popups';
        $this->post_settings = array(
            'label' => __( 'Popups', 'splash-popup-for-woocommerce' ),
            'labels' => array(
                'name'               => __( 'Popups', 'splash-popup-for-woocommerce' ),
                'singular_name'      => __( 'Popup', 'splash-popup-for-woocommerce' ),
                'menu_name'          => _x( 'Popups', 'Admin menu name', 'splash-popup-for-woocommerce' ),
                'add_new'            => __( 'Add Popup', 'splash-popup-for-woocommerce' ),
                'add_new_item'       => __( 'Add New Popup', 'splash-popup-for-woocommerce' ),
                'edit'               => __( 'Edit', 'splash-popup-for-woocommerce' ),
                'edit_item'          => __( 'Edit Popup', 'splash-popup-for-woocommerce' ),
                'new_item'           => __( 'New Popup', 'splash-popup-for-woocommerce' ),
                'view'               => __( 'View Popups', 'splash-popup-for-woocommerce' ),
                'view_item'          => __( 'View Popup', 'splash-popup-for-woocommerce' ),
                'search_items'       => __( 'Search Popups', 'splash-popup-for-woocommerce' ),
                'not_found'          => __( 'No Popups found', 'splash-popup-for-woocommerce' ),
                'not_found_in_trash' => __( 'No Popups found in trash', 'splash-popup-for-woocommerce' ),
            ),
            'description'     => __( 'This is where you can add Popups.', 'splash-popup-for-woocommerce' ),
            'public'          => true,
            'show_ui'         => true,
            'capability_type' => 'product',
            'map_meta_cap'    => true,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_in_menu'        => 'berocket_account',
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => false,
            'supports'            => array( 'title', 'editor' ),
            'show_in_nav_menus'   => false,
        );
        $this->default_settings = array(
            'data'          => array(),
            'timer'         => '',
            'open_timer'    => '',
        );
        $this->add_meta_box('conditions', __( 'Conditions', 'splash-popup-for-woocommerce' ));
        $this->add_meta_box('settings', __( 'Popup Settings', 'splash-popup-for-woocommerce' ));
        //$this->add_meta_box('meta_box_shortcode', __( 'Shortcode', 'splash-popup-for-woocommerce' ), false, 'side');
        //$this->add_meta_box('information_faq', __( 'FAQ', 'splash-popup-for-woocommerce' ), false, 'side');
        parent::__construct();
    }
    public function init_conditions() {
        $this->conditions = new BeRocket_conditions_splash_popup($this->post_name.'[data]', $this->hook_name, array(
            'condition_page_id',
            'condition_page_woo_attribute',
            'condition_page_woo_search',
            'condition_page_woo_category',
            'condition_product',
            'condition_week_day',
            'condition_user_status',
        ));
    }
    public function conditions($post) {
        $options = $this->get_option( $post->ID );
        echo $this->conditions->build($options['data']);
        ?>
        <div>
            <table>
                <tr>
                    <th><?php _e('Hide this group on:', 'splash-popup-for-woocommerce'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" value="1" name="<?php echo $this->post_name; ?>[is_hide_mobile]"<?php if( ! empty($options['is_hide_mobile']) ) echo ' checked'; ?>>
                            <?php _e('Mobile', 'splash-popup-for-woocommerce'); ?>
                        </label>
                        <label>
                            <input type="checkbox" value="1" name="<?php echo $this->post_name; ?>[hide_group][tablet]"<?php if( ! empty($options['hide_group']['tablet']) ) echo ' checked'; ?>>
                            <?php _e('Tablet', 'splash-popup-for-woocommerce'); ?>
                        </label>
                        <label>
                            <input type="checkbox" value="1" name="<?php echo $this->post_name; ?>[hide_group][desktop]"<?php if( ! empty($options['hide_group']['desktop']) ) echo ' checked'; ?>>
                            <?php _e('Desktop', 'splash-popup-for-woocommerce'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    public function meta_box_shortcode($post) {
        global $pagenow;
        if( in_array( $pagenow, array( 'post-new.php' ) ) ) {
            _e( 'You need save it to get shortcode', 'splash-popup-for-woocommerce' );
        } else {
            echo "[br_filter_single filter_id={$post->ID}]";
        }
    }
    public function information_faq($post) {
        include splash_popup_TEMPLATE_PATH . "filters_information.php";
    }
    public function settings($post) {
        $options = $this->get_option( $post->ID );
        $BeRocket_splash_popup = BeRocket_splash_popup::getInstance();
        $settings = $BeRocket_splash_popup->get_option();
        echo '<div class="br_framework_settings br_alabel_settings">';
        $BeRocket_splash_popup->display_admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                ),
            ),
            array(
                'General' => array(
                    'open_timer' => array(
                        "label"     => __('Timer', 'splash-popup-for-woocommerce'),
                        "label_for" => __('second(s)', 'splash-popup-for-woocommerce'),
                        "type"      => "number",
                        "name"      => "open_timer",
                        "value"     => '',
                        "extra"     => 'min="0" placeholder="'.$settings['open_timer'].'"',
                    ),
                    'timer' => array(
                        "label"     => __('Close Timer', 'splash-popup-for-woocommerce'),
                        "label_for" => __('second(s)', 'splash-popup-for-woocommerce'),
                        "type"      => "number",
                        "name"      => "timer",
                        "value"     => '',
                        "extra"     => 'min="0" placeholder="'.$settings['timer'].'"',
                    ),
                    'popup_width' => array(
                        "label"     => __('Popup Width', 'splash-popup-for-woocommerce'),
                        "type"      => "text",
                        "name"      => "popup_width",
                        "value"     => '',
                        "extra"     => 'placeholder="'.$settings['popup_width'].'"',
                    ),
                    'popup_height' => array(
                        "label"     => __('Popup Height', 'splash-popup-for-woocommerce'),
                        "type"      => "text",
                        "name"      => "popup_height",
                        "value"     => '',
                        "extra"     => 'placeholder="'.$settings['popup_height'].'"',
                    ),
                ),
            ),
            array(
                'name_for_filters' => $this->hook_name,
                'hide_header' => true,
                'hide_form' => true,
                'hide_additional_blocks' => true,
                'hide_save_button' => true,
                'settings_name' => $this->post_name,
                'options' => $options
            )
        );
        echo '</div>';
    }
    public function manage_edit_columns ( $columns ) {
        $columns = parent::manage_edit_columns($columns);
        //$columns["data"] = __( "Data", 'splash-popup-for-woocommerce' );
        return $columns;
    }
    public function columns_replace ( $column ) {
        parent::columns_replace($column);
        global $post;
        $filter = $this->get_option($post->ID);
        switch ( $column ) {
            case "data":
                echo 'Some data';
                break;
        }
    }
}
