<?php

namespace WPametuTest\Models;


use WPametu\DB\Model;

/**
 * Test DB query functions.
 */
class QueryTestModel extends Model {

	/**
	 * Get total value.
	 *
	 * @return int
	 */
	public function get_total() {
		$int = (int) $this->select( 'COUNT(*)' )
			->from( $this->db->posts )
			->group_by( 'post_author' )
			->order_by( 'post_author', 'DESC' )
			->get_var();
		error_log( $this->db->last_query );
		return $int;
	}

}
