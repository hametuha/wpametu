<?php

namespace WPametu\Tool;


use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;

/**
 * Batch class
 *
 * @package WPametu\Tool
 * @property-read string $version
 * @property-read string $title
 * @property-read string $description
 * @property-read int|false $last_executed
 */
abstract class Batch extends Singleton
{

	use i18n;

	/**
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * @var int
	 */
	protected $per_process = 100;

	/**
	 * Return title
	 *
	 * @return string
	 */
	abstract protected function get_title();

	/**
	 * Return description
	 *
	 * @return string
	 */
	abstract protected function get_description();

	/**
	 * Do process
	 *
	 * @param $page
	 *
	 * @return BatchResult|null
	 */
	abstract public function process($page);

	/**
	 * Return success message
	 *
	 * @param int $page
	 *
	 * @return string
	 */
	public function success_message($page){
		return sprintf($this->__('%d has been done.'), $page * $this->per_process);
	}

	/**
	 * Abort batch process
	 *
	 * @param string $message
	 * @param int $code
	 * @throws \RuntimeException
	 */
	protected function abort($message, $code = 500){
		throw new \RuntimeException($message, $code);
	}

	/**
	 * Get total count
	 *
	 * If amount of batch target can be explicit,
	 * override this function.
	 *
	 * @return int
	 */
	protected function get_total(){
		return 0;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return null|string
	 */
	public function __get($name){
		switch( $name ){
			case 'title':
			case 'version':
			case 'description':
				$method = 'get_'.$name;
				return (string) $this->{$method}();
				break;
			case 'last_executed':
				$option = get_option('wpametu_batch_record', []);
				return isset($option[get_called_class()]) ? $option[get_called_class()] : false;
				break;
			default:
				return null;
				break;
		}
	}

}
