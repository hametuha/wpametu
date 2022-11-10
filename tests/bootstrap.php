<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Never_Let_Me_Go
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

use Yoast\WPTestUtils\WPIntegration;

$plugin_main_dir = dirname(__DIR__);
$plugin_main_file = "$plugin_main_dir/example-plugin.php";

// Define plugin dir.
if ( getenv( 'WP_PLUGIN_DIR' ) !== false ) {
	define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

require_once "$plugin_main_dir/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php";

$_tests_dir = $_tests_dir = WPIntegration\get_path_to_wp_test_dir();


// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/wpametu.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
WPIntegration\bootstrap_it();
