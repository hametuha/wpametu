<?php

namespace WPametu\API;


/**
 * Highjack WP_Query
 *
 * @package WPametu
 * @property-read \wpdb $db
 */
abstract class QueryHighJack extends Controller {


	/**
	 * Query vars
	 *
	 * @var array
	 */
	protected $query_var = array();

	/**
	 * Rewrite rules
	 *
	 * @var array
	 */
	protected $rewrites = array();

	/**
	 * Constructor
	 */
	protected function __construct() {
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_filter( 'rewrite_rules_array', array( $this, 'rewrite_rules_array' ) );
		add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'detect_title' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_filter( 'posts_fields', array( $this, 'posts_fields' ), 10, 2 );
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 2 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
		add_filter( 'posts_search', array( $this, 'posts_search' ), 10, 2 );
		add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 10, 2 );
		add_filter( 'posts_request', array( $this, 'posts_request' ), 10, 2 );
		add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );
	}

	/**
	 * Add query var filter
	 *
	 * @param array $vars
	 * @return array
	 */
	public function query_vars( array $vars ) {
		if ( ! empty( $this->query_var ) ) {
			foreach ( $this->query_var as $var ) {
				if ( false === array_search( $var, $vars, true ) ) {
					$vars[] = $var;
				}
			}
		}
		return $vars;
	}

	/**
	 * Register rewrite rules
	 *
	 * @param array $rules
	 * @return array
	 */
	public function rewrite_rules_array( array $rules ) {
		if ( ! empty( $this->rewrites ) ) {
			$rules = array_merge( $this->rewrites, $rules );
		}
		return $rules;
	}

	/**
	 * Add wp_title filter if required
	 *
	 * @param \WP_Query $wp_query
	 */
	final public function detect_title( \WP_Query &$wp_query ) {
		if ( $wp_query->is_main_query() && $this->is_valid_query( $wp_query ) ) {
			add_filter( 'wp_title', array( $this, 'wp_title' ), 10, 3 );
		}
	}


	/**
	 * Override this method if you want to change title
	 *
	 * @param string $title
	 * @param string $sep
	 * @param string $sep_location
	 *
	 * @return string
	 */
	public function wp_title( $title, $sep, $sep_location ) {
		return $title;
	}

	/**
	 * action for pre_get_posts
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		// Do nothing.
	}

	/**
	 * Override distinct
	 *
	 * @param string $distinct
	 * @param \WP_Query $wp_query
	 * @return mixed
	 */
	public function posts_distinct( $distinct, \WP_Query $wp_query ) {
		return $distinct;
	}

	/**
	 * Filter select fields
	 *
	 * @param string $fields
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function posts_fields( $fields, \WP_Query $wp_query ) {
		return $fields;
	}

	/**
	 * Filter JOIN
	 *
	 * @param string $join
	 * @param \WP_Query $wp_query
	 * @return mixed
	 */
	public function posts_join( $join, \WP_Query $wp_query ) {
		return $join;
	}


	/**
	 * Filter where clause
	 *
	 * @param string $where
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function posts_where( $where, \WP_Query $wp_query ) {
		return $where;
	}

	/**
	 * Filter posts search
	 *
	 * @param string $search
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function posts_search( $search, \WP_Query $wp_query ) {
		return $search;
	}

	/**
	 * Filter group by
	 *
	 * @param string $order_by
	 * @param \WP_Query $wp_query
	 * @return mixed
	 */
	public function posts_orderby( $order_by, \WP_Query $wp_query ) {
		return $order_by;
	}

	/**
	 * Filter order by
	 *
	 * @param string $group_by
	 * @param \WP_Query $wp_query
	 * @return mixed
	 */
	public function posts_groupby( $group_by, \WP_Query $wp_query ) {
		return $group_by;
	}

	/**
	 * Posts request
	 *
	 * @param string $request
	 * @param \WP_Query $wp_query
	 * @return mixed
	 */
	public function posts_request( $request, \WP_Query $wp_query ) {
		return $request;
	}

	/**
	 * Posts results
	 *
	 * @param array $posts
	 * @param \WP_Query $wp_query
	 * @return array
	 */
	public function the_posts( array $posts, \WP_Query $wp_query ) {
		return $posts;
	}

	/**
	 * Detect if query var is valid
	 *
	 * @param \WP_Query $wp_query
	 * @return bool
	 */
	abstract protected function is_valid_query( \WP_Query $wp_query );

	/**
	 * Add meta query
	 *
	 * @param \WP_Query $wp_query
	 * @param array $meta_query
	 */
	protected function add_meta_query( \WP_Query &$wp_query, array $meta_query ) {
		$old_query = (array) $wp_query->get( 'meta_query' );
		array_push( $old_query, $meta_query );
		$wp_query->set( 'meta_query', $old_query );
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed|\wpdb
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'db':
				global $wpdb;
				return $wpdb;
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
