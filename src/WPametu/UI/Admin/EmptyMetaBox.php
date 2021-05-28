<?php

namespace WPametu\UI\Admin;


use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Utility\StringHelper;
use WPametu\Http\Input;
use WPametu\Http\PostRedirectGet;

/**
 * Create meta box for edit screen
 *
 * @package WPametu
 * @property-read StringHelper $string
 * @property-read Input $input
 * @property-read PostRedirectGet $prg
 * @property-read \WP_Screen $screen
 */
abstract class EmptyMetaBox extends Singleton {


	use i18n;

	/**
	 * Index of `add_meta_boxes`
	 *
	 * @var int
	 */
	protected $hook_priority = 10;

	/**
	 * Index
	 *
	 * @var string
	 */
	protected $context = 'side';

	/**
	 * Priority
	 *
	 * @var string
	 */
	protected $priority = 'low';

	/**
	 * Post type to show
	 *
	 * @var array
	 */
	protected $post_types = array();

	/**
	 * @var string
	 */
	protected $nonce_key = '';

	/**
	 * @var string
	 */
	protected $nonce_action = '';

	/**
	 * @var bool
	 */
	protected $hook = false;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = array() ) {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'adminInit' ) );
			add_action(
				'save_post',
				function( $post_id, \WP_Post $post ) {
					if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
						return;
					}
					if ( $this->nonce_key && $this->input->verify_nonce( $this->nonce_action, $this->nonce_key ) ) {
						$this->savePost( $post );
					}
				},
				10,
				2
			);
			add_action( 'add_meta_boxes', array( $this, 'adminMetaBoxes' ), $this->hook_priority, 2 );
		}
	}

	/**
	 * Executed on admin_init
	 */
	public function adminInit() {

	}

	/**
	 * Execute when nonce is O.K.
	 *
	 * @param \WP_Post $post
	 */
	public function savePost( \WP_Post $post ) {

	}

	/**
	 * Register meta boxes
	 *
	 * @param string $post_type
	 * @param \WP_Post $post
	 */
	public function adminMetaBoxes( $post_type, $post ) {
		if ( false !== array_search( $post_type, $this->post_types, true ) ) {
			switch ( $this->hook ) {
				case 'post_submitbox_misc_actions':
				case 'post_submitbox_start':
					add_action(
						$this->hook,
						function() use ( $post ) {
							if ( $this->nonce_key ) {
								wp_nonce_field( $this->nonce_action, $this->nonce_key );
							}
							$this->editFormX( $post );
						},
						$this->hook_priority
					);
					break;
				case 'post_edit_form_tag':
				case 'edit_form_top':
				case 'edit_form_before_permalink':
				case 'edit_form_after_title':
				case 'edit_form_after_editor':
				case 'dbx_post_sidebar':
					add_action(
						$this->hook,
						function( \WP_Post $post ) {
							if ( $this->nonce_key ) {
								wp_nonce_field( $this->nonce_action, $this->nonce_key );
							}
							$this->editFormX( $post );
						}
					);
					break;
				default:
					$s    = explode( '\\', get_called_class() );
					$name = $this->string->camel_to_hyphen( $s[ count( $s ) - 1 ] );
					add_meta_box(
						"metabox-$name",
						$this->title,
						function( \WP_Post $post, array $screen ) {
							if ( $this->nonce_key ) {
								wp_nonce_field( $this->nonce_action, $this->nonce_key );
							}
							$this->doMetaBox( $post, $screen );
						},
						$post_type,
						$this->context,
						$this->priority
					);
					break;
			}
		}

	}

	/**
	 * Edit form x
	 *
	 * @param \WP_Post $post
	 */
	public function editFormX( \WP_Post $post ) {
		printf( $this->__( 'You should override editFromX method in %s' ), get_called_class() );
	}

	/**
	 * Render meta box
	 *
	 * @param \WP_Post $post
	 * @param array $screen
	 */
	public function doMetaBox( \WP_Post $post, array $screen ) {
		printf( $this->__( 'You should override doMetaBox method in %s' ), get_called_class() );
	}


	/**
	 * Detect if this is Ajax request
	 *
	 * @return bool
	 */
	protected function isAjax() {
		return is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'string':
				return StringHelper::get_instance();
				break;
			case 'input':
				return Input::get_instance();
				break;
			case 'prg':
				return PostRedirectGet::get_instance();
				break;
			case 'screen':
				return get_current_screen();
				break;
			default:
				// Do nothing
				return null;
				break;
		}
	}

}
