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
	 * Instacne list
	 *
	 * Key is class name, Value is class instance
	 *
	 * @var array
	 */
	private static $instances = array();

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
	 * @return \WPametu\Pattern\Singleton
	 */
	public static function get_instance( array $argument = array() ){
		$class_name = get_called_class();
		if(!self::has_instance($class_name)){
			self::init($argument);
		}
		return self::$instances[$class_name];
	}

	/**
	 * Initialize method.
	 */
	protected static function init( array $argument = array() ){
		$class_name = get_called_class();
		if(!self::has_instance($class_name)){
			// Merge arguments to default array
			$merged = array_merge(self::$default_arguments, $argument);
			self::$instances[$class_name] = new $class_name($merged);
		}
	}

	/**
	 * Returns if instance exists
	 *
	 * @param $class_name
	 * @return boolean
	 */
	private static function has_instance($class_name){
		return isset(self::$instances[$class_name]) && !is_null(self::$instances[$class_name]);
	}
}