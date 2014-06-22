<?php
/**
 * Created by PhpStorm.
 * User: guy
 * Date: 2013/11/12
 * Time: 3:37
 */

namespace WPametu\UI\Theme;


abstract class Table {

	/**
	 * Dtermin
	 *
	 * @var bool
	 */
	protected $show_search_box = false;

	/**
	 * Key-Value data of column header
	 *
	 * @var string
	 */
	protected $class_name = array('list-table');

	/**
	 * Column table
	 *
	 * @var array
	 */
	protected $header = array();

} 