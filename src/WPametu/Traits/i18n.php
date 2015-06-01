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
 * @package WPametu\Traits
 */
trait i18n
{

	/**
	 * Domain name
	 *
	 * @var string
	 */
	protected $i18n_domain = 'wpametu';

	/**
	 * Shorthand for __()
	 *
	 * @param string $string
	 * @return string|void
	 */
	public function __($string){
		return __($string, $this->i18n_domain);
	}



	/**
	 * Shorthand for _e()
	 *
	 * @param string $string
	 * @return void
	 */
	public function _e($string){
		_e($string, $this->i18n_domain);
	}



	/**
	 * Shorthand for _x()
	 *
	 * @param $string
	 * @param $context
	 * @return string|void
	 */
	public function _x($string, $context){
		return _x($string, $context, $this->i18n_domain);
	}



	/**
	 * Short hand for _ex()
	 *
	 * @param $string
	 * @param $context
	 * @return void
	 */
	public function _ex($string, $context){
		_ex($string, $context, $this->i18n_domain);
	}
} 