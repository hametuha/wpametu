<?php

namespace WPametu\API;

use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Http\Input;
use WPametu\Utility\IteratorWalker;
use WPametu\Utility\StringHelper;


/**
 * Controller base class
 *
 * @package WPametu
 * @property-read Input $input
 * @property-read IteratorWalker $walker
 * @property-read StringHelper $str
 */
abstract class Controller extends Singleton {


	use i18n;
	use Path;

	/**
	 * Action names
	 * @var array
	 */
	protected static $actions = array();

	/**
	 * Action name
	 *
	 * Must be overridden and unique.
	 *
	 * @var string
	 */
	protected $action = '';

	/**
	 * Models
	 *
	 * Override this with [$key => $class_name]
	 *
	 * @var array
	 */
	protected $models = array();

	/**
	 * Authentication isn't required
	 *
	 * If authentication with nonce is not required
	 *
	 * @var bool
	 */
	protected $no_auth = false;


	/**
	 * Which screen to enqueue assets
	 *
	 * @var string 'admin', 'public', 'all'. Default 'admin';
	 */
	protected $screen = 'admin';

	/**
	 * Constructor
	 *
	 * Register action hook for enqueue assets.
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = array() ) {
		// Register scripts if this request is NOT Ajax
		if ( ! self::is_ajax() ) {
			// Register scripts
			switch ( $this->screen ) {
				case 'admin':
					if ( is_admin() ) {
						add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
					}
					break;
				case 'public':
					if ( ! is_admin() ) {
						add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
					}
					break;
				case 'login':
					add_action( 'login_enqueue_scripts', array( $this, 'enqueue_assets' ) );
					break;
				default:
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
					add_action( 'login_enqueue_scripts', array( $this, 'enqueue_assets' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
					break;
			}
		}
	}


	/**
	 * Check ajax notification
	 *
	 * If you want to change authentication,
	 * override this method.
	 *
	 * @return bool
	 */
	protected function auth() {
		return $this->no_auth || $this->verify_nonce();
	}

	/**
	 * Create nonce
	 *
	 * @return string
	 */
	public function create_nonce() {
		return wp_create_nonce( $this->action );
	}

	/**
	 * Make nonced URL
	 *
	 * @param string $url
	 * @param string $key
	 * @return string
	 */
	public function nonce_url( $url, $key = '_wpnonce' ) {
		return wp_nonce_url( $url, $this->action, $key );
	}

	/**
	 * Verify nonce
	 *
	 * @param string $key
	 * @return bool
	 */
	public function verify_nonce( $key = '_wpnonce' ) {
		return wp_verify_nonce( $this->input->request( $key ), $this->action );
	}

	/**
	 * Echo nonce field
	 *
	 * @param string $key Default _wpnonce
	 * @param bool $referrer Default false.
	 * @param bool $echo Default true
	 * @return string
	 */
	public function nonce_field( $key = '_wpnonce', $referrer = false, $echo = true ) {
		return wp_nonce_field( $this->action, $key, $referrer, $echo );
	}

	/**
	 * Load template
	 *
	 * @param string $slug
	 * @param string $name
	 * @param array $args This array will be extract
	 */
	public function load_template( $slug, $name = '', array $args = array() ) {
		$base_path     = $slug . '.php';
		$original_path = $slug . ( ! empty( $name ) ? '-' . $name : '' ) . '.php';
		$found_path    = '';
		foreach ( array( $original_path, $base_path ) as $file ) {
			foreach ( array( get_stylesheet_directory(), get_template_directory() ) as $dir ) {
				$path = $dir . '/' . ltrim( $file, '\\' );
				if ( file_exists( $path ) ) {
					$found_path = $path;
					break 2;
				}
			}
		}
		if ( $found_path ) {
			// Path found. Let's locate template.
			global $post, $posts, $wp_query;
			if ( ! empty( $args ) ) {
				// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				extract( $args );
			}
			include $found_path;
		}
	}

	/**
	 * Load view template with arguments
	 *
	 * @param string $slug
	 * @param string $name
	 * @param array $args
	 */
	public static function view( $slug, $name = '', array $args = array() ) {
		$class_name = get_called_class();
		/** @var Controller $instance */
		$instance = $class_name::get_instance();
		$instance->lazy_scripts();
		$instance->load_template( $slug, $name, $args );
	}

	/**
	 * Executed on view method
	 *
	 * You can load some scripts on view template loading.
	 *
	 */
	protected function lazy_scripts() {
		// Do something
	}

	/**
	 * Throws error
	 *
	 * @param string $message
	 * @param int $code
	 * @throws \Exception
	 */
	protected function error( $message, $code = 400 ) {
		throw new \Exception( $message, $code );
	}

	/**
	 * Detect if current request is Ajax
	 *
	 * @return bool
	 */
	public static function is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Enqueue scripts and asset
	 *
	 * @param string $page Available only on admin screen
	 */
	public function enqueue_assets( $page = '' ) {
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'input':
				return Input::get_instance();
				break;
			case 'str':
				return StringHelper::get_instance();
				break;
			case 'walker':
				return IteratorWalker::get_instance();
				break;
			default:
				if ( isset( $this->models[ $name ] ) ) {
					$class_name = $this->models[ $name ];
					return $class_name::get_instance();
				}
				break;
		}
	}
}
