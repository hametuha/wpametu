<?php

namespace WPametu\UI\Field;


class Select extends Multiple {


	protected $close_tag = '</select>';

	/**
	 * Get open tag
	 *
	 * @param string $data
	 * @param array $fields
	 * @return string
	 */
	protected function get_open_tag( $data, array $fields = array() ) {
		return sprintf( '<select name="%1$s" id="%1$s">', $this->get_name() );
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
			'<option value="%s"%s>%s</option>',
			esc_attr( $key ),
			selected( $key, $data, false ),
			esc_html( $label )
		);
	}
}
