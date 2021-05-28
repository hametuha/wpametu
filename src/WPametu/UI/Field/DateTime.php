<?php

namespace WPametu\UI\Field;


class DateTime extends Input {


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
				'placeholder' => 'YYYY-MM-DD HH:II:SS',
			)
		);
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
			if ( isset( $args['class'] ) ) {
				$args['class'] .= ' wpametu-datetime-picker';
			} else {
				$args['class'] = ' wpametu-datetime-picker';
			}
		}
		unset( $args['data-max-length'] );
		unset( $args['data-min-length'] );
		return $args;
	}

}
