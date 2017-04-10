<?php

namespace WPametu\Utility;

/**
 * Short hand class for WP_CLI command
 *
 * @package WPametu
 */
abstract class Command extends \WP_CLI_Command
{

	/**
	 * Override this constant
	 */
	const COMMAND_NAME = '';

	/**
	 * Print success
	 *
	 * @param string $string
	 */
	protected static function s($string){
		\WP_CLI::success($string);
	}

	/**
	 * Print line
	 *
	 * @param string $string
	 */
	protected static function l($string){
		\WP_CLI::line($string);
	}

	/**
	 * Print string
	 *
	 * @param string $string
	 */
	protected static function o($string){
		\WP_CLI::out($string);
	}

	/**
	 * Show error and stop processing
	 *
	 * @param string $string
	 */
	protected static function e($string){
		\WP_CLI::error( $string );
	}

	/**
	 * Show warning
	 *
	 * @param string $string
	 */
	protected static function w($string){
		\WP_CLI::warning( $string );
	}

	/**
	 * Show table
	 *
	 * @param array $header
	 * @param array $body
	 */
	protected static function table($header, $body){
		$table = new \cli\Table();
		$table->setHeaders( $header );
		$table->setRows( $body );
		$table->display();
	}

	/**
	 * Get assoc args shorthand
	 *
	 * @param string $key
	 * @param array $assoc_args
	 *
	 * @return string|null
	 */
	protected static function get_flag($key, $assoc_args){
		return isset($assoc_args[$key]) ? $assoc_args[$key] : null;
	}
}
