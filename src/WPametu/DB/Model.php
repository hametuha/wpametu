<?php

namespace WPametu\DB;


use WPametu\Pattern\Singleton;

/**
 * Model class
 *
 * @package WPametu
 * @property-read string $table
 */
abstract class Model extends QueryBuilder {


	/**
	 * If specified, try to add updated column
	 *
	 * @var bool
	 */
	protected $updated_column = false;

	/**
	 * Specify default column's placeholder.
	 *
	 * <code>
	 * // column => placeholder
	 * [ 'date' => '%s', 'post_id' => '%d']
	 * </code>
	 *
	 * @var array
	 */
	protected $default_placeholder = array();

	/**
	 * Primary key for this model
	 *
	 * <code>$this_table.$this->primary_key</code> will used for
	 * <code>$this->get()</code> method.
	 *
	 * @var string
	 */
	protected $primary_key = 'ID';

	/**
	 * Result will be convert to this class.
	 *
	 * @var string
	 */
	protected $result_class = '';

	/**
	 * Get found rows
	 *
	 * @return int
	 */
	final public function found_count() {
		return (int) $this->db->get_var( 'SELECT FOUND_ROWS() AS count' );
	}

	/**
	 * Get object with primary key
	 *
	 * @param int $id
	 * @param bool $ignore_cache
	 * @return mixed|null
	 */
	public function get( $id, $ignore_cache = false ) {
		if ( empty( $this->primary_key ) ) {
			return null;
		}
		$row = $this->where( "{$this->table}.{$this->primary_key} = %d", $id )->get_row( '', $ignore_cache );
		if ( $row && $this->result_class ) {
			return new $this->result_class( $row );
		} else {
			return $row;
		}
	}

	/**
	 * @param array $wheres
	 * @param int $limit
	 * @param int $offset
	 * @return array|mixed|null
	 */
	public function find( array $wheres = array(), $limit = 0, $offset = 0 ) {
		if ( ! $limit ) {
			$limit = $this->per_page;
		}
		return $this->calc()->wheres( $wheres )->limit( $limit, $offset * $limit )->result();
	}

	/**
	 * Get var
	 *
	 * @param string $query
	 * @return null|string
	 */
	public function get_var( $query = '' ) {
		if ( ! $query ) {
			$query = $this->build_query();
		}
		$this->clear();
		return $this->db->get_var( $query );
	}

	/**
	 * Returns specified column as array
	 *
	 * @param int $x
	 * @param string $query
	 * @return array
	 */
	public function get_col( $x = 0, $query = '' ) {
		if ( ! $query ) {
			$query = $this->build_query();
		}
		$this->clear();
		return $this->db->get_col( $query, $x );
	}

	/**
	 * Returns row object
	 *
	 * @param string $query
	 * @param bool $ignore_cache
	 * @return mixed|null
	 */
	public function get_row( $query = '', $ignore_cache = false ) {
		if ( empty( $query ) ) {
			$query = $this->build_query();
			$this->clear();
		}
		if ( $this->cache_exist( $query ) && ! $ignore_cache ) {
			return $this->get_cache( $query );
		} else {
			return $this->db->get_row( $query );
		}
	}

	/**
	 * Get results
	 *
	 * @param string $query
	 * @param bool $ignore_cache
	 * @return array|mixed|null
	 */
	public function result( $query = '', $ignore_cache = false ) {
		$result = $this->raw_result( $query, $ignore_cache );
		if ( $result && $this->has_result_class() ) {
			$new_results = array();
			foreach ( $result as $key => $obj ) {
				$new_results[ $key ] = new $this->result_class( $obj );
			}
			return $new_results;
		} else {
			return $result;
		}
	}

	/**
	 * @param string $query
	 * @param bool $ignore_cache
	 * @return mixed|null
	 */
	public function raw_result( $query = '', $ignore_cache = false ) {
		if ( empty( $query ) ) {
			$query = $this->calc()->build_query();
		}
		$this->clear();
		if ( $this->cache_exist( $query ) && ! $ignore_cache ) {
			return $this->get_cache( $query );
		} else {
			return $this->db->get_results( $query );
		}
	}

