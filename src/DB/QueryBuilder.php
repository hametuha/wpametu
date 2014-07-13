<?php

namespace WPametu\DB;


use WPametu\Pattern\Singleton;

/**
 * QueryBuilder
 *
 * @package WPametu\DB
 * @property-read \wpdb $db
 * @property-read string $table
 */
abstract class QueryBuilder extends Singleton
{


    /**
     * Table name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Related table names.
     *
     * You can get this each table name prefixed with getter.
     *
     * <code>
     * // Define like this.
     * protected $related = ['posts', 'terms'];
     *
     * // You can get with table prefix.
     * $this->posts; // -> 'wp_posts'
     * $this->terms; // -> 'wp_terms'
     * </code>
     *
     * @var array
     */
    protected $related = [];

    /**
     * Add SQL_CALC_FOUND_ROWS or not
     *
     * @var bool
     */
    private $_calc_rows = false;

    /**
     * @var bool
     */
    private $_distinct = '';

    /**
     * @var array
     */
    private $_select = [];

    /**
     * @var string
     */
    private $_from = '';

    /**
     * @var array
     */
    private $_join = [];

    /**
     * @var array
     */
    private $_wheres = [];

    /**
     * @var array
     */
    private $_group_by = [];

    /**
     * @var array
     */
    private $_order_by = [];

    /**
     * @var array
     */
    private $_limit = [];

    /**
     * Default per page
     *
     * @var int
     */
    protected $per_page = 20;

    /**
     * Cache length
     *
     * @var int
     */
    protected $cache_limit = 20;

    /**
     * Query cache
     *
     * @var array
     */
    private $query_cache = [];


    /**
     * Build query with current setting
     *
     * @return string
     */
    final protected function build_query(){
        $select = empty($this->_select) ? $this->default_select() : implode(', ', $this->_select);
        $calc = $this->_calc_rows ? ' SQL_CALC_FOUND_ROWS' : '';
        $distinct = $this->_distinct ? sprintf('DISTINCT(%s)', $this->escape_column_name($this->_distinct)) : '';
        $from = empty($this->_from) ? $this->table : $this->_from;
        // Build query
        $sql = <<<SQL
          SELECT{$calc} {$distinct} {$select} FROM {$from}
SQL;
        // Add join
        $join = [];
        if( !empty($this->_join) ){
            $join = $this->_join;
        }else{
            $default_join = $this->default_join();
            if( !empty($default_join) ){
                $join = $default_join;
            }
        }
        foreach($join as list($table, $on, $method)){
            $method = 'INNER' === strtoupper($method) ? 'INNER' : 'LEFT';
            $sql .= <<<SQL
            {$method} JOIN {$table}
            ON {$on}
SQL;
        }
        // Add where
        if( !empty($this->_wheres) ){
            $where_clause = $this->build_where($this->_wheres);
            $sql .= <<<SQL
            WHERE {$where_clause}
SQL;
        }
        // Add group by
        if( !empty($this->_group_by) ){
            $group_by = implode(', ', $this->_group_by);
            $sql .= <<<SQL
            GROUP BY {$group_by}
SQL;
        }
        // Add order by
        if( !empty($this->_order_by) ){
            $order_by = implode(', ', $this->_order_by);
            $sql .= <<<SQL
            ORDER BY {$order_by}
SQL;
        }
        // Add limit
        if( !empty($this->_limit) ){
            list($per_page, $offset) = $this->_limit;
            $limit = <<<SQL
            LIMIT %d, %d
SQL;
            $sql .= sprintf($limit, $offset, $per_page);
        }
        return $sql;
    }

    /**
     * Build where clause
     *
     * @param array $where_array
     * @return string
     */
    private function build_where(array $where_array){
        $where_clause = [];
        $counter = 0;
        foreach( $where_array as list($glue, $where) ){
            if( $counter ){
                $where_clause[] = $glue;
            }
            if( is_array($where) ){
                $where_clause[] = $this->build_where($where_array);
            }else{
                $where_clause[] = sprintf('(%s)', $where);
            }
            $counter++;
        }
        return sprintf('(%s)', implode(' ', $where_clause));
    }

