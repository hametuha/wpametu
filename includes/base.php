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
	
	
	/**
	 * Magic method getter
	 * 
	 * @param string $property
	 * @return mixed
	 */
	public function __get($property){
		switch($property){
			case 'input':
				return Input::getInstance();
				break;
			default:
				return null;
				break;
		}
	}
	
}
