<?php

namespace WPametu\UI\Field;
use WPametu\Exception\ValidateException;


/**
 * Input field base
 *
 * @package WPametu
 * @property-read string $placeholder
 */
abstract class Input extends Base {


	/**
	 * Input type
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Get field
	 *
	 * @param \WP_Post $post
	 * @return string
	 */
	protected function get_field( \WP_Post $post ) {
		$fields = array();
		foreach ( $this->get_field_arguments() as $key => $val ) {
			$fields[] = sprintf( '%s="%s"', $key, esc_attr( $val ) );
		}
		return $this->build_input( $this->get_data( $post ), $fields );
	}

	/**
	 * build input field
	 *
	 * @param mixed $data
	 * @param array $fields
	 * @return string
	 */
	protected function build_input( $data, array $fields = array() ) {
		$fields = implode( ' ', $fields );
		return sprintf(
			'<input id="%1$s" name="%1$s" type="%2$s" %3$s value="%4$s" />',
			$this->name,
			esc_attr( $this->get_input_type() ),
			$fields,
			esc_attr( $data )
		);
	}

	/**
	 * Get input type.
	 *
	 * @return string
	 */
	protected function get_input_type() {
		return $this->type;
	}

	/**
	 * @param array $setting
	 * @return array
	 */
	protected function parse_args( array $setting ) {
		return wp_parse_args(
			parent::parse_args( $setting ),
			array(
				'placeholder' => '',
			)
		);
	}

	/**
	 * @return array
	 */
	protected function get_field_arguments() {
		return array(
			'placeholder' => $this->placeholder,
		);
	}

	/**
	 * Validate values
	 *
	 * @param mixed $value
	 * @return bool
	 * @throws ValidateException
	 */
	protected function validate( $value ) {
		if ( parent::validate( $value ) ) {
			if ( $this->required && empty( $value ) ) {
				// translators: %s is field label.
				throw new ValidateException( sprintf( __( 'Field %s is required.', 'wpametu' ), $this->label ) );
			}
			return true;
		} else {
			return false;
		}
	}
}
