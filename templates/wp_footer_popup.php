<?php
$plugin_options = $BeRocket_splash_popup_option;
$timer = berocket_isset($plugin_options['timer']);
$open_timer = berocket_isset($plugin_options['open_timer']);
$page_id = apply_filters('berocket_splash_popup_page_id', 0, $timer, $open_timer);
$title = apply_filters('berocket_splash_popup_page_title', '', $page_id, $timer, $open_timer);
if( ! empty($page_id) ) {
    $cookie = ( isset($_COOKIE["br_popup"]) ? $_COOKIE["br_popup"] : '' );
    $hide_popup = false;
    $new_cookie = '';
    if( $plugin_options['display_popup_type'] == 'each_content' ) {
        if( ! $cookie || $cookie == 'open' || $cookie == 'close' ) {
            $new_cookie = array();
        } else {
            $new_cookie = explode( '-', $cookie );
        }
        if( in_array( $page_id, $new_cookie ) ) {
            $hide_popup = true;
        } else {
            $new_cookie[] = $page_id;
            $new_cookie = array_filter ( $new_cookie );
        }
        $new_cookie = implode( '-', $new_cookie );
    } elseif( $plugin_options['display_popup_type'] == 'each_page' ) {
        if( ! $cookie || $cookie == 'open' || $cookie == 'close' ) {
            $new_cookie = array();
        } else {
            $new_cookie = explode( '-', $cookie );
        }
        $current_page_id = $BeRocket_splash_popup->current_page_id;
        if( ! $current_page_id ) {
            $current_page_id = 'main';
        }
        if( in_array( $current_page_id, $new_cookie ) ) {
            $hide_popup = true;
        } else {
            $new_cookie[] = $current_page_id;
            $new_cookie = array_filter ( $new_cookie );
        }
        $new_cookie = implode( '-', $new_cookie );
    } else {
        if( $cookie == 'close' ) {
            $hide_popup = true;
        } else {
            $new_cookie = 'close';
        }
    }
    if( ! $hide_popup || $plugin_options['force_popup'] ) {
        global $wp_embed;
        $content = get_page($page_id);
        $content = $content->post_content;
        $content = do_blocks($content);
        $content = $wp_embed->run_shortcode($content);
        $content = do_shortcode($content);
        $content = $wp_embed->autoembed($content);
        $content = wptexturize($content);
        $content = wpautop($content);
        $content = shortcode_unautop($content);
        $content = prepend_attachment($content);
        $wp_filter_content_tags = function_exists('wp_filter_content_tags') ? 'wp_filter_content_tags' : 'wp_make_content_images_responsive';
        $content = $wp_filter_content_tags($content);
        $content = convert_smilies($content);
        ?>
        <script>
            var br_splash_popup_title = "<?php echo $title; ?>";
            var br_splash_popup_cookie = "<?php echo $new_cookie; ?>";
        </script>
        <?php
        ?>
        <div id="splash-popup" class="splash-popup" style="display: none;" data-timer="<?php echo $timer; ?>" data-open_timer="<?php echo $open_timer; ?>">
            <?php
                echo '<div class="popup-content">' . $content . '</div>';
            ?>
        </div>
        <?php
    }
}
?>
