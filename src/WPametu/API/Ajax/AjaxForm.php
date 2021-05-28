<?php

namespace WPametu\API\Ajax;


/**
 * Form with Ajax
 *
 * @package WPametu
 */
abstract class AjaxForm extends AjaxBase {



	/**
	 * Open form
	 *
	 * @param array $attributes
	 * @param bool $echo
	 * @return string
	 */
	protected  function form_open( array $attributes = array(), $echo = true ) {
		$attributes = array_merge(
			array(
				'method' => $this->method,
				'action' => $this->ajax_url(),
			),
			$attributes
		);
		$str        = array();
		foreach ( $attributes as $key => $value ) {
			$str[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}
		$str   = implode( ' ', $str );
		$html  = "<form {$str}>";
		$html .= sprintf( '<input type="hidden" name="action" value="%s" />', esc_attr( $this->action ) );
		$html .= $this->nonce_field( '_wpnonce', false, false );
		if ( $echo ) {
			echo $html;
		}
		return $html;
	}

	/**
	 * Close form
	 *
	 * @param bool $echo
	 * @return string
	 */
	protected  function form_close( $echo = true ) {
		$form = '</form>';
		if ( $echo ) {
			echo $form;
		}
		return $form;
	}

	/**
	 * Display form control
	 *
	 * @param string $slug
	 * @param string $name
	 * @param array $attributes
	 */
	public static function form( $slug, $name = '', array $attributes = array() ) {
		$class_name = get_called_class();
		/** @var AjaxBaseForm $instance */
		$instance = $class_name::get_instance();
		$instance->form_open( $attributes );
		$args = $instance->form_arguments();
		$instance->load_template( $slug, $name, $args );
		$instance->form_close();
	}

	/**
	 * Executed on form display.
	 *
	 * If you want to pass some variables to form elements,
	 * override this function.
	 * Must return an array and it will be extracted.
	 *
	 * @return array
	 */
	protected function form_arguments() {
		return array();
	}
}
