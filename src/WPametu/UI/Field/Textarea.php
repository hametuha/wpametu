<?php

namespace WPametu\UI\Field;


/**
 * TextArea
 *
 * @package WPametu
 * @property-read int $rows
 */
class TextArea extends Text {


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
				'min'  => 0,
				'max'  => 0,
				'rows' => 3,
			)
		);
	}

	/**
	 * Make input field
	 *
	 * @param mixed $data
	 * @param array $fields
	 * @return string
	 */
	protected function build_input( $data, array $fields = array() ) {
		$fields = implode( ' ', $fields );
		return sprintf(
			'<textarea id="%1$s" name="%1$s" %2$s>%3$s</textarea>%4$s',
			$this->name,
			$fields,
			esc_textarea( $data ),
			$this->length_helper( $data )
		);
	}

	/**
	 * Get field arguments
	 *
	 * @return array
	 */
	protected function get_field_arguments() {
		return array_merge(
			parent::get_field_arguments(),
			array(
				'rows' => $this->rows,
			)
		);
	}

}
