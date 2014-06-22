<?php

namespace WPametu\HTTP;

use WPametu\Pattern;

/**
 * Utility class for handle input object
 *
 * @package WPametu\HTTP
 * @author Takahashi Fumiki
 * @since 0.1
 * 
 */
final class Input extends Pattern\Singleton
{

	protected function __construct( array $argument ) {}

	
	/**
	 * Returns $_GET
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function get($key){
		if(isset($_GET[$key])){
			return $this->sanitize($_GET[$key]);
		}else{
			return null;
		}
	}
	
	
	/**
	 * Returns $_POST
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function post($key){
		if(isset($_POST[$key])){
			return $this->sanitize($_POST[$key]);
		}else{
			return null;
		}
	}
	
	
	
	/**
	 * Returns $_REQUEST
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function request($key){
		if(isset($_REQUEST[$key])){
			return $this->sanitize($_REQUEST[$key]);
		}else{
			return null;
		}
	}
	
	
	
	/**
	 * Returns post body
	 * 
	 * This method is usefull for typical XML API.
	 * 
	 * @return string
	 */
	public function post_body(){
		return file_get_contents('php://input');
	}
	
	
	/**
	 * Sanitize super globals
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	private function sanitize($value){
		return $value;
	}
}
