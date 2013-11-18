<?php

namespace WPametu\Traits;


trait i18n
{

	/**
	 * Short hand for printf()
	 *
	 * @param $string
	 */
	protected function pf($string){
		call_user_func_array('printf', func_get_args());
	}

	/**
	 * Short hand for sprintf()
	 *
	 * @param $string
	 * @return mixed
	 */
	protected function spf($string){
		return call_user_func_array('sprintf', func_get_args());
	}

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
	protected function __($string){
		return __($string, $this->i18n_domain);
	}



	/**
	 * Shorthand for _e()
	 *
	 * @param string $string
	 * @return void
	 */
	protected function _e($string){
		_e($string, $this->i18n_domain);
	}



	/**
	 * Shorthand for _x()
	 *
	 * @param $string
	 * @param $context
	 * @return string|void
	 */
	protected function _x($string, $context){
		return _x($string, $context, $this->i18n_domain);
	}



	/**
	 * Short hand for _ex()
	 *
	 * @param $string
	 * @param $context
	 * @return void
	 */
	protected function _ex($string, $context){
		_ex($string, $context, $this->i18n_domain);
	}
} 