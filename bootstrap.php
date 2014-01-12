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
 * @package WPametu
 * @author Takahashi Fumiki
 * @since 0.1
 */


// Do not load this file directly.
defined('ABSPATH') or die();

try{
    // If PHP < 5.4
    if( version_compare(PHP_VERSION, '5.4.0') < 0 ){
        throw new Exception( sprintf('PHP version should not be less than <code>5.4.0</code>. Your version is <code>%s</code>.', PHP_VERSION));
    // IF WordPress < 3.8
    }elseif( !version_compare( '3.8.*', get_bloginfo( 'version' ), '<=') ){
        throw new Exception( sprintf('WordPress must not be less than <code>3.8.0</code>. Your version is <code>%s</code>.', get_bloginfo('version')));
    }else{
        // Call setup script and call rambda function
        $function = require dirname(__FILE__).'/setup.php';
        call_user_func($function);
    }
}catch(Exception $e){
    // Use create_function, because this line could be executed in
    // PHP 5.2 environment
    add_action('admin_notices', create_function('$a', sprintf(
        'printf( "<div class=\"error\"><p>%%s</p></div>", "%s" );',
        '<strong>WPametu Error: </strong>'.$e->getMessage().'<br />Error occurs at <code>'.dirname(__FILE__).'</code>'.
        '<br /><strong>All plugins and themes which use WPametu has no effect.</strong>'
    )));
}

