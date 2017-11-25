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
		if ( $this->is_available() ) {
			add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
		}
	}
	
	/**
	 * Check availability
	 *
	 * Override this function if some condition exists like 
	 * plugin dependencies.
	 *
	 * @return bool
	 */
	protected function is_available() {
		return true;
	}


	/**
	 * Register rest endpoints
	 *
	 * @throws \Exception If no handler is set, throws error.
	 */
	public function rest_api_init() {
		$register = [];
		foreach ( [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTION' ] as $method ) {
			$method_name = strtolower( "handle_{$method}" );
			if ( ! method_exists( $this, $method_name ) ) {
				continue;
			}
			$register[] = [
				'methods' => $method,
			    'callback' => [ $this, 'invoke' ],
			    'args'     => $this->get_arguments( $method ),
			    'permission_callback' => [ $this, 'permission_callback' ],
			];
		}
		if ( ! $register ) {
			throw new \Exception( sprintf( 'Class %s has no handler.', get_called_class() ), 500 );
		} else {
			register_rest_route( $this->namespace, $this->get_route(), $register );
		}
	}
	
	/**
	 * Invoke callback
	 * 
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function invoke( $request ) {
		$method_name = 'handle_' . strtolower( $request->get_method() );
		try {
			return call_user_func_array( [ $this, $method_name ], func_get_args() );
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
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
