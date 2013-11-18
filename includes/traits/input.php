<?php
/**
 * Created by PhpStorm.
 * User: guy
 * Date: 2013/11/16
 * Time: 23:40
 */

namespace WPametu\Traits;

use WPametu\HTTP;

/**
 * Trait Input
 *
 * This traits add public member which helps HTTP inptus
 *
 * @package WPametu
 * @property-read \WPametu\HTTP\Input $input
 */
trait Input
{

	/**
	 * Getter
	 *
	 * @param $name
	 * @return mixed
	 */
	public function __get($name){
		if('input' == $name){
			return HTTP\Input::get_instance();
		}
	}
} 