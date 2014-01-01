<?php
/**
 * Created by PhpStorm.
 * User: guy
 * Date: 2013/11/19
 * Time: 0:58
 */

namespace WPametu\Data\Table;

use WPametu\Pattern, WPametu\Data;

abstract class Table extends Pattern\Singleton
{

	/**
	 * Version of this table
	 *
	 * Version is used when plugin is updated.
	 *
	 * @var string
	 */
	protected $version = '1.0';


	/**
	 * This tables name
	 *
	 * WP's table prefix will be prepend to
	 * at constructor.
	 * e.g. 'sample_talbe' will be 'wp_sample_table'
	 *
	 * @var string
	 */
	protected $name = '';



	/**
	 * Constructor
	 *
	 * @throws \UnexpectedValueException
	 * @param array $arguments Not used.
	 */
	protected function __construct( array $arguments = array()){
		// Check if name is set
		if(empty($this->name)){
			throw new \UnexpectedValueException(sprintf('You must set $name variable of %s', get_called_class()));
		}
	}



	/**
	 * Executed after constuct finished
	 *
	 * @return void
	 */
	abstract protected function initialized();



	/**
	 * Should return creation SQL.
	 *
	 * <code>
	 * return "
	 *     CREATE
	 * ";
	 * </code>
	 *
	 * @return mixed
	 */
	protected function create($table_name, $charset, $storage_engine);



	/**
	 * Should return MySQL storage engine
	 *
	 * If you need to change storage engine,
	 * override this function. Default is InnoDB.
	 * You can use WPametu\Data\Engine class.
	 *
	 * <code>
	 * return WPametu\Data\Engine::INNODB;
	 * </code>
	 *
	 * @return String
	 */
	protected function storage_engine(){
		return Data\Engine::INNODB;
	}
}