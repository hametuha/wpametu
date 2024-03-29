<?php

namespace WPametu\File;

/**
 * Path helper
 *
 * @package WPametu
 */
trait Path {

	/**
	 * Is child theme
	 *
	 * @return bool
	 */
	protected function is_child_theme() {
		return wp_get_theme()->parent();
	}

	/**
	 * Get frameworks root dir
	 *
	 * @return string
	 */
	protected function get_root_dir() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

	/**
	 * Returns vendor's directory
	 *
	 * @return string
	 */
	protected function get_vendor_dir() {
		return $this->get_root_dir() . '/vendor';
	}

	/**
	 * Returns this theme's root directory
	 *
	 * @return string
	 */
	protected function get_theme_dir() {
		if ( $this->is_child_theme() ) {
			return get_stylesheet_directory();
		} else {
			return get_template_directory();
		}
	}

	/**
	 * Returns this theme's root uri
	 *
	 * @return string
	 */
	protected function get_theme_uri() {
		if ( $this->is_child_theme() ) {
			return get_stylesheet_directory_uri();
		} else {
			return get_template_directory_uri();
		}
	}

	/**
	 * Returns frameworks root uri
	 *
	 * @return string
	 */
	protected function get_root_uri() {
		return untrailingslashit( str_replace( untrailingslashit( ABSPATH ), rtrim( home_url(), '/' ), $this->get_root_dir() ) );
	}

	/**
	 * Get vendor directory
	 *
	 * @return string
	 */
	protected function get_vendor_uri() {
		return $this->get_root_uri() . '/vendor';
	}


	/**
	 * Remove directory recursively
	 *
	 * @param string $path
	 */
	private function remove_dir( $path ) {
		if ( is_dir( $path ) ) {
			foreach ( scandir( $path ) as $file ) {
				if ( ! in_array( $file, [ '.', '..' ], true ) ) {
					if ( is_dir( "{$path}/{$file}" ) ) {
						$this->remove_dir( "{$path}/{$file}" );
					} else {
						@unlink( "{$path}/{$file}" );
					}
				}
			}
			@rmdir( $path );
		}
	}

	/**
	 * Get configuration directory
	 *
	 * @return string
	 */
	protected function get_config_dir() {
		/**
		 * wpametu_config_dir_name
		 *
		 * Configuration directory's name.
		 *
		 * @filter
		 *
		 * @param string $dir Default 'config'.
		 *
		 * @return string
		 */
		$basename = apply_filters( 'wpametu_config_dir_name', 'config' );

		return $this->get_theme_dir() . '/' . $basename;
	}

}
