<?php

namespace WPametu\Pattern;


/**
 * Singleton Class
 *
 * @author Takahashi Fumiki
 * @since 0.1
 */
abstract class  Singleton
{

	/**
	 * @var Singleton
	 */
	protected static $instance = null;

	/**
	 * @var array
	 */
	protected static $default_arguments = array();

	/**
	 * Constructor
	 *
	 * Constructor should not be public.
	 *
	 * @param array $argument
	 */
	abstract protected function __construct( array $argument );

	/**
	 * Get instance
	 *
	 * @return Singleton
	 */
	public static function get_instance( $argument = null ){
		if(!self::$instance){
			$class_name = get_called_class();
			self::init($argument);
		}
		return self::$instance;
	}

	/**
	 * Initialize method.
	 *
	 * @return void
	 */
	protected static function init( array $argument = array() ){
		if(!self::$instance){
			// Merge arguments to default array
			$merged = array_merge(self::$default_arguments, $argument);
			$class_name = get_called_class();
			self::$instance = new $class_name($merged);
		}
	}
}