<?php

namespace WPametu\UI\Field;



/**
 * Class Radio
 * @package WPametu
 * @property-read bool $inline
 */
class Radio extends Multiple {


	protected $type = 'radio';


	/**
	 * @param array $setting
	 * @return array
	 */
	protected function parse_args( array $setting ) {
		return wp_parse_args(
			parent::parse_args( $setting ),
			array(
				'inline' => false,
			)
		);
	}

	/**
	 * Open tag
	 *
	 * @param string $data
	 * @param array $fields
	 * @return string
	 */
	protected function get_open_tag( $data, array $fields = array() ) {
		return '<p class="radio-container">';
	}

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
	protected function get_option( $key, $label, $counter, $data, array $fields = array() ) {
		return sprintf(
			'<label%6$s><input type="radio" name="%1$s" id="%1$s-%2$d" value="%3$s" %4$s /> %5$s </label>',
			$this->get_name(),
			$counter,
			esc_attr( $key ),
			checked( $key, $data, false ),
			esc_html( $label ),
			$this->inline ? ' class="inline"' : ''
		);
	}
}
