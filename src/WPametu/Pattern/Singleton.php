<?php

namespace WPametu\Pattern;

/**
 * Singleton Pattern
 *
 * @package WPametu
 */
abstract class Singleton {
	/**
	 * @var array
	 */
	private static $instances = [];

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = [] ) {
		// Implement constructor if required.
	}

	/**
	 * Singleton initialize method
	 *
	 * @param array $setting
	 *
	 * @return static
	 */
	final public static function get_instance( array $setting = [] ) {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name( $setting );
		}

		return self::$instances[ $class_name ];
	}
}
