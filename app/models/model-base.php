<?php

namespace WPametu\Models;

use WPametu\Pattern, WPametu\Traits;

/**
 * Class Model
 *
 * @package WPametu\Data
 * @property-read \wpdb $db
 * @property-read string $post Table name
 * @property-read string $postmeta Table name
 * @property-read string $users Table name
 * @property-read string $usermeta Table name
 * @property-read string $terms Table name
 * @property-read string $term_taxonomy Table name
 * @property-read string $term_relationships Table name
 * @property-read string $options Table name
 * @property-read string $links Table name
 */
abstract class ModelBase extends Pattern\Singleton
{


    use Traits\i18n, Traits\Util {
        __get as traitGet;
    }

    /**
     * Constructor
     *
     * @param array $arguments
     */
    final protected  function __construct( array $arguments){
        $this->initialized();
	}

    /**
     * Executed at the end of constructor
     *
     * @return void
     */
    abstract protected function initialized();

    /**
     * Get results with prepared statement
     *
     * @param $query
     * @param $data
     * @return mixed
     */
    public function getResults($query, $data){
        return $this->db->get_results(call_user_func_array([$this->db, 'prepare'], func_get_args()));
    }

    /**
     * Get var with prepared statement
     *
     * @param string $query
     * @param mixed $data
     * @return false|string
     */
    public function getVar($query, $data){
        return $this->db->get_var(call_user_func_array([$this->db, 'prepare'], func_get_args()));
    }

    /**
     * @param string $query
     * @param mixed $data
     * @return false|Object
     */
    public function getRow($query, $data){
        return $this->db->get_row(call_user_func_array([$this->db, 'prepare'], func_get_args()));
    }

    /**
     * Execute query
     *
     * @param $query
     * @param $data
     * @return false|int
     */
    public function query($query, $data){
        return $this->db->query(call_user_func_array([$this->db, 'prepare'], func_get_args()));
    }

    /**
     * Insert data with timestamp
     *
     * @param string $table
     * @param array $data
     * @param array $map
     * @return false|int
     */
    public function insertWithTime($table, array $data, array $map){
        $data['updated'] = current_time('mysql');
        $data['created'] = current_time('mysql');
        $map = array_merge($map, ['%s', '%s']);
        return $this->db->insert($table, $data, $map);
    }

    /**
     * Update column timestamp
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @param array $data_map
     * @param array $where_map
     * @return false|int Affected rows count
     */
    public function updateWithTime($table, array $data, array $where, array $data_map, array $where_map){
        $data['updated'] = current_time('mysql');
        $data_map[] = '%s';
        return $this->db->update($table, $data, $where, $data_map, $where_map);
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
		switch($name){
			case 'db':
			case 'wpdb':
				global $wpdb;
				return $wpdb;
				break;
			case 'posts':
			case 'users':
			case 'usermeta':
			case 'postmeta':
			case 'terms':
			case 'term_taxonomy':
			case 'term_relationships':
			case 'options':
			case 'links':
				return $this->db->{$name};
				break;
            case 'per_page':
                return get_option('posts_per_page');
                break;
            default:
                return traitGet($name);
                break;
		}
	}
} 