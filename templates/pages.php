<table class="form-table license">
    <tr>
        <th>
            <?php 
            $options = get_post_meta( $post->ID, 'br-splash_popup-options', true );
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
                echo '<a class="br_week_select br_week_select_'.$week_id.($week_id == 0 ? ' active' : '').'" href="#'.$week_day.'" data-id="'.$week_id.'">'.$week_day.'</a>';
            }
            ?>
        </th>
        <td>
            <?php
            foreach($week_days as $week_id => $week_day) {
                echo '<div class="br_week_block br_week_block_'.$week_id.($week_id == 0 ? ' active' : '').'">';
                echo '<p>';
                echo '<h4>' .__('Not Logged In Page', 'splash-popup-for-woocommerce').'</h4>';
                ?>
                <span style="width: 70px;"><?php _e('Page', 'splash-popup-for-woocommerce') ?></span>
                <select name="br-splash_popup-options[not_logged_page][<?php echo $week_id; ?>]">
                    <option value=""></option>
                    <option value="remove"<?php echo ( ( ! empty($options['not_logged_page'][$week_id]) && $options['not_logged_page'][$week_id] == 'remove' ) ? ' selected' : '' ); ?>><?php _e('Don\'t show', 'splash-popup-for-woocommerce') ?></option>
                    <?php 
                    foreach ( $pages as $page ) {
                        echo '<option value="'.$page->ID.'"'.( ( ! empty($options['not_logged_page'][$week_id]) && $options['not_logged_page'][$week_id] == $page->ID ) ? ' selected' : '' ).'>'.$page->post_title.'</option>';
                    }
                    ?>
                </select>
                <span style="width: 70px;"><?php _e('Title', 'splash-popup-for-woocommerce') ?></span>
                <input type="text" name="br-splash_popup-options[not_logged_text][<?php echo $week_id; ?>]" value="<?php if( isset($options['not_logged_text'][$week_id]) ) echo $options['not_logged_text'][$week_id]; ?>">
                </p><p>
                <?php
                echo '<h4>' .__('Logged In Page', 'splash-popup-for-woocommerce').'</h4>';
                ?>
                <span style="width: 70px;"><?php _e('Page', 'splash-popup-for-woocommerce') ?></span>
                <select name="br-splash_popup-options[logged_page][<?php echo $week_id; ?>]">
                    <option value=""></option>
                    <option value="remove"<?php echo ( ( ! empty($options['logged_page'][$week_id]) && $options['logged_page'][$week_id] == 'remove' ) ? ' selected' : '' ); ?>><?php _e('Don\'t show', 'splash-popup-for-woocommerce') ?></option>
                    <?php 
                    foreach ( $pages as $page ) {
                        echo '<option value="'.$page->ID.'"'.( ( ! empty($options['logged_page'][$week_id]) && $options['logged_page'][$week_id] == $page->ID ) ? ' selected' : '' ).'>'.$page->post_title.'</option>';
                    }
                    ?>
                </select>
                <span style="width: 70px;"><?php _e('Title', 'splash-popup-for-woocommerce') ?></span>
                <input type="text" name="br-splash_popup-options[logged_text][<?php echo $week_id; ?>]" value="<?php if( isset($options['logged_text'][$week_id]) ) echo $options['logged_text'][$week_id]; ?>">
                </p><p>
                <?php
                echo '<h4>' .__('Logged In Customers Page', 'splash-popup-for-woocommerce').'</h4>';
                ?>
                <span style="width: 70px;"><?php _e('Page', 'splash-popup-for-woocommerce') ?></span>
                <select name="br-splash_popup-options[customer_page][<?php echo $week_id; ?>]">
                    <option value=""></option>
                    <option value="remove"<?php echo ( ( ! empty($options['customer_page'][$week_id]) && $options['customer_page'][$week_id] == 'remove' ) ? ' selected' : '' ); ?>><?php _e('Don\'t show', 'splash-popup-for-woocommerce') ?></option>
                    <?php 
                    foreach ( $pages as $page ) {
                        echo '<option value="'.$page->ID.'"'.( ( ! empty($options['customer_page'][$week_id]) && $options['customer_page'][$week_id] == $page->ID ) ? ' selected' : '' ).'>'.$page->post_title.'</option>';
                    }
                    ?>
                </select>
                <span style="width: 70px;"><?php _e('Title', 'splash-popup-for-woocommerce') ?></span>
                <input type="text" name="br-splash_popup-options[customer_text][<?php echo $week_id; ?>]" value="<?php if( isset($options['customer_text'][$week_id]) ) echo $options['customer_text'][$week_id]; ?>">
                </p>
                </div>
                <?php
            }
            ?>
        </td>
    </tr>
</table>
