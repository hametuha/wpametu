<?php

namespace WPametu\Utility;


/**
 * Override WP_Post
 *
 * @package WPametu
 * @property-read StringHelper $str
 */
abstract class PostHelper {

	/**
	 * Original post object
	 *
	 * @var \WP_Post
	 */
	protected $post = null;

	/**
	 * Constructor
	 *
	 * @param \WP_Post $post
	 */
	public function __construct( \WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Getter.
	 *
	 * If you override this class's getter,
	 * you must call parent::__get inside getter.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'str':
				return StringHelper::get_instance();
				break;
			default:
				return null;
				break;
		}
	}

}
