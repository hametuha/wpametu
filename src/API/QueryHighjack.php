<?php

namespace WPametu\API;


/**
 * Highjack WP_Query
 *
 * @package WPametu\API
 * @property-read \wpdb $db
 */
abstract class QueryHighjack extends Controller
{

    /**
     * Query vars
     *
     * @var array
     */
    protected $query_var = [];

    /**
     * Rewrite rules
     *
     * @var array
     */
    protected $rewrites = [];

    /**
     * Constructor
     */
    protected function __construct(){
        add_filter('query_vars', [$this, 'query_vars']);
        add_filter('rewrite_rules_array', [$this, 'rewrite_rules_array']);
        add_filter('posts_distinct', [$this, 'posts_distinct'], 10, 2);
        add_action('pre_get_posts', [$this, 'pre_get_posts']);
        add_filter('posts_fields', [$this, 'posts_fields'], 10, 2);
        add_filter('posts_join', [$this, 'posts_join'], 10, 2);
        add_filter('posts_orderby', [$this, 'posts_orderby'], 10, 2);
        add_filter('posts_where', [$this, 'posts_where'], 10, 2);
        add_filter('posts_search', [$this, 'posts_search'], 10, 2);
        add_filter('posts_groupby', [$this, 'posts_groupby'], 10, 2);
        add_filter('posts_request', [$this, 'posts_request'], 10, 2);
    }

    /**
     * Add query var filter
     *
     * @param array $vars
     * @return array
     */
    public function query_vars( array $vars ){
        if( !empty($this->query_var) ){
            $vars = array_merge($vars, $this->query_var);
        }
        return $vars;
    }

    /**
     * Regsiter rewrite rules
     *
     * @param array $rules
     * @return array
     */
    public function rewrite_rules_array( array $rules ){
        if( !empty($this->rewrites) ){
            $rules = array_merge($this->rewrites, $rules);
        }
        return $rules;
    }

    /**
     * action for pre_get_posts
     *
     * @param \WP_Query $wp_query
     */
    public function pre_get_posts( \WP_Query &$wp_query ){
        // Do nothing.
    }

    /**
     * Override distinct
     *
     * @param string $distinct
     * @param \WP_Query $wp_query
     * @return mixed
     */
    public function posts_distinct($distinct, \WP_Query $wp_query){
        return $distinct;
    }

    /**
     * Filter select fields
     *
     * @param string $fields
     * @param \WP_Query $wp_query
     * @return string
     */
    public function posts_fields($fields, \WP_Query $wp_query){
        return $fields;
    }

    /**
     * Filter JOIN
     *
     * @param string $join
     * @param \WP_Query $wp_query
     * @return mixed
     */
    public function posts_join($join, \WP_Query $wp_query){
        return $join;
    }


    /**
     * Filter where clause
     *
     * @param string $where
     * @param \WP_Query $wp_query
     * @return string
     */
    public function posts_where($where, \WP_Query $wp_query){
        return $where;
    }

    /**
     * Filter posts search
     *
     * @param string $search
     * @param \WP_Query $wp_query
     * @return string
     */
    public function posts_search($search, \WP_Query $wp_query){
        return $search;
    }

    /**
     * Filter group by
     *
     * @param string $order_by
     * @param \WP_Query $wp_query
     * @return mixed
     */
    public function posts_orderby($order_by, \WP_Query $wp_query){
        return $order_by;
    }

    /**
     * Filter order by
     *
     * @param string $group_by
     * @param \WP_Query $wp_query
     * @return mixed
     */
    public function posts_groupby($group_by, \WP_Query $wp_query){
        return $group_by;
    }

    /**
     * Posts request
     *
     * @param string $request
     * @param \WP_Query $wp_query
     * @return mixed
     */
    public function posts_request($request, \WP_Query $wp_query){
        return $request;
    }

    /**
     * Detect if query var is valid
     *
     * @param \WP_Query $wp_query
     * @return bool
     */
    abstract protected function is_valid_query( \WP_Query $wp_query );

    /**
     * Getter
     *
     * @param string $name
     * @return mixed|\wpdb
     */
    public function __get($name){
        switch($name){
            case 'db':
                global $wpdb;
                return $wpdb;
                break;
            default:
                return parent::__get($name);
                break;
        }
    }
} 