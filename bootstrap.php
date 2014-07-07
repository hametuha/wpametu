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

/**
 * Required PHP Version
 * @const
 */
define('WPAMETU_PHP_VERSION', '5.5.0');

// Add i18n Domain
define('WPAMETU_DOMAIN', 'wpametu');

// Check PHP Version
if( version_compare(PHP_VERSION, WPAMETU_PHP_VERSION) >= 0 ){
    call_user_func(function(){

        // Mark as initialized.
        define('WPAMETU_INIT', true);

        // Define is Child theme
        if( !defined('WPAMETU_CHILD') ){
            /**
             * Whether is's child theme
             */
            define('WPAMETU_CHILD', false);
        }

        // Load i18n files
        load_theme_textdomain( WPAMETU_DOMAIN, __DIR__.'/i18n' );

        // Load global functions
        require __DIR__.'/functions.php';

        // Register autoload
        $vendor_dir = __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR;
        spl_autoload_register(function( $class_name ) use ($vendor_dir){
            $class_name = ltrim($class_name, '\\');
            if( 0 === strpos($class_name, 'WPametu\\') ){
                $path = __DIR__.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.
                    str_replace('\\', '/', str_replace('WPametu\\', '', $class_name)).'.php';
                if( file_exists($path) ){
                    require $path;
                }
            }elseif( ltrim($class_name, '\\') == 'Spyc' ){
                require $vendor_dir.'spyc'.DIRECTORY_SEPARATOR.'Spyc.php';
            }
        });

        // Fire AutoLoader
        \WPametu\Autoloader::get_instance(['config' => __DIR__.DIRECTORY_SEPARATOR.'autoloads.yaml']);


    });
}elseif( WP_DEBUG ){
    trigger_error( sprintf(__('PHP version should not be less than %s. Your version is %s.', WPAMETU_DOMAIN), WPAMETU_PHP_VERSION, PHP_VERSION), E_USER_WARNING);
}
