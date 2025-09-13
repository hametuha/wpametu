<?php
/**
 * Class SampleTest
 *
 * @package wpametu
 */



/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

	function test_orderby() {
		$instance = \WPametuTest\Models\QueryTestModel::get_instance();
		$total    = $instance->get_total();
		$this->assertTrue( is_int( $total ) );
	}
}

