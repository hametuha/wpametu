<?php


/**
 * Access to theme helper instance
 *
 * @return \WPametu\ThemeHelper
 */
function wpametu(){
    return WPametu\ThemeHelper::get_instance();
}

/**
 * Force session
 */
function wpametu_session(){
    static $done = false;
    if( !$done ){
        add_filter('wpametu_auto_start_session', function(){
            return true;
        });
        $done = true;
    }
}


/**
 * Show recaptcha
 *
 * @param string $theme
 * @param string $lang
 *
 * @return mixed
 */
function wpametu_recaptcha($theme = 'clean', $lang = 'en'){
	return WPametu\Service\Recaptcha::get_instance()->get_html($theme, $lang);
}