    /**
     * Set select param
     *
     * @param string $name
     * @return $this
     */
    final protected function select($name){
        $this->_select[] = $this->escape_column_name($name);
        return $this;
    }

    /**
     * Set SQL_CALC_FOUND_ROWS
     *
     * @param bool $on
     * @return $this
     */
    final protected function calc($on = true){
        $this->_calc_rows = (bool)$on;
        return $this;
    }

    /**
     * Change distinct
     *
     * @param string $column
     * @return $this
     */
    final protected function distinct($column){
        $this->_distinct = $column;
        return $this;
    }

    /**
     * Set from table
     *
     * @param string $table
     * @return $this
     */
    final protected function from($table){
        $this->_from = $table;
        return $this;
    }

    /**
     * Set join
     *
     * @param string $table
     * @param $on
     * @param string $method
     * @return $this
     */
    final protected function join($table, $on, $method = 'left'){
        $this->_join[] = [$table, $on, $method];
        return $this;
    }

    /**
     * Add where group
     *
     * @param string $where
     * @param mixed $replace
     * @param bool $or
     * @return $this
     */
    final protected function where($where, $replace, $or = false){
        $this->_wheres[] = [$this->and_or($or), $this->db->prepare($where, $replace)];
        return $this;
    }

    /**
     * Add where not null clause
     *
     * @param string $column
     * @param bool $or Default false
     * @return $this
     */
    final protected function where_not_null($column, $or = false){
        $this->_wheres[] = [$this->and_or($or), sprintf("%s IS NOT NULL", $this->escape_column_name($column))];
        return $this;
    }

    /**
     * Add Where LIKE
     *
     * @param string $column
     * @param string $replace
     * @param string $position Position of %. 'left', 'right' or 'both'
     * @param bool $or
     * @return $this
     */
    final protected function where_like($column, $replace, $position = 'both', $or = false){
        switch( strtolower($position) ){
            case 'left':
                $replace = '%'.$replace;
                break;
            case 'right':
                $replace = $replace.'%';
                break;
            default:
                $replace = '%'.$replace.'%';
                break;
        }
        $column = $this->escape_column_name($column);
        $this->_wheres[] = [$this->and_or($or), $this->db->prepare("{$column} LIKE %s", $replace)];
        return $this;
    }

    /**
     * Add where in one shot
     *
     * @param array $wheres
     * @param bool $or
     * @return $this
     */
    final protected function wheres(array $wheres, $or = false){
        foreach($wheres as $where => $replace){
            $this->where($where, $replace, $or);
        }
        return $this;
    }

    /**
     * Add where group
     *
     * @param array $wheres
     * @param bool $or
     * @param bool $each_or
     * @return $this
     */
    final protected function where_group(array $wheres, $or = false, $each_or = false){
        $where_segments = [];
        foreach( $wheres as $where => $format ){
            $where_segments[] = [$this->and_or($each_or), $this->db->prepare($where, $format)];
        }
        $this->_wheres[] = [$this->and_or($or), $where_segments];
        return $this;
    }

    /**
     * Add where in with subquery
     *
     * @param string $column
     * @param string $sub_query
     * @param bool $or
     * @return $this
     */
    final protected function where_in_subquery($column, $sub_query, $or = false){
        $this->_wheres[] = [$this->and_or($or), "{$column} IN ({$sub_query})"];
        return $this;
    }

    /**
     * Add group by
     *
     * @param string $column
     * @param string $order
     * @return $this
     */
    final protected function group_by($column, $order = 'ASC'){
        $order = 'DESC' === strtoupper($order) ? 'DESC' : 'ASC';
        $this->_group_by[] = sprintf('%s %s', $this->escape_column_name($column), $order);
        return $this;
    }

    /**
     * Add order by
     *
     * @param string $column
     * @param string $order
     * @return $this
     */
    final protected function order_by($column, $order = 'ASC'){
        $order = 'DESC' === strtoupper($order) ? 'DESC' : 'ASC';
        $this->_order_by[] = sprintf('%s %s', $this->escape_column_name($column), $order);
        return $this;
    }

    /**
     * Set order random
     */
    final protected function random(){
        $this->_order_by = ['RAND()'];
        return $this;
    }

