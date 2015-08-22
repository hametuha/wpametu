<?php

namespace WPametu\Service;

use WPametu\Pattern\NoConstructor;

/**
 * Akismet utility
 *
 * @package WPametu\Service
 */
class Akismet extends NoConstructor
{
	
	const VERSION = '1.0';

	/**
	 * Check if data is spam
	 *
	 * @param array $values
	 *
	 * @return bool|\WP_Error
	 */
	public static function is_spam( array $values = [] ){
		$query_string = self::make_request( $values );
		// If Akismet is not active, always return error
		if( !class_exists('Akismet') || ! \Akismet::get_api_key() ){
			return new \WP_Error(500, 'Akismet is not active.');
		}
		// Make request
		add_filter('akismet_ua', [static::class, 'get_ua'], 9);
		$response = \Akismet::http_post($query_string, 'comment-check');
		remove_filter('akismet_ua', [static::class, 'get_ua'], 9);
		// Parse result
		switch( $response[1] ){
			case 'true':
				return true; // This is spam.
				break;
			case 'false':
				return false; // This is not spam.
				break;
			default:
				// Something is wrong
				if( isset( $response[0]['x-akismet-debug-help'] ) && !empty($response[0]['x-akismet-debug-help']) ){
					$message = $response[0]['x-akismet-debug-help'];
				}else{
					$message = 'Akismet return the invalid result. Something is wrong.';
				}
				return new \WP_Error(500, $message);
				break;
		}
	}

	/**
	 * Make request arguments
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function make_request( array $args = [] ){
		$args = wp_parse_args([
			'blog' => get_option( 'home' ),
			'blog_lang' => get_locale(),
			'blog_charset' => get_option( 'blog_charset' ),
			'user_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
			'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
		], $args);
		// Add server variables
		foreach ( $_SERVER as $key => $value ) {
			switch( $key ){
				case 'REMOTE_ADDR':
				case 'HTTP_USER_AGENT':
				case 'HTTP_REFERER':
				case 'HTTP_COOKIE':
				case 'HTTP_COOKIE2':
				case 'PHP_AUTH_PW':
					// Ignore
					break;
				default:
					$args[$key] = $value;
					break;
			}
		}
		return http_build_query($args);
	}

	/**
	 * Change User Agent name
	 * 
	 * @param string $akismet_ua
	 *
	 * @return string
	 */
	public static function get_ua( $akismet_ua ){
		global $wp_version;
		return sprintf( 'WordPress/%s | WPametu/%s', $wp_version, static::VERSION );
	}
}
