<?php
/**
 * Created by PhpStorm.
 * User: guy
 * Date: 2013/11/17
 * Time: 16:43
 */

namespace WPametu\Traits;

use WPametu;

trait URL
{
	/**
	 * Base URL
	 *
	 * @var string
	 */
	private static $url = '';



	/**
	 * Returns Lib JS
	 *
	 * @param string $path
	 * @return string
	 */
	protected function get_lib_js($path){
		return $this->get_minified_js($this->lib_url('js/'.ltrim($path, '/')));
	}



	/**
	 * Returns minified JS if exists
	 *
	 * @param string $js_path
	 * @return string
	 */
	protected function get_minified_js($js_path){
		$orig = str_replace(home_url('/'), trailingslashit(ABSPATH), $js_path);
		$orig_file = basename($orig);
		$min_file = explode('.', $orig_file);
		$ext = array_pop($min_file);
		array_push($min_file, 'min');
		array_push($min_file, $ext);
		$min_file = implode('.', $min_file);
		$min = str_replace($orig_file, $min_file, $orig);
		if( !WP_DEBUG && file_exists($orig) && file_exists($min) ){
			return str_replace($orig_file, $min_file, $js_path);
		}else{
			return $js_path;
		}
	}



	/**
	 * Returns library directory path
	 *
	 * @return string
	 */
	protected function lib_dir(){
		return trailingslashit(\WPametu\BASE_DIR).'libs/';
	}



	/**
	 * Returns lib directory url
	 *
	 * @param string $path
	 * @return string
	 */
	protected function lib_url($path = ''){
		if(empty(self::$url)){
			// Avoid repetition
			if($this->in_plugin()){
				self::$url = plugin_dir_url($this->lib_dir()).'libs/';
			}else{
				// Detect if this framework is template or stylesheet
				$current_theme = wp_get_theme();
				$base_dir = $this->lib_dir();
				foreach(search_theme_directories() as $dir){
					$theme_path = explode(DIRECTORY_SEPARATOR, trim($dir['theme_file'], DIRECTORY_SEPARATOR));
					$root_path = explode(DIRECTORY_SEPARATOR, $dir['theme_root']);
					$root_path[] = $theme_path[0];
					if(false !== strpos($base_dir, implode(DIRECTORY_SEPARATOR, $root_path))){
						// Get theme name
						if(false !== strpos($base_dir, $current_theme->get_stylesheet_directory())){
							// This is main theme
							self::$url = str_replace($current_theme->get_stylesheet_directory(), $current_theme->get_stylesheet_directory_uri(), $base_dir);
						}else{
							// this is parent theme
							self::$url = str_replace($current_theme->get_template_directory(), $current_theme->get_template_directory_uri(), $base_dir);
						}
						break;
					}
				}
			}
		}
		$path = ltrim($path, '/');
		return self::$url.$path;
	}



	/**
	 * Detect if framework is in plugin
	 *
	 * @return bool
	 */
	protected function in_plugin(){
		return false !== strpos(__FILE__, WP_PLUGIN_DIR);
	}

} 