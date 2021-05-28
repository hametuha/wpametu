<?php

namespace WPametu\UI\Field;


use WPametu\Exception\PropertyException;

/**
 * Taxonomy field
 *
 * @package WPametu
 */
trait Taxonomy {


	/**
	 * Override class method
	 *
	 * @param array $setting
	 * @throws \WPametu\Exception\PropertyException
	 */
	protected function test_setting( array $setting = array() ) {
		if ( ! taxonomy_exists( $setting['name'] ) ) {
			throw new PropertyException( 'taxonomy', get_called_class() );
		}
	}

	/**
	 * Save post data
	 *
	 * @param mixed $value
	 * @param \WP_Post $post
	 */
	protected function save( $value, \WP_Post $post = null ) {
		// Do nothing, because taxonomy will be automatically save.
	}

	/**
	 * Taxonomy is different name.
	 *
	 * @param string $value
	 * @return bool
	 */
	protected function validate( $value ) {
		return true;
	}

	/**
	 * Get terms as option
	 *
	 * @return array
	 */
	protected function get_options() {
		$terms = get_terms( $this->name );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return array();
		} else {
			$result = array();
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
			return $result;
		}
	}

	/**
	 * Detect if this value is allowed
	 *
	 * @param string $value
	 * @return bool
	 */
	protected function is_allowed_value( $value ) {
		return true;
	}


	/**
	 * Get input name
	 *
	 * @param int $index
	 * @return string
	 */
	protected function get_name( $index = 0 ) {
		if ( 'category' == $this->name ) {
			return 'post_category[]';
		} else {
			return 'tax_input[' . $this->name . '][]';
		}
	}
}
