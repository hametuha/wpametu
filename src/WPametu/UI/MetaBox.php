<?php

namespace WPametu\UI;


use WPametu\Http\PostRedirectGet;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Traits\Reflection;
use WPametu\Http\Input;
use WPametu\Utility\StringHelper;
use WPametu\Utility\IteratorWalker;
use WPametu\UI\Field\Taxonomy;

/**
 * Class MetaBox
 *
 * @package WPametu
 * @property-read string $nonce
 * @property-read Input $input
 * @property-read StringHelper $str
 * @property-read PostRedirectGet $prg
 * @property-read IteratorWalker $walker
 */
abstract class MetaBox extends Singleton {


	use Reflection, i18n;

	/**
	 * If this is initialized
	 *
	 * @var bool
	 */
	public static $initialized = false;

	/**
	 * Meta box name
	 *
	 * Must start with underscore.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Meta box label
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * @var string
	 */
	protected $capability = 'edit_posts';

	/**
	 * Meta box fields.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Array of post types
	 *
	 * @var array
	 */
	protected $post_types = array();

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = array() ) {
		$this->register_save_action();
		$this->register_ui();
		add_action( 'add_meta_boxes', [ $this, 'override' ], 10, 2 );
		if ( ! self::$initialized ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
			self::$initialized = true;
		}
		add_action( 'admin_enqueue_scripts', function( $page ) {
			$screen = get_current_screen();
			if ( in_array( $page, [ 'post.php', 'post-new.php' ], true ) && $this->is_valid_post_type( $screen->post_type ) ) {
				$this->load_additional_assets();
			}
		} );
	}

	/**
	 * Register save post hook
	 */
	protected function register_save_action() {
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	abstract protected function register_ui();

	/**
	 * Detect if nonce is valid
	 *
	 * @return bool
	 */
	protected function verify_nonce() {
		return wp_verify_nonce( $this->input->post( $this->nonce ), $this->nonce );
	}

	/**
	 * Override default meta box
	 *
	 * @param string $post_type
	 * @param \WP_Post $post
	 */
	public function override( $post_type, $post ) {
		if ( $this->is_valid_post_type( $post_type ) ) {
			foreach ( $this->fields as $name => $vars ) {
				switch ( $name ) {
					case 'excerpt':
						remove_meta_box( 'postexcerpt', $post_type, 'normal' );
						break;
					case 'post_format':
						remove_meta_box( 'formatdiv', $post_type, 'side' );
						break;
					default:
						if ( false !== array_search( Taxonomy::class, class_uses( $vars['class'] ), true ) ) {
							if ( taxonomy_exists( $name ) ) {
								if ( is_taxonomy_hierarchical( $name ) ) {
									$box_id = $name . 'div';
								} else {
									$box_id = 'tagsdiv-' . $name;
								}
								remove_meta_box( $box_id, $post_type, 'side' );
							}
						} else {
							// Do nothing
						}
						break;
				}
			}
		}
	}

	/**
	 * Echo nonce field
	 */
	protected function nonce_field() {
		wp_nonce_field( $this->nonce, $this->nonce, false );
	}

	/**
	 * This meta fields description
	 */
	protected function desc() {
		return '';
	}

	/**
	 * Save post data
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function save_post( $post_id, \WP_Post $post ) {
		// Skip auto save
		if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
			return;
		}
		// Check Nonce
		if ( ! $this->verify_nonce() ) {
			return;
		}
		// O.K., let's save
		foreach ( $this->loop_fields() as $field ) {
			try {
				if ( is_wp_error( $field ) ) {
					/** @var \WP_Error $field */
					throw new \Exception( $field->get_error_message(), $field->get_error_code() );
				}
				if ( ! $this->is_override_field( $field->name ) ) {
					/** @var \WPametu\UI\Field\Base $field */
					$field->update( $this->input->post( $field->name ), $post );
				}
			} catch ( \Exception $e ) {
				$this->prg->addErrorMessage( $e->getMessage() );
			}
		}
	}

	/**
	 * Render meta box content
	 *
	 * @param \WP_Post $post
	 */
	public function render( \WP_Post $post ) {
		$this->nonce_field();
		$this->desc();
		echo '<table class="table form-table wpametu-meta-table">';
		foreach ( $this->loop_fields() as $field ) {
			if ( ! is_wp_error( $field ) ) {
				/** @var \WPametu\UI\Field\Base $field */
				$field->render( $post );
			} else {
				/** @var \WP_Error $field */
				printf( '<div class="error"><p>%s</p></div>', $field->get_error_message() );
			}
		}
		echo '</table>';
	}

	/**
	 * Generator
	 *
	 * @return \Generator
	 */
	protected function loop_fields() {
		foreach ( $this->fields as $name => $args ) {
			$return = null;
			if ( isset( $args['class'] ) && class_exists( $args['class'] ) ) {
				$class_name = $args['class'];
				unset( $args['class'] );
				$args['name'] = $name;
				try {
					$return = new $class_name( $args );
				} catch ( \Exception $e ) {
					$return = new \WP_Error( $e->getCode(), $e->getMessage() );
				}
			} else {
				// translators: %s is field name.
				$return = new \WP_Error( 500, sprintf( __( '%s\'s argument setting is invalid.', 'wpametu' ), $name ) );
			}
			yield $return;
		}
	}

	/**
	 * Detect if current user has capability
	 *
	 * @return bool
	 */
	protected function has_cap() {
		return current_user_can( $this->capability );
	}

	/**
	 * Detect if post type is valid
	 *
	 * @param string $post_type
	 * @return bool
	 */
	protected function is_valid_post_type( $post_type = '' ) {
		return in_array( $post_type, $this->post_types, true );
	}

	/**
	 * Detect if override talbe
	 *
	 * @param string $name
	 * @return bool
	 */
	protected function is_override_field( $name ) {
		switch ( $name ) {
			case 'excerpt':
				return true;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Load meta box helper css and JS
	 *
	 * @param string $page
	 */
	final public function assets( $page = '' ) {
		if ( in_array( $page, [ 'post.php', 'post-new.php' ], true ) ) {
			wp_enqueue_script( 'wpametu-metabox' );
			wp_enqueue_style( 'wpametu-metabox' );
		}
	}

	/**
	 * Load meta box helper
	 *
	 * If you need additional assets, override this.
	 */
	protected function load_additional_assets() {
		// Do something here.
		// wp_enqueue_script(), wp_enqueue_style().
	}


	/**
	 * Getter.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'nonce':
				return $this->name . '_nonce';
				break;
			case 'input':
				return Input::get_instance();
				break;
			case 'str':
				return StringHelper::get_instance();
				break;
			case 'prg':
				return PostRedirectGet::get_instance();
				break;
			case 'walker':
				return IteratorWalker::get_instance();
				break;
			default:
				// Do nothing.
				break;
		}
	}
}
