<?php
/** 
 * This file is used for setting up WPametu 
 * 
 * @package WordPress
 * @since 0.1
 */

namespace WPametu;

/**
 * Register global object and initialized
 * 
 * @global array $wpametu
 */
function config($config){
    global $wpametu;
}


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
    $path = $file_name.str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
    if ( file_exists($path) ){
        require $path;
    }
}


/**
 * Returns lib directory url
 *
 * @param string $path
 * @return string
 */
function lib_url($path = ''){
	static $url = '';
	if(empty($url)){
		// Avoid repetition
		if(in_plugin()){
			$url = plugin_dir_url(__FILE__).'libs/';
		}else{
			// Detect if this framework is template or stylesheet
			$current_theme = wp_get_theme();
			$base_dir = __DIR__.'/libs/';
			foreach(search_theme_directories() as $dir){
				$theme_path = explode(DIRECTORY_SEPARATOR, trim($dir['theme_file'], DIRECTORY_SEPARATOR));
				$root_path = explode(DIRECTORY_SEPARATOR, $dir['theme_root']);
				$root_path[] = $theme_path[0];
				if(false !== strpos($base_dir, implode(DIRECTORY_SEPARATOR, $root_path))){
					// Get theme name
					if(false !== strpos($base_dir, $current_theme->get_stylesheet_directory())){
						// This is main theme
						$url = str_replace($current_theme->get_stylesheet_directory(), $current_theme->get_stylesheet_directory_uri(), $base_dir);
					}else{
						// this is parent theme
						$url = str_replace($current_theme->get_template_directory(), $current_theme->get_template_directory_uri(), $base_dir);
					}
					break;
				}
			}
		}
	}
	$path = ltrim($path, '/');
	return $url.$path;
}

/**
 * Detect if framework is in plugin
 *
 * @return bool
 */
function in_plugin(){
	return false !== strpos(__FILE__, WP_PLUGIN_DIR);
}


