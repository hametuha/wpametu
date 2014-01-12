<?php

namespace WPametu\Traits;

/**
 * Utility trait
 *
 * @package WPametu\Traits
 * @property-read \WPametu\HTTP\Input $input
 * @property-read \WPametu\HTTP\Url $url
 * @property-read \WPametu\Utils\String $str
 * @author Takahashi Fumiki
 */
trait Util
{

    use Prg;

	/**
	 * Getter
	 *
	 * @param $name
	 * @return mixed
	 */
	public function __get($name){
		switch($name){
			case 'url':
				return \WPametu\HTTP\Url::getInstance();
				break;
			case 'str':
				return \WPametu\Utils\String::getInstance();
				break;
			case 'input':
				return \WPametu\HTTP\Input::getInstance();
				break;
		}
	}
} 