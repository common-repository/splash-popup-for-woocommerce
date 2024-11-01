<?php
class BeRocket_splash_popup_deprecated_old_popup_addon extends BeRocket_framework_addon_lib {
    public $addon_file = __FILE__;
    public $plugin_name = 'splash_popup';
    public $php_file_name   = 'popup';
    function get_addon_data() {
        $data = parent::get_addon_data();
        return array_merge($data, array(
            'addon_name'    => 'Old Popup<br><small style="font-size:14px;color:red;">DEPRECATED</small>',
            'image'         => plugins_url('/old-popup.png', __FILE__),
            'deprecated'    => true,
            'tooltip'       => '<span style="color: red;">DO NOT USE<br>IT WILL BE REMOVED IN THE FUTURE</span><br>Uses for compatibility with old style popup'
        ));
    }
}
new BeRocket_splash_popup_deprecated_old_popup_addon();
