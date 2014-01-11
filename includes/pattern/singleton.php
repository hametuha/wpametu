<?php

namespace WPametu\Pattern;


/**
 * Singleton Class
 *
 * @package WPametu\Pattern
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
	private static $instances = [];

	/**
	 * @var array
	 */
	protected static $default_arguments = [];

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
	 * @param array $argument
	 * @return \WPametu\Pattern\Singleton
	 */
	public static function getInstance( array $argument = [] ){
		$class_name = get_called_class();
		if(!self::hasInstance($class_name)){
			self::init($argument);
		}
		return self::$instances[$class_name];
	}

	/**
	 * Initialize method.
	 */
	protected static function init( array $argument = [] ){
		$class_name = get_called_class();
		if(!self::hasInstance($class_name)){
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
	private static function hasInstance($class_name){
		return isset(self::$instances[$class_name]) && !is_null(self::$instances[$class_name]);
	}
}