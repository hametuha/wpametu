<?php

namespace WPametu\Pattern;

/**
 * You can't new this.
 *
 * @package WPametu
 */
abstract class NoConstructor {


	final private function __construct() {
		// You can't override this!
	}
}
