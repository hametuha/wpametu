<?php
/** 
 * 
 * 
 * 
 * 
 */

// Do not load this file directly.
defined('ABSPATH') or die();


// If PHP >= 5.3
if( version_compare(PHP_VERSION, '5.3.0') >= 0 ){
    call_user_func(function(){
        // Register global object
        global $hametuha_framework;
        // Version of this directory
        $version = '0.1';
        
        // Load functions.
        if( !function_exists('hametuha_bootstrap') ){
            require dirname(__FILE__).'/hametuha/functions.php';
            // Add action to init
            add_action('init', 'hametuha_bootstrap', 1);
        }

        // Compare version and assign if greater
        if( !isset($hametuha_framework['version']) || version_compare($hametuha_framework['version'], $version) > 0 ){
            // assign version to it
            $hametuha_framework = array(
                'version' => $version,
                'file' => dirname(__FILE__).'/autoload.php',
            );
        }
    });
}elseif(WP_DEBUG){
    trigger_error( sprintf('PHP version should not be less than 5.3.0. Your version is %s.', PHP_VERSION), E_USER_WARNING);
}
