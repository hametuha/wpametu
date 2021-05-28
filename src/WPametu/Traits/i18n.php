<?php

namespace WPametu\Traits;


/**
 * Internationalization utility
 *
 * To override this trait, just change property $i18n to your
 * translation domain.
 * You have to register translation domain by yourself.
 *
 * <code>
 * load_plugin_textdomain( 'your_domain', false, '/path/to/your/mo/dir/' );
 * </code>
 *
 * @deprecated
 * @package WPametu
 * phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralText
 * phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralContext
 */
trait i18n {

	/**
	 * Domain name
	 *
	 * @var string
	 */
	protected $i18n_domain = 'wpametu';

	/**
	 * Shorthand for __()
	 *
	 * @deprecated
	 * @param string $string
	 * @return string|void
	 */
	public function __( $string ) {
		return __( $string, 'wpametu' );
	}

	/**
	 * Shorthand for _e()
	 *
	 * @deprecated
	 * @param string $string
	 * @return void
	 */
	public function _e( $string ) {
		_e( $string, 'wpametu' );
	}

	/**
	 * Shorthand for _x()
	 *
	 * @deprecated
	 * @param $string
	 * @param $context
	 * @return string|void
	 */
	public function _x( $string, $context ) {
		return _x( $string, $context, 'wpametu' );
	}

	/**
	 * Short hand for _ex()
	 *
	 * @deprecated
	 * @param $string
	 * @param $context
	 * @return void
	 */
	public function _ex( $string, $context ) {
		_ex( $string, $context, 'wpametu' );
	}
}
