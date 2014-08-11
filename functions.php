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

