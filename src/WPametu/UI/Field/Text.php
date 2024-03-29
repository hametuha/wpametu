<?php

namespace WPametu\UI\Field;
use WPametu\Exception\ValidateException;


/**
 * Text input field
 *
 * @package WPametu
 * @property-read int $min
 * @property-read int $max
 * @property-read int $input_type
 */
class Text extends Input {


	protected $type = 'text';

	/**
	 * Whether if length helper is required.
	 *
	 * @var bool
	 */
	protected $length_helper = true;

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
				'min'        => 0,
				'max'        => 0,
				'input_type' => '',
			)
		);
	}

	/**
	 * Override input type.
	 *
	 * @return string
	 */
	protected function get_input_type() {
		switch ( $this->input_type ) {
			case 'email':
			case 'url':
			case 'tel':
			case 'password':
				return $this->input_type;
			default:
				return $this->type;
		}
	}


	/**
	 * Return input field
	 *
	 * @param mixed $data
	 * @param array $fields
	 * @return string
	 */
	protected function build_input( $data, array $fields = array() ) {
		return parent::build_input( $data, $fields ) . $this->length_helper( $data );
	}

	/**
	 * Returns length helper
	 *
	 * @param string $data
	 * @return string
	 */
	protected function length_helper( $data ) {
		if ( ( $this->min || $this->max ) && $this->length_helper ) {
			$notice     = array();
			$class_name = 'ok';
			if ( $this->min ) {
				$notice[] = sprintf( $this->__( '%s chars or more' ), number_format( $this->min ) );
				if ( $this->min > mb_strlen( $data, 'utf-8' ) ) {
					$class_name = 'ng';
				}
			}
			if ( $this->max ) {
				$notice[] = sprintf( $this->__( '%s chars or less' ), number_format( $this->max ) );
				if ( $this->max < mb_strlen( $data, 'utf-8' ) ) {
					$class_name = 'ng';
				}
			}

			return sprintf(
				'<p class="char-counter %s"><i class="dashicons"></i> <strong>%s</strong><span> %s</span><small>%s</small></p>',
				$class_name,
				mb_strlen( $data, 'utf-8' ),
				$this->__( 'Letters' ),
				implode( ', ', $notice )
			);
		} else {
			return '';
		}
	}

	/**
	 * Field arguments
	 *
	 * @return array
	 */
	protected function get_field_arguments() {
		$args = parent::get_field_arguments();
		if ( $this->min ) {
			$args['data-min-length'] = $this->min;
		}
		if ( $this->max ) {
			$args['data-max-length'] = $this->max;
		}
		if ( is_admin() ) {
			$args['class'] = 'regular-text';
		}
		return $args;
	}

	/**
	 * Validator
	 *
	 * @param mixed $value
	 * @return bool
	 * @throws \WPametu\Exception\ValidateException
	 */
	protected function validate( $value ) {
		if ( parent::validate( $value ) ) {
			$length = mb_strlen( $value, 'utf-8' );
			if ( $this->min && $this->min > $length ) {
				throw new ValidateException( sprintf( $this->__( 'Fields %s must be %d digits and more.' ), $this->label, $this->min ) );
			}
			if ( $this->max && $this->max < $length ) {
				throw new ValidateException( sprintf( $this->__( 'Fields %s must be %d digits and fewer.' ), $this->label, $this->max ) );
			}
		}
		return true;
	}

}
