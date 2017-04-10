<?php

namespace WPametu\Utility;

use WPametu\Pattern\Singleton;


/**
 * Utility class for array
 *
 * @package WPametu
 */
class IteratorWalker extends Singleton {
	/**
	 * Detect if array member with specified key exits.
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function key_exists( array $array, $key, $value ) {
		return false !== $this->key_search( $array, $key, $value );
	}

	/**
	 * Get index of array member with specified key
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return false|int|string
	 */
	public function key_search( array $array, $key, $value ) {
		foreach ( $array as $key => $a ) {
			if ( isset( $a[ $key ] ) && $a[ $key ] == $value ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Search if property exists
	 *
	 * @param array $array
	 * @param string $property
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function prop_exists( array $array, $property, $value ) {
		return false !== $this->prop_search( $array, $property, $value );
	}

	/**
	 * Search array member with specified property
	 *
	 * @param array $array
	 * @param string $property
	 * @param mixed $value
	 *
	 * @return false|int|string
	 */
	public function prop_search( array $array, $property, $value ) {
		foreach ( $array as $key => $a ) {
			if ( property_exists( $a, $property ) && $a->{$property} == $value ) {
				return $key;
			}
		}

		return false;
	}
}
