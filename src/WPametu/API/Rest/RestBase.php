<?php

namespace WPametu\API\Rest;

use WPametu\API\Controller;
use WPametu\API\RewriteParser;
use WPametu\Exception\AuthException;

/**
 * Rest Controller base
 *
 * @package WPametu\API
 * @property-read \WP_User $user
 */
class RestBase extends RewriteParser {

	/**
	 * Rewrite rules array
	 *
	 * @var string
	 */
	public static $prefix = '';

	/**
	 * Is always public page
	 *
	 * @var string
	 */
	protected $screen = 'public';

	/**
	 * WP_Query instance
	 *
	 * @var \WP_Query
	 */
	protected $wp_query = null;

	/**
	 * RestBase constructor.
	 *
	 * @param array $setting Setting value.
	 */
	public function __construct( array $setting ) {
		parent::__construct( $setting );
		// If REST API is supported, call it.
		if ( defined( 'REST_API_VERSION' ) ) {
			add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
		}
	}

	/**
	 * Override this if you need rest API
	 */
	public function rest_api_init() {}


	/**
	 * Handle request
	 *
	 * @param string $method Method name.
	 * @param string $request_method GET, POST, PUT, DELETE.
	 * @param array  $arguments Arguments as array.
	 */
	protected function handle_request( $method, $request_method, array $arguments = [] ) {
		if ( empty( $method ) || 'page' === $method ) {
			$page = max( ( isset( $arguments[0] ) ? intval( $arguments[0] ) : 1 ), 1 );
			$this->pager( $page );
			exit;
		} else {
			// Call method if exists.
			if ( $this->invoke( $method, $request_method, $arguments ) ) {
				exit;
			} else {
				$this->method_not_found();
			}
		}
	}

	/**
	 * Pager
	 *
	 * @param int $page Page number.
	 */
	protected function pager( $page = 1 ) {
		$this->method_not_found();
	}

	/**
	 * Check auth and redirect if not logged in
	 */
	protected function auth_redirect() {
		if ( ! is_user_logged_in() ) {
			auth_redirect();
			exit;
		}
	}

	/**
	 * Return url
	 *
	 * @param string $uri Get URI.
	 * @param bool   $ssl If SSL, set true.
	 *
	 * @return string
	 */
	public function url( $uri = '', $ssl = false ) {
		$class_name = get_called_class();
		$seg        = explode( '\\', $class_name );
		$base       = $seg[ count( $seg ) - 1 ];
		$prefix     = trim( $class_name::$prefix ?: $this->str->camel_to_hyphen( $base ), '/' );
		$uri        = ltrim( $uri, '/' );

		return home_url( $prefix . '/' . $uri, $ssl ? 'https' : 'http' );
	}

	/**
	 * Getter
	 *
	 * @param string $name key name.
	 *
	 * @return mixed|null|\WP_User
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'user':
				$user = wp_get_current_user();
				return $user->ID ? $user : null;
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
