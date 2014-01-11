<?php

namespace WPametu;

/**
 * Base class for all class
 * 
 * @author Takahashi Fumiki
 * @since 0.1
 * @property-read \WPametu\Input $input Utilitiy class for handle get or post
 */
abstract class Base {
	
	use Traits\Util;
	
}
