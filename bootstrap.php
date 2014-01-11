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
 * @since 0.1
 */


// Do not load this file directly.
defined('ABSPATH') or die();

// If PHP < 5.4
if( version_compare(PHP_VERSION, '5.4.0') < 0 ){
	trigger_error( sprintf('PHP version should not be less than 5.4.0. Your version is %s.', PHP_VERSION), E_USER_WARNING);
	return;
}


// Call setup script and call rambda function
$function = require dirname(__FILE__).'/setup.php';
call_user_func($function);
