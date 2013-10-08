<?php
/** 
 * This file is used for setting up WPametu 
 */


/**
 * Register global object and initialized
 * 
 * @global array $wpametu
 */
function wpametu_config($config){
    
}


/**
 * Autoloader for calss file.
 * 
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md PSR-0
 * @global array $wpametu
 * @param string $class_name class_name to load
 */
function wpametu_autoload($class_name){
    global $wpametu;
    $base_dir = dirname($wpametu['file']);
    $class_name = strtolower(ltrim($class_name, '\\'));
    $file_name = '';
    $namespace = '';
    if( ($last_ns_pos = strrpos($class_name, '\\')) ){
        $namespace = substr($class_name, 0, $last_ns_pos);
        $class_name = substr($class_name, $last_ns_pos + 1);
        $file_name = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        $file_name = str_replace('wpametu/', $base_dir.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR, $file_name);
    }
    $path = $file_name
            .str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
    if ( file_exists($path) ){
        require $path;
    }
}