    /**
     * Where in
     *
     * @param $column
     * @param array $values
     * @param string $format %s as string, %d as integer, %f as float
     * @param bool $or
     * @return $this
     */
    final protected function where_in($column, array $values, $format = '%s', $or = false){
        $replace_values = [];
        foreach( $values as $val ){
            $replace_values[] = $this->db->prepare($format, $val);
        }
        $this->_wheres[] = [$this->and_or($or), sprintf('%s IN (%s)', $this->escape_column_name($column), implode(', ', $replace_values))];
        return $this;
    }

    /**
     * Set limit
     *
     * @param int $per_page
     * @param int $page
     * @return $this
     */
    final protected function limit($per_page, $page = 0){
        $this->_limit = [$per_page, $page * $per_page];
        return $this;
    }

    /**
     * Returns AND or OR
     *
     * @param bool $or
     * @return string
     */
    private function and_or($or){
        return $or ? 'OR' : 'AND';
    }

    /**
     * Wrap table name with backtick
     *
     * @param string $column_names
     * @return string
     */
    private function escape_column_name($column_names){
        $columns = [];
        foreach( explode(',', $column_names) as $column_name ){
            // trim
            $column_name = trim($column_name);
            // Remove special chars
            $columns[] = preg_replace('/[`\'";\n\r\\\0]/u', '', $column_name);
        }
        return implode(', ', $columns);
    }

    /**
     * Execute query
     *
     * @param string $query
     * @param bool $ignore_cache Default false. If true, always return fresh result.
     * @return false|int False on failure, Affected row count on success.
     */
    final protected function execute($query, $ignore_cache = false){
        if( $this->cache_exist($query) && !$ignore_cache ){
            return $this->get_cache($query);
        }else{
            $result = $this->db->query($query);
            $this->clear();
            $this->cache_query($query, $result);
            return $result;
        }
    }

    /**
     * Return cache
     *
     * @param string $query
     * @return null|mixed
     */
    protected function get_cache($query){
        return isset($this->query_cache[$query]) ? $this->query_cache[$query] : null;
    }

    /**
     * Detect if cache exits
     * @param $query
     * @return bool
     */
    protected function cache_exist($query){
        return isset($this->query_cache[$query]);
    }

    /**
     * Clear all result
     */
    final protected function clear(){
        $this->_distinct = false;
        $this->_select = [];
        $this->_from = '';
        $this->_join = [];
        $this->_wheres = [];
        $this->_group_by = [];
        $this->_order_by = [];
        $this->_limit = [];
    }

    /**
     * Save query cache
     *
     * @param string $sql
     * @param mixed $result
     */
    final protected function cache_query($sql, $result){
        if( isset($this->query_cache[$sql]) ){
            $this->query_cache[$sql] = $result;
        }elseif( count($this->query_cache) >= $this->cache_limit ){
            // Remove oldest cache
            array_shift($this->query_cache);
            // save query
            $this->query_cache[$sql] = $result;
        }
    }


    /**
     * Default select
     *
     * If select clause is not specified,
     * this function will fill.
     * Override this function to change default.
     *
     * @return string *(asterisk)
     */
    protected function default_select(){
        return '*';
    }


    /**
     * Default Join array
     *
     * If join is not specified, this will used.
     * Override this function to change default.
     *
     * <code>
     * return [
     *     [$this->some, "{$this->some}.ID = {$this->table}.user_id", 'inner'],
     *     [$this->other, "{$this->table}.other_id = {$this->other}.post_id", 'left'],
     * ];
     * </code>
     *
     * @return array Array of join array like ['talble', 'on table.ID = another.post_id', 'left']
     */
    protected function default_join(){
        return [];
    }

    /**
     * Getter
     *
     * @param string $name
     * @return \wpdb
     */
    public function __get($name){
        switch( $name ){
            case 'table':
                return $this->db->prefix.$this->name;
                break;
            case 'db':
                global $wpdb;
                return $wpdb;
                break;
            default:
                if( false !== array_search($name, $this->related) ){
                    return $this->db->prefix.$name;
                }
                break;
        }
    }
} 