<?php

namespace WPametu\Utility;
use WPametu\Traits\i18n;
use WPametu\Assets\Library;

/***
 * Utility commands for WPametu
 *
 * ## EXAMPLES
 *
 *     # Show all libraries for WPametu
 *     wp ametu assets css
 *
 * @package WPametu\Utility
 */
class WPametuCommand extends Command
{

	use i18n;

	const COMMAND_NAME = 'ametu';

	/**
	 * Show all asset libraries of WPametu
	 *
	 * ## OPTIONS
	 *
	 * <type>
	 * : Assets type. 'css', 'js' are supported.
	 *
	 * [--global]
	 * : Show global assets registered without WPametu
	 *
	 * ## EXAMPLES
	 *
	 *     wp ametu assets css
	 *
	 * @synopsis <type> [--global]
	 */
	public function assets( $args, $assoc_args ){
		list( $type ) = $args;
		$global       = self::get_flag('global', $assoc_args);
		if( false === array_search($type, ['css', 'js']) ){
			self::e($this->__('Only css and js are supported.'));
		}
		$header = [
			'Handle',
			'Source',
			'Dependency',
			'Version',
			$type == 'css' ? 'Media' : 'Footer'
         ];
		$rows = [];
		if( $global ){
			switch( $type ){
				case 'js':

					break;
				case 'css':

					break;
				default:
					// Do nothing
					break;
			}
		}else{
			$assets = Library::all_assets()[$type];
			foreach( $assets as $handle => $asset ){
				$rows[] = array_merge([$handle], array_map(function($var){
					if( is_bool($var) ){
						return $var ? 'Yes' : 'No' ;
					}elseif( is_array($var) ){
						return empty($var) ? '-' : implode(', ', $var);
					}elseif( is_null($var) ){
						return '-';
					}else{
						return $var;
					}
				}, $asset));
			}
		}

		self::table($header, $rows);
		self::l('');
		self::s(sprintf('%d %s are available on WPametu.', count($rows), $type));
	}

	/**
	 * Check if akismet is available
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     wp ametu akismet
	 *
	 * @synopsis
	 */
	public function akismet(){
		try{
			if( !class_exists( 'Akismet' ) ){
				throw new \Exception('Akismet is not installed.');
			}
			if( ! ( $key = \Akismet::get_api_key() ) ){
				throw new \Exception('Akismet API key is not set.');
			}
			if( 'valid' !== \Akismet::verify_key( $key ) ){
				throw new \Exception( 'Akismet API key is invalid.' );
			}
			static::s( 'Akismet is available!' );
		}catch ( \Exception $e ){
			static::e( $e->getMessage() );
		}
	}
}
