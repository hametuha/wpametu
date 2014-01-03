<?php
/** 
 * This file is used for setting up WPametu 
 * 
 * @package WPametu
 * @since 0.1
 */

namespace WPametu;

// Prepend direct loading.
defined('ABSPATH') or die();

/**
 * Base directory of WPametu
 */
const BASE_DIR = __DIR__;



/**
 * WPametu Version
 */
const VERSION = '0.2';


/**
 * Autoloader for class file.
 * 
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md PSR-0
 * @global array $wpametu
 * @param string $class_name
 */
function autoload($class_name){
    global $wpametu;
    $base_dir = dirname($wpametu['file']);
    $class_name = strtolower(ltrim($class_name, '\\'));
    $file_name = '';
    $namespace = '';
    if( ($last_ns_pos = strrpos($class_name, '\\')) ){
        $namespace = substr($class_name, 0, $last_ns_pos);
        $class_name = substr($class_name, $last_ns_pos + 1);
        $file_name = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        $file_name = str_replace('wpametu/', $base_dir.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR, $file_name);
    }
    $path = $file_name.str_replace('_', '-', $class_name).'.php';
    if ( file_exists($path) ){
        require $path;
    }
}

// Load i18n files
load_plugin_textdomain( 'wpametu', false, BASE_DIR.'/i18n/' );

// Register Main autoloader
spl_autoload_register('\WPametu\autoload');

// Load SPYC
require BASE_DIR.'/vendor/spyc/Spyc.php';

// Configure
Config::get_instance();

