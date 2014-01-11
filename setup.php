<?php
/**
 * This script returns callback function
 *
 * @package WPametu
 * @since 0.1
 */
return function(){

    // Version of this directory
    $version = '0.2';

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
            'base' => '',
        );
    }

    // Register initial hook.
    if( !$wpametu['initialized'] ){
        // Add action to init
        add_action('init', function() use ( &$wpametu ){
            if( file_exists($wpametu['file']) ){
                // Load autoloader file
                $wpametu['base'] = dirname($wpametu['file']);
                require $wpametu['file'];
            }elseif( WP_DEBUG ){
                trigger_error(sprintf('Unable to load wpametu Framework\'s bootstrap file(%s).', $wpametu['file']), E_USER_WARNING);
            }
        }, 1);
        // Initiation regsitered.
        $wpametu['initialized'] = true;
    }

    // Compare version and assign if greater
    if( version_compare($wpametu['version'], $version) < 0 ){
        // assign version to it
        $wpametu = array_merge($wpametu, array(
            'version' => $version,
            'file' => dirname(__FILE__).'/autoload.php',
        ));
    }
};
