<?php
/*
Plugin Name: WPametu
Plugin URI: https://github.com/hametuha/wpametu
Description: A WordPress Theme framework.
Author: hametuha
Version: nightly
Author URI: https://hametuha.co.jp/
*/

defined( 'ABSPATH' ) || die();

if ( file_exists( __DIR__.'/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
	WPametu::entry( 'WPametuTest', __DIR__ . '/tests/src' );
} else {
	trigger_error( 'WPametu: autoload.php doesn\'t exist. If this is from github, run composer install.' );
}
