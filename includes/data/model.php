<?php
/**
 * Created by PhpStorm.
 * User: guy
 * Date: 2013/11/25
 * Time: 20:49
 */

namespace WPametu\Data;


/**
 * Class Model
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
class Model
{

	public function __construct(){
		return $this->post;
	}

	public function __get($name){
		switch($name){
			case 'db':
			case 'wpdb':
				global $wpdb;
				if($wpdb){
					return $wpdb;
				}
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
		}
	}
} 