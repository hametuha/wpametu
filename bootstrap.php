<?php
/** 
 * Load WPametu Framework
 *
 * @since 1.0
 */

// Do not load this file directly.
defined('ABSPATH') or die();

// This bootstrap can be load only once.
!defined('WPAMETU_INIT') or die('Do not load twitce!');

// Mark as initialized.
define('WPAMETU_INIT', true);

// If PHP >= 5.4
if( version_compare(PHP_VERSION, '5.4.0') >= 0 ){


    call_user_func(function(){

        // Register autoload
        spl_autoload_register(function( $class_name ){
            $class_name = ltrim($class_name, '\\');
            if( 0 === strpos($class_name, 'WPametu\\') ){
                $path = __DIR__.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.
                    str_replace('\\', '/', str_replace('WPametu\\', '', $class_name)).'.php';
                if( file_exists($path) ){
                    require $path;
                }
            }
        });

    });
}elseif(WP_DEBUG){
    trigger_error( sprintf('PHP version should not be less than 5.4.0. Your version is %s.', PHP_VERSION), E_USER_WARNING);
}
