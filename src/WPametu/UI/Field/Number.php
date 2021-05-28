<?php

namespace WPametu\UI\Field;


/**
 * Number input field
 *
 * @package WPametu
 * @property-read int $step
 * @property-read string $prefix
 * @property-read string $suffix
 */
class Number extends Text {


	protected  $type = 'number';

	protected $length_helper = false;

	/**
	 * Parse arguments
	 *
	 * @param array $setting
	 * @return array
	 */
	protected function parse_args( array $setting ) {
		return wp_parse_args(
			parent::parse_args( $setting ),
			array(
				'step'   => 1,
				'prefix' => '',
				'suffix' => '',
			)
		);
	}

	/**
	 * Add suffix or prefix to field
	 *
	 * @param \WP_Post $post
	 *
	 * @return array|string
	 */
	protected function get_field( \WP_Post $post ) {
		$field = parent::get_field( $post );
		if ( $this->prefix ) {
			$field = $this->prefix . ' ' . $field;
		}
		if ( $this->suffix ) {
			$field .= $this->suffix;
		}
		return $field;
	}

	/**
	 * Field arguments
	 *
	 * @return array
	 */
	protected function get_field_arguments() {
		$args = parent::get_field_arguments();
		if ( $this->min ) {
			$args['min'] = $this->min;
		}
		if ( $this->max ) {
			$args['max'] = $this->max;
		}
		if ( is_admin() ) {
			$args['class'] .= ' number-input';
		}
		unset( $args['data-max-length'] );
		unset( $args['data-min-length'] );
		$args['step'] = $this->step;
		return $args;
	}

}
