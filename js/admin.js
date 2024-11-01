var br_saved_timeout;
var br_savin_ajax = false;
(function ($){
    $(document).ready( function () {
        $(document).on('change', '.berocket_addons', function() {
            $(this).parents('.br_framework_submit_form').addClass('br_reload_form');
        });
        $(document).on('click', '.br_week_select', function(event) {
            event.preventDefault();
            $('.br_week_select.active').removeClass('active');
            $('.br_week_block.active').removeClass('active');
            $('.br_week_select_'+$(this).data('id')).addClass('active');
            $('.br_week_block_'+$(this).data('id')).addClass('active');
        });
    });
})(jQuery);
