<?php

namespace WPametu\UI\Field;


/**
 * Class TokenInput
 *
 * @package WPametu
 * @property-read string $action
 * @property-read array $args
 */
abstract class TokenInput extends Text {


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
				'args' => array(),
			)
		);
	}

	/**
	 * Field arguments
	 *
	 * @return array
	 */
	protected function get_field_arguments() {
		$args          = parent::get_field_arguments();
		$args['class'] = 'token-input';
		$url           = $this->get_endpoint();
		if ( ! empty( $this->args ) ) {
			$url = add_query_arg( $this->args, $url );
		}
		$args['data-endpoint'] = $url;
		$args['placeholder']   = $this->__( 'Type and search...' );
		return $args;
	}

	/**
	 * Add prepopulates
	 *
	 * @param mixed $data
	 * @param array $fields
	 * @return string|void
	 */
	protected function build_input( $data, array $fields = array() ) {
		$prepopulates = $this->get_prepopulates( $data );
		$ids          = array();
		foreach ( $prepopulates as $obj ) {
			$ids[] = $obj['id'];
		}
		$prepopulates = json_encode( $prepopulates );
		$input        = parent::build_input( '', $fields );
		$input       .= <<<HTML
            <script>
                WPametuTokenInput = window.WPametuTokenInput || {};
                WPametuTokenInput['{$this->name}'] = {$prepopulates};
            </script>
HTML;
		return $input;
	}

	/**
	 * Get prepopulated data
	 *
	 * @param array $data
	 * @return array
	 */
	abstract protected function get_prepopulates( $data);

	/**
	 * @return mixed
	 */
	abstract protected function get_endpoint();


}