	/**
	 * Detect if this class has result class
	 *
	 * @return bool
	 */
	protected function has_result_class() {
		return ! empty( $this->result_class ) && class_exists( $this->result_class );
	}

	/**
	 * Insert row
	 *
	 * @param array $values Array with 'column' => 'value'
	 * @param array $place_holders Array of '%s', '%d', '%f'
	 * @param string $table
	 * @return false|int number of rows or false on failure
	 */
	protected function insert( array $values, array $place_holders = array(), $table = '' ) {
		if ( empty( $table ) ) {
			extract( $this->get_default_values( $values, $table, $place_holders ) );
		}
		return $this->db->insert( $table, $values, $place_holders );
	}

	/**
	 * Update table
	 *
	 * @param array $values
	 * @param array $wheres ['column' => 'value'] format.
	 * @param array $place_holders
	 * @param array $where_format
	 * @param string $table
	 * @return false|int
	 */
	protected function update( array $values, array $wheres = array(), array $place_holders = array(), array $where_format = array(), $table = '' ) {
		if ( empty( $table ) ) {
			extract( $this->get_default_values( $values, $table, $place_holders ) );
		}
		if ( empty( $where_format ) && ! empty( $this->default_placeholder ) ) {
			foreach ( $wheres as $column => $valud ) {
				if ( isset( $this->default_placeholder[ $column ] ) ) {
					$where_format[] = $this->default_placeholder[ $column ];
				}
			}
		}
		return $this->db->update( $table, $values, $wheres, $place_holders, $where_format );
	}

	/**
	 * Get default value
	 *
	 * @param array $values
	 * @param string $table
	 * @param array $place_holders
	 * @return array
	 */
	protected function get_default_values( array $values = array(), $table = '', array $place_holders = array() ) {
		if ( empty( $table ) ) {
			$table = $this->table;
		}
		if ( $table == $this->table ) {
			// Overwrite place holder
			if ( $this->default_placeholder ) {
				$default_place_holder = array();
				foreach ( $values as $column => $value ) {
					$default_place_holder[] = $this->default_placeholder[ $column ];
				}
				if ( empty( $place_holders ) ) {
					$place_holders = $default_place_holder;
				}
			}
			// Add updated column
			if ( $this->updated_column ) {
				if ( ! isset( $values[ $this->updated_column ] ) ) {
					$values[ $this->updated_column ] = current_time( 'mysql' );
					$place_holders[]                 = '%s';
				}
			}
		}
		return compact( 'table', 'place_holders', 'values' );
	}

	/**
	 * Delete record
	 *
	 * @param array $wheres
	 * @param string $table_name If not specified, <code>$this->table</code> will be used.
	 * @return false|int
	 * @throws \Exception
	 */
	protected function delete_where( array $wheres, $table_name = '' ) {
		if ( empty( $table_name ) ) {
			$table_name = $this->table;
		}
		if ( ! empty( $wheres ) ) {
			$where_clause = array();
			foreach ( $wheres as $condition ) {
				if ( count( $condition ) != 4 ) {
					throw new \Exception( 'Where section of delete query has 4 parameters(column, operand, value, placeholder)', 500 );
				}
				list($column, $operand, $value, $replace) = $condition;
				if ( is_array( $value ) ) {
					$where_clause[] = "{$column} {$operand} (" . implode(
						', ',
						array_map(
							function( $val ) use ( $replace ) {
								return $this->db->prepare( $replace, $val );
							},
							$value
						)
					) . ')';
				} else {
					$where_clause[] = $this->db->prepare( "{$column} {$operand} {$replace}", $value );
				}
			}
			$where_clause = implode( ' AND ', $where_clause );
			$query        = <<<SQL
              DELETE FROM {$table_name}
              WHERE {$where_clause}
SQL;
			return $this->db->query( $query );
		}
		return 0;
	}
}
