<?php

namespace WPametu\API;


use WPametu\API\Ajax\AjaxBase;
use WPametu\API\Rest\RestBase;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Traits\Reflection;
use WPametu\Utility\StringHelper;

/**
 * Rewrite rule manager
 *
 * @package WPametu
 * @property-read string $api_class
 * @property-read string $api_vars
 * @property-read bool|string $config
 * @property-read int $last_updated
 * @property-read string $rewrite_md5
 * @property-read StringHelper $str
 */
final class Rewrite extends Singleton {


	use Path, i18n, Reflection;

	/**
	 * Option name of WPametu's rewrite rules
	 *
	 * @var string
	 */
	private $option_name = 'wpametu_rewrite_last_updated';

	/**
	 * Option name of rewrite rule's md5
	 *
	 * @var string
	 */
	private $rewrite_md5_name = 'wpametu_rewrite_md5';

	/**
	 * Query vars name for method name
	 *
	 * @var string
	 */
	private $api_query_name = 'api_class';

	/**
	 * Query vars name for method arguments
	 *
	 * @var string
	 */
	private $vars_query_name = 'api_vars';

	/**
	 * Rewrite classes
	 *
	 * @var array
	 */
	private $classes = array();

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	public function __construct( array $setting = array() ) {
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'rewrite_rules_array', array( $this, 'rewrite_rules_array' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	/**
	 * Filter query vars
	 *
	 * @param array $vars
	 * @return array
	 */
	public function query_vars( $vars ) {
		$vars[] = $this->api_class;
		$vars[] = $this->api_vars;
		return $vars;
	}

	/**
	 * Add rewrite rules.
	 *
	 * @param array $rules
	 * @return array
	 */
	public function rewrite_rules_array( array $rules ) {
		if ( ! empty( $this->classes ) ) {
			// Normal rewrite rules
			$new_rewrite   = array();
			$error_message = array();
			foreach ( $this->classes as $class_name ) {
				$prefix = $this->get_prefix( $class_name );
				/** @var RestBase $class_name */
				if ( empty( $prefix ) ) {
					$error_message[] = sprintf( $this->__( '<code>%s</code> should have prefix property.' ), $class_name );
					continue;
				}
				// API Rewrite rules
				$new_rewrite[ trim( $prefix, '/' ) . '(/.*)?$' ] = "index.php?{$this->api_class}={$class_name}&{$this->api_vars}=\$matches[1]";
			}
			if ( ! empty( $new_rewrite ) ) {
				$rules = array_merge( $new_rewrite, $rules );
			}
			if ( ! empty( $error_message ) ) {
				add_action(
					'admin_notices',
					function() use ( $error_message ) {
						printf( '<div class="error"><p>%s</p></div>', implode( '<br />', $error_message ) );
					}
				);
			}
		}
		return $rules;
	}

	/**
	 * Parse request and invoke REST class if possible
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() ) {
			return;
		}
		// Detect class is valid
		$api_class = $wp_query->get( $this->api_class );
		if ( ! $api_class ) {
			return;
		}
		try {
			// Fix escaped namespace delimiter
			$api_class = str_replace( '\\\\', '\\', $api_class );
			// Check class existence
			if ( ! $this->is_valid_class( $api_class ) ) {
				throw new \Exception( __( 'Specified URL is invalid.', 'wpametu' ), 404 );
			}
			/** @var RestBase $instance */
			$instance = $api_class::get_instance();
			$instance->parse_request( $wp_query->get( $this->api_vars ), $wp_query );
		} catch ( \Exception $e ) {
			switch ( $e->getCode() ) {
				case 404:
					$wp_query->set_404();
					break;
				case 200:
				case 201:
					// If status is O.K.
					// Do nothing.
					break;
				default:
					wp_die(
						$e->getMessage(),
						get_status_header_desc( $e->getCode() ),
						array(
							'response'  => $e->getCode(),
							'back_link' => true,
						)
					);
					break;
			}
		}
	}

	/**
	 * Add class name to rewrite rules
	 *
	 * @param $class_name
	 */
	public static function register_class( $class_name ) {
		/** @var $this $instance */
		$instance            = self::get_instance();
		$instance->classes[] = $class_name;
	}

	/**
	 * Detect if class name is valid
	 *
	 * @param string $class_name
	 * @return bool
	 */
	private function is_valid_class( $class_name ) {
		return class_exists( $class_name ) && $this->is_sub_class_of( $class_name, RestBase::class );
	}

	/**
	 * Update rewrite rules if possible
	 */
	public function admin_init() {
		if ( ! AjaxBase::is_ajax() && current_user_can( 'manage_options' ) ) {
			if ( ! empty( $this->classes ) ) {
				$rewrites = '';
				foreach ( $this->classes as $class_name ) {
					$rewrites .= $this->get_prefix( $class_name );
				}
				$rewrites = md5( $rewrites );
				if ( get_option( 'rewrite_rules' ) && $this->rewrite_md5 !== $rewrites ) {
					flush_rewrite_rules();
					$last_updated = current_time( 'timestamp', true );
					update_option( $this->option_name, $last_updated );
					update_option( $this->rewrite_md5_name, $rewrites );
					$message = sprintf( $this->__( 'Rewrite rules updated. Last modified date is %s' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_updated ) );
					add_action(
						'admin_notices',
						function() use ( $message ) {
							printf( '<div class="updated"><p>%s</p></div>', $message );
						}
					);
				}
			}
		}
	}

	/**
	 * Get class prefix
	 *
	 * @param string $class_name
	 * @return string
	 */
	public function get_prefix( $class_name ) {
		/** @var RestBase $class_name */
		if ( ! empty( $class_name::$prefix ) ) {
			return $class_name::$prefix;
		} else {
			$seg  = explode( '\\', $class_name );
			$base = $seg[ count( $seg ) - 1 ];
			return $this->str->camel_to_hyphen( $base );
		}
	}

	/**
	 * Getter
	 *
	 * @param $name
	 * @return mixed|void
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'api_class':
				/**
				 * wpametu_api_query_name
				 *
				 * Filter query_vars name for api class detect
				 *
				 * @filter
				 * @param string $api_query_name
				 * @return string
				 */
				return apply_filters( 'wpametu_api_query_name', $this->api_query_name );
				break;
			case 'api_vars':
				/**
				 * wpametu_vars_query_name
				 *
				 * Filter query_vars name for api class detect
				 *
				 * @filter
				 * @param string $api_query_name
				 * @return string
				 */
				return apply_filters( 'wpametu_vars_query_name', $this->vars_query_name );
				break;
			case 'config':
				$config = $this->get_config_dir() . '/rewrite.php';
				if ( file_exists( $config ) ) {
					return $config;
				} else {
					return false;
				}
				break;
			case 'last_updated':
				return (int) get_option( $this->option_name, false );
				break;
			case 'rewrite_md5':
				return (string) get_option( $this->rewrite_md5_name, false );
				break;
			case 'str':
				return StringHelper::get_instance();
				break;
			default:
				// Do nothing
				break;
		}
	}
}
