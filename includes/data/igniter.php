<?php
/**
 * Created by PhpStorm.
 * User: guy
 * Date: 2013/11/25
 * Time: 20:39
 */

namespace WPametu\Data;

/**
 * Class Igniter
 * @package WPametu
 * @property-read array $default_models
 */
class Igniter
{


	public function __get($name){
		switch($name){
			case 'default_models':
				return array();
				break;
		}
	}
} 