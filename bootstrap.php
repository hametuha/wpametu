<?php
/** 
 * Load this file from you theme or plugin
 * 
 * <code>
 * // from theme
 * get_template_part('wpametu/bootstrap');
 * 
 * // from plugin
 * require plugin_dir_path(__FILE__).'wpametu/bootstrap.php';
 * </coce>
 * 
 * @package WordPress
 * @since 0.1
 */

// Do not load this file directly.
defined('ABSPATH') or die();



// If PHP >= 5.3
if( version_compare(PHP_VERSION, '5.3.0') >= 0 ){
    call_user_func(function(){
        
        // Version of this directory
        $version = '0.1';
        
        
		
		/**
		 * @var array Global object for setting. Hope this will be the only global object...
		 */
        global $wpametu;
		
		// Register global object.
        if( !isset($wpametu) || !is_array($wpametu) || !array_key_exists('initialized', $wpametu)){
            $wpametu = array(
                'initialized' => false,
                'version' => 0,
                'file' => '',
                'configs' => array(),
                'base' => '',
            );
        }
        
        // Register initial hook.
        if( !$wpametu['initialized'] ){
            // Add action to init
            add_action('init', function() use ( &$wpametu ){
                if( file_exists($wpametu['file']) ){
                    $wpametu['base'] = dirname($wpametu['file']);
                    require $wpametu['file'];
                    // Register autoloader
                    spl_autoload_register('\Wpametu\autoload');
                }elseif( WP_DEBUG ){
                    trigger_error(sprintf('Unable to load wpametu Framework\'s bootstrap file(%s).', $wpametu['file']), E_USER_WARNING);
                }
            }, 1);
            // Initiation regsitered.
            $wpametu['initlized'] = true;
        }

        // Compare version and assign if greater
        if( version_compare($wpametu['version'], $version) < 0 ){
            // assign version to it
            $wpametu = array_merge($wpametu, array(
                'version' => $version,
                'file' => dirname(__FILE__).'/autoload.php',
            ));
        }
    });
}elseif(WP_DEBUG){
    trigger_error( sprintf('PHP version should not be less than 5.3.0. Your version is %s.', PHP_VERSION), E_USER_WARNING);
}
