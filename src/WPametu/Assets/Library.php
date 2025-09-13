<?php

namespace WPametu\Assets;


use WPametu\File\Path;
use WPametu\Traits\i18n;
use WPametu\Pattern\Singleton;
use WPametu\Utility\StringHelper;


/**
 * Load assets library
 *
 * @package WPametu
 * @property-read StringHelper $str
 */
class Library extends Singleton {

	use Path;
	use i18n;

	/**
	 * Library common version
	 *
	 * @const string
	 */
	const COMMON_VERSION = '1.0.2';

	/**
	 * Scripts to register
	 *
	 * @var array
	 */
	private $scripts = array(
		// Bundled libraries
		'chart-js'             => array(
			'/assets/vendor/chart-js/Chart.min.js',
			null,
			'2.7.1',
			true,
		),
		'jsrender'             => array(
			'/assets/vendor/jsrender/jsrender.min.js',
			array( 'jquery' ),
			'0.9.89',
			true,
		),
		'jquery-ui-timepicker' => array(
			'/assets/vendor/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js',
			array( 'jquery-ui-datepicker-i18n', 'jquery-ui-slider' ),
			'1.6.3',
			true,
		),
		// External Libraries
		'gmap'                 => array(
			'//maps.googleapis.com/maps/api/js',
			null,
			null,
			true,
		),
		'google-jsapi'         => array(
			'https://www.google.com/jsapi',
			null,
			null,
			true,
		),
	);

	/**
	 * CSS to register
	 *
	 * @var array
	 */
	private $css = array(
		'jquery-ui-mp6'        => array(
			'/assets/vendor/jquery-ui-mp6/css/jquery-ui.css',
			null,
			'1.12.1',
			'screen',
		),
		'jquery-ui-timepicker' => array(
			'/assets/vendor/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css',
			array( 'jquery-ui-mp6' ),
			'1.6.3',
			'screen',
		),
		'font-awesome'         => array(
			'/assets/vendor/font-awesome/css/font-awesome.min.css',
			array(),
			'4.7.0',
			'all',
		),
	);

	/**
	 * Show all registered assets
	 *
	 * @return array
	 */
	public function show_assets() {
		return array(
			'js'  => $this->scripts,
			'css' => $this->css,
		);
	}

	/**
	 * Show all registered assets for debugging
	 */
	public static function all_assets() {
		/** @var Library $instance */
		$instance = self::get_instance();

		return $instance->show_assets();
	}

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = array() ) {
		add_action( 'init', array( $this, 'register_libraries' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Register assets
	 */
	public function register_libraries() {
		// Detect Locale
		$locale = explode( '_', get_locale() );
		if ( count( $locale ) === 1 ) {
			$locale = strtolower( $locale[0] );
		} else {
			$locale = strtolower( $locale[0] ) . '-' . strtoupper( $locale[1] );
		}
		$this->scripts['jquery-ui-datepicker-i18n'] = array(
			'/assets/vendor/jquery-ui-i18n/datepicker-' . $locale . '.js',
			array( 'jquery-ui-datepicker' ),
			'1.12.1',
			true,
		);
		$this->scripts['jquery-ui-timepicker-i18n'] = array(
			'/assets/vendor/jquery-ui-timepicker-addon/i18n/jquery-ui-timepicker-' . $locale . '.js',
			array( 'jquery-ui-timepicker' ),
			'1.6.3',
			true,
		);

		// Register all scripts
		foreach ( $this->scripts as $handle => list( $src, $deps, $version, $footer ) ) {
			$src = $this->build_src( $src );
			// Google map
			if ( 'gmap' === $handle ) {
				$args = array( 'sensor' => 'true' );
				if ( defined( 'WPAMETU_GMAP_KEY' ) ) {
					$args['key'] = \WPAMETU_GMAP_KEY;
				}
				$src = add_query_arg( $args, $src );
			}
			wp_register_script( $handle, $src, $deps, $version, $footer );
			$localized = $this->localize( $handle );
			if ( ! empty( $localized ) ) {
				wp_localize_script( $handle, $this->str->hyphen_to_camel( $handle ), $localized );
			}
		}
		// Register all css
		foreach ( $this->css as $handle => list( $src, $deps, $version, $media ) ) {
			$src = $this->build_src( $src );
			wp_register_style( $handle, $src, $deps, $version, $media );
		}
		// Parse dependencies.json.
		$path = $this->get_root_dir() . '/wp-dependencies.json';
		if ( file_exists( $path ) ) {
			$json = json_decode( file_get_contents( $path ), true );
			if ( $json ) {
				foreach ( $json as $asset ) {
					$url = trailingslashit( $this->get_root_uri() ) . $asset['path'];
					switch ( $asset['ext'] ) {
						case 'css':
							wp_register_style( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['media'] );
							break;
						case 'js':
							wp_register_script( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['footer'] );
							break;
					}
				}
			}
		}
	}

	/**
	 * Build URL for library
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	private function build_src( $src ) {
		if ( ! preg_match( '/^(https?:)?\/\//u', $src ) ) {
			// O.K. This is a library in WPametu!
			$src = trailingslashit( $this->get_root_uri() ) . ltrim( $src, '/' );
		}

		return $src;
	}

	/**
	 * Make localized script
	 *
	 * @param string $handle
	 *
	 * @return array
	 */
	private function localize( $handle ) {
		switch ( $handle ) {
			case 'wpametu-admin-helper':
				return array(
					'error' => $this->__( 'Error' ),
					'close' => $this->__( 'Close' ),
				);
				break;
			default:
				return array();
				break;
		}
	}

	/**
	 * Load assets on admin screen
	 *
	 * @param string $page
	 */
	public function admin_assets( $page = '' ) {
		wp_enqueue_script( 'wpametu-admin-helper' );
		wp_enqueue_style( 'jquery-ui-mp6' );
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return Singleton
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'str':
				return StringHelper::get_instance();
				break;
			default:
				// Do nothing
				break;
		}
	}
}
