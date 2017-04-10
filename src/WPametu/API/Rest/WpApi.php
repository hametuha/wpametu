<?php

namespace WPametu\API\Rest;


use WPametu\API\Controller;

/**
 * Class WpApi
 * @package WPametu
 */
abstract class WpApi extends Controller {

	/**
	 * @var string
	 */
	protected $namespace = 'hametuha/v1';

	/**
	 * Should return route
	 *
	 * @return string
	 */
	abstract protected function get_route();

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	public function __construct( array $setting = [] ) {
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	/**
	 * Register rest endpoints
	 *
	 * @throws \Exception If no handler is set, throws error.
	 */
	public function rest_api_init() {
		$register = [];
		foreach ( [ 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION' ] as $method ) {
			$method_name = strtolower( "handle_{$method}" );
			if ( ! method_exists( $this, $method_name ) ) {
				continue;
			}
			$register[] = [
				'methods' => $method,
			    'callback' => $method_name,
			    'args'     => $this->get_arguments( $method ),
			    'permission_callback' => [ $this, 'permission_callback' ],
			];
		}
		if ( $register ) {
			throw new \Exception( sprintf( 'Class %s has no handler.', get_called_class() ), 500 );
		} else {
			register_rest_route( $this->namespace, $this->get_route(), $register );
		}
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		return [];
	}
}
