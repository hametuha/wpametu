<?php

namespace WPametu\UI\Field;

use WPametu\Exception\PropertyException;
use WPametu\Exception\ValidateException;

/**
 * Multiple selection
 *
 * @package WPametu
 * @property-read array $options
 * @property-read string $default
 */
abstract class Multiple extends Input {


	/**
	 * Close tag
	 *
	 * @var string
	 */
	protected $close_tag = '';

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
				'options' => array(),
				'default' => '',
			)
		);
	}

	/**
	 * @param mixed $value
	 * @return bool
	 * @throws \WPametu\Exception\ValidateException
	 */
	protected function validate( $value ) {
		if ( parent::validate( $value ) ) {
			if ( ! $this->is_allowed_value( $value ) ) {
				throw new ValidateException( sprintf( $this->__( 'Field %s is invalid.' ), $this->label ) );
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Show input labels
	 *
	 * @param mixed $data
	 * @param array $fields
	 * @return string
	 */
	protected function build_input( $data, array $fields = array() ) {
		$input   = $this->get_open_tag( $data, $fields );
		$counter = 1;
		if ( '' === $data ) {
			$data = $this->default;
		}
		foreach ( $this->get_options() as $key => $label ) {
			$input .= $this->get_option( $key, $label, $counter, $data, $fields );
			++$counter;
		}
		$input .= $this->close_tag;
		return $input;
	}

	/**
	 * Return name
	 *
	 * @param int $index
	 * @return string
	 */
	protected function get_name( $index = 0 ) {
		return $this->name;
	}

	/**
	 * Get open tag
	 *
	 * @param string $data
	 * @param array $fields
	 * @return string
	 */
	abstract protected function get_open_tag( $data, array $fields = array() );

	/**
	 * Get fields input
	 *
	 * @param string $key
	 * @param string $label
	 * @param int $counter
	 * @param string $data
	 * @param array $fields
	 * @return string
	 */
	abstract protected function get_option( $key, $label, $counter, $data, array $fields = array() );

	/**
	 * Detect if this value is allowed
	 *
	 * @param string $value
	 * @return bool
	 */
	protected function is_allowed_value( $value ) {
		return array_key_exists( $value, $this->options );
	}

	/**
	 * Multiple has no field
	 *
	 * @return array
	 */
	protected function get_field_arguments() {
		return array();
	}

	/**
	 * Test setting
	 *
	 * @param array $setting
	 * @throws \WPametu\Exception\PropertyException
	 */
	protected function test_setting( array $setting = array() ) {
		if ( empty( $setting['options'] ) ) {
			throw new PropertyException( 'options', get_called_class() );
		}
	}

	/**
	 * Get options
	 *
	 * @return array
	 */
	protected function get_options() {
		return $this->options;
	}
}
