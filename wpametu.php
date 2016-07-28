<?php
/*
Plugin Name: WPametu
Plugin URI: https://github.com/hametuha/wpametu
Description: A WordPress Theme framework
Author: Takahashi_Fumiki
Version: 0.4
Author URI: https://hametuha.co.jp/
*/
if ( file_exists( __DIR__.'/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
	WPametu::entry();
} else {
	trigger_error( 'WPametu: autoload.php doesn\'t exist. If this is from github, run composer install.' );
}
