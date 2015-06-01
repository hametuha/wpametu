<?php

use WPametu\AutoLoader;

/**
 * Class WPametu
 *
 * @package WPametu
 */
class WPametu
{

	/**
	 * Required PHP Version
	 */
	const PHP_VERSION = '5.5.0';

	/**
	 * i18n domain
	 */
	const DOMAIN = 'wpametu';

	/**
	 * Detect if
	 *
	 * @var bool
	 */
	public static $initialized = false;

	/**
	 * Avoid new
	 */
	private function __construct(){

	}

	/**
	 * Initialize WPametu
	 *
	 * @param string $namespace Namespace base to scan
	 * @param string $base Namespace root directory path
	 */
	public static function entry( $namespace = '', $base = '' ){
		if( !self::$initialized ){
			self::init();
		}
		// Namespace is specified.
		AutoLoader::get_instance()->register_namespace($namespace, $base);
	}

	/**
	 * Initialize WPametu
	 */
	private static function init(){
		// Avoid double initialization
		if( self::$initialized ){
			trigger_error('Do not call WPametu::init twice!', E_USER_WARNING);
			return;
		}
		// Todo: i18n for plugin
		load_theme_textdomain( self::DOMAIN, dirname(__DIR__).'/i18n' );
		// Check version
		if( version_compare(phpversion(), self::PHP_VERSION, '<') ){
			trigger_error( sprintf(__('PHP version should be more and over %s. Your version is %s.', self::DOMAIN), self::PHP_VERSION, phpversion()), E_USER_WARNING);
			return;
		}
		// Fire AutoLoader
		AutoLoader::get_instance();
		self::$initialized = true;
	}

	/**
	 * Call theme helper
	 *
	 * @return \WPametu\ThemeHelper
	 */
	public static function helper(){
		return WPametu\ThemeHelper::get_instance();
	}

	/**
	 * Show reCaptcha
	 *
	 * @param string $theme
	 * @param string $lang
	 *
	 * @return false|string
	 */
	public static function recaptcha($theme = 'clean', $lang = 'en'){
		return WPametu\Service\Recaptcha::get_instance()->get_html($theme, $lang);
	}


}