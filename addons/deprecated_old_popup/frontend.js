(function ($){
    var br_timer = 0;
    var br_timer_timeout;
    $(document).ready( function () {
        splash_popup_execute_func( the_splash_popup_js_data.script.js_page_load );
        var $popup = $( ".splash-popup" );
        if( $popup.length > 0 ) {
            if( typeof($popup.data('open_timer')) != 'undefined' ) {
                br_open_timer = parseInt($popup.data('open_timer')) * 1000;
            } else {
                br_open_timer = parseInt(the_splash_popup_js_data.open_timer) * 1000;
            }
            setTimeout(function() {
                if( typeof($popup.data('open_timer')) != 'undefined' ) {
                    br_timer = parseInt($popup.data('timer'));
                } else {
                    br_timer = parseInt(the_splash_popup_js_data.timer);
                }
                
                splash_popup_execute_func( the_splash_popup_js_data.script.before_popup );
                var width = $( window ).width() * 0.98;
                var height = $( window ).height() * 0.94;
                if( the_splash_popup_js_data.styles.height_paddings ) {
                    if( the_splash_popup_js_data.styles.height_paddings.indexOf('%') != -1 ) {
                        var multiple_height = parseInt(the_splash_popup_js_data.styles.height_paddings.replace('%', ''));
                        height = height * (100 - multiple_height) / 100;
                    } else {
                        var multiple_height = parseInt(the_splash_popup_js_data.styles.height_paddings);
                        height = height - multiple_height;
                    }
                }
                if( the_splash_popup_js_data.styles.width_paddings ) {
                    if( the_splash_popup_js_data.styles.width_paddings.indexOf('%') != -1 ) {
                        var multiple_width = parseInt(the_splash_popup_js_data.styles.width_paddings.replace('%', ''));
                        width = width * (100 - multiple_width) / 100;
                    } else {
                        var multiple_width = parseInt(the_splash_popup_js_data.styles.width_paddings);
                        width = width - multiple_width;
                    }
                }
                var link = "#TB_inline?width=" + width + "&height=" + height + "&inlineId=splash-popup";
                setTimeout(function() {
                    tb_show(br_splash_popup_title,link,null);
                    $('#TB_window').addClass('br_splash_popup_window');
                    $('#TB_overlay').addClass('br_splash_popup_window_bg');
                    if( br_timer > 0 ) {
                        $('#TB_closeWindowButton').hide();
                        if( $('#TB_closeAjaxWindow .br_timer').length == 0 ) {
                            $('#TB_closeAjaxWindow').append('<span class="br_timer"></span>');
                        }
                        $('#TB_closeAjaxWindow .br_timer').text(br_timer);
                    }
                }, 50);
                if( br_timer > 0 ) {
                    br_timer_timeout = setInterval(function() {
                        br_timer--;
                        if( br_timer > 0 ) {
                            $('#TB_closeWindowButton').hide();
                            if( $('#TB_closeAjaxWindow .br_timer').length == 0 ) {
                                $('#TB_closeAjaxWindow').append('<span class="br_timer"></span>');
                            }
                            $('#TB_closeAjaxWindow .br_timer').text(br_timer);
                        } else {
                            clearInterval(br_timer_timeout);
                            $('#TB_closeAjaxWindow .br_timer').remove();
                            $('#TB_closeWindowButton').show();
                        }
                    }, 1000);
                }
                var old_tb_remove = window.tb_remove;
                tb_remove = function() {
                    if( br_timer <= 0 ) {
                        old_tb_remove();
                        var expires = parseInt(the_splash_popup_js_data.expire);
                        jQuery.cookie( "br_popup", br_splash_popup_cookie, { expires: expires, path: "/" } );
                        splash_popup_execute_func( the_splash_popup_js_data.script.close_popup );
                    }
                };
            }, br_open_timer);
        }
    });
})(jQuery);
function splash_popup_execute_func ( func ) {
    if( the_splash_popup_js_data.script != 'undefined'
        && the_splash_popup_js_data.script != null
        && typeof func != 'undefined' 
        && func.length > 0 ) {
        try{
            eval( func );
        } catch(err){
            alert('You have some incorrect JavaScript code (Splash Popup)');
        }
    }
}
