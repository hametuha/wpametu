<?php

namespace WPametu\API\Ajax;


use WPametu\API\Controller;
use WPametu\Exception\OverrideException;
use WPametu\Pattern\Singleton;


/**
 * Class Ajax
 *
 * @package WPametu
 */
abstract class AjaxBase extends Controller {


	/**
	 * Which user can access
	 *
	 * @var string user, guest, all.
	 */
	protected $target = 'user';

	/**
	 * HTTP Method
	 *
	 * @var string get or post
	 */
	protected $method = 'get';

	/**
	 * Content Type
	 *
	 * @var string
	 */
	protected $content_type = 'application/json';

	/**
	 * Required parameters
	 *
	 * @var array
	 */
	protected $required = array();

	/**
	 * If no cache header required
	 *
	 * @var bool
	 */
	protected $always_nocache = false;

	/**
	 * Constructor
	 *
	 * @param array $setting
	 * @throws OverrideException
	 */
	protected function __construct( array $setting = array() ) {
		// Check if action exists and is unique.
		if ( empty( $this->action ) || false !== array_search( $this->action, self::$actions, true ) ) {
			throw new OverrideException( get_called_class() );
		}
		// Save action
		self::$actions[] = $this->action;
		// Call parent's constructor
		parent::__construct();
	}

	/**
	 * Convert Data to JSON format
	 *
	 * You can override this action
	 *
	 * @param array $data
	 * @return mixed|string|void
	 */
	protected function encode( $data ) {
		return json_encode( $data );
	}

	/**
	 * Register ajax.
	 */
	public function register() {
		switch ( $this->target ) {
			case 'all':
				add_action( 'wp_ajax_nopriv_' . $this->action, array( $this, 'ajax' ) );
				add_action( 'wp_ajax_' . $this->action, array( $this, 'ajax' ) );
				break;
			case 'guest':
				add_action( 'wp_ajax_nopriv_' . $this->action, array( $this, 'ajax' ) );
				break;
			default:
				add_action( 'wp_ajax_' . $this->action, array( $this, 'ajax' ) );
				break;
		}
	}

	/**
	 * Do Ajax
	 */
	public function ajax() {
		if ( 'post' === $this->method || $this->always_nocache ) {
			nocache_headers();
		}
		try {
			// Authenticate
			if ( ! $this->auth() ) {
				$this->error( $this->__( 'Sorry, but you have no permission.' ), 403 );
			}
			// Validate
			if ( ! empty( $this->required ) ) {
				foreach ( $this->required as $key ) {
					if ( ! isset( $_REQUEST[ $key ] ) ) {
						$this->error( $this->__( 'Sorry, but request parameters are wrong.' ), 400 );
					}
				}
			}
			// O.K.
			$data = $this->get_data();
		} catch ( \Exception $e ) {
			$data = array(
				'error'   => true,
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			);
		}
		header( 'Content-Type', $this->content_type );
		echo $this->encode( $data );
		exit;
	}


	/**
	 * Returns Ajax endpoint
	 *
	 * @return string
	 */
	protected function ajax_url() {
		$url = admin_url( 'admin-ajax.php' );
		if ( is_ssl() ) {
			$url = str_replace( 'http://', 'https://', $url );
		} else {
			$url = str_replace( 'https://', 'http://', $url );
		}
		return $url;
	}

	/**
	 * Returns data as array.
	 *
	 * @return array
	 */
	abstract protected function get_data();

}
