<?php

namespace WPametu\DB;


use WPametu\Pattern\Singleton;

/**
 * Model class
 *
 * @package WPametu\DB
 * @property-read string $table
 */
abstract class Model extends QueryBuilder
{

    /**
     * Primary key for this model
     *
     * <code>$this_table.$this->primary_key</code> will used for
     * <code>$this->get()</code> method.
     *
     * @var string
     */
    protected $primary_key = '';

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
    public function found_count(){
        return (int)$this->db->get_var('SELECT FOUND_ROWS() AS count');
    }

    /**
     * Get object with primary key
     *
     * @param int $id
     * @param bool $ignore_cache
     * @return mixed|null
     */
    public function get($id, $ignore_cache = false){
        if( empty($this->primay_key) ){
            return null;
        }
        $row = $this->where("{$this->table}.{$this->primary_key} = %d", $id)->get_row('', $ignore_cache);
        if( $row && $this->result_class ){
            return new $this->result_class($row);
        }else{
            return $row;
        }
    }

    /**
     * @param array $wheres
     * @param int $limit
     * @param int $offset
     * @return array|mixed|null
     */
    public function find(array $wheres, $limit = 0, $offset = 0){
        if( !$limit ){
            $limit = $this->per_page;
        }
        return $this->calc()->wheres($wheres)->limit($limit, $offset * $limit)->result();
    }

    /**
     * Get var
     *
     * @param string $query
     * @return null|string
     */
    public function get_var($query = ''){
        if( !$query ){
            $query = $this->build_query();
        }
        $this->clear();
        return $this->db->get_var($query);
    }

    /**
     * Returns specified column as array
     *
     * @param int $x
     * @param string $query
     * @return array
     */
    public function get_col($x = 0, $query = ''){
        if( !$query ){
            $query = $this->build_query();
        }
        $this->clear();
        return $this->db->get_col($query, $x);
    }

    /**
     * Returns row object
     *
     * @param string $query
     * @param bool $ignore_cache
     * @return mixed|null
     */
    public function get_row($query = '', $ignore_cache = false){
        if( empty($query) ){
            $query = $this->build_query();
            $this->clear();
        }
        if( $this->cache_exist($query) && !$ignore_cache ){
            return $this->get_cache($query);
        }else{
            $row = $this->db->get_row($query);
            return $row;
        }
    }

    /**
     * Get results
     *
     * @param string $query
     * @param bool $ignore_cache
     * @return array|mixed|null
     */
    public function result($query = '', $ignore_cache = false){
        $result = $this->raw_result($query, $ignore_cache);
        if( $result && $this->has_result_class() ){
            $new_results = [];
            foreach($result as $key => $obj){
                $new_results[$key] = new $this->result_class($obj);
            }
            return $new_results;
        }else{
            return $result;
        }
    }

    /**
     * @param string $query
     * @param bool $ignore_cache
     * @return mixed|null
     */
    public function raw_result($query = '', $ignore_cache = false){
        if( empty($query) ){
            $query = $this->calc()->build_query();
        }
        $this->clear();
        if( $this->cache_exist($query) && !$ignore_cache ){
            return $this->get_cache($query);
        }else{
            return $this->db->get_results($query);
        }
    }

    /**
     * Detect if this class has result class
     *
     * @return bool
     */
    protected function has_result_class(){
        return !empty($this->result_class) && class_exists($this->result_class);
    }
}
