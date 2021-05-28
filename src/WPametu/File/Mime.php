<?php

namespace WPametu\File;


use WPametu\Pattern\Singleton;

/**
 * Mime type controller
 *
 * @package WPametu
 */
class Mime extends Singleton {


	/**
	 * Detect if specified file is image
	 *
	 * @param string $path
	 * @return bool
	 */
	public function is_image( $path ) {
		if ( ! file_exists( $path ) ) {
			return false;
		}
		$info = getimagesize( $path );
		if ( ! $info ) {
			return false;
		}
		return (bool) preg_match( '/^image\/(jpeg|png|gif)$/', $info['mime'] );
	}

	/**
	 * Return mime type
	 *
	 * @param string $file
	 * @return string
	 */
	public function get_mime( $file ) {
		$type = $this->get_content_type( $file );
		if ( $type ) {
			$type = array_map( 'trim', explode( ';', $type ) );
			return $type[0];
		} else {
			return '';
		}
	}

	/**
	 * Get content type
	 *
	 * @param string $path
	 * @return string
	 */
	public function get_content_type( $path ) {
		if ( file_exists( $path ) ) {
			$info = finfo_open( FILEINFO_MIME );
			$type = finfo_file( $info, $path );
			finfo_close( $info );
			return $type ?: '';
		} else {
			return '';
		}
	}

	/**
	 * Get extension
	 *
	 * @param string $path
	 * @return string
	 */
	public function get_extension( $path ) {
		$type  = $this->get_mime( $path );
		$match = array();
		$ext   = '';
		if ( $type ) {
			foreach ( wp_get_mime_types() as $extensions => $content_type ) {
				if ( $content_type === $type ) {
					$ext = explode( '|', $extensions )[0];
					break;
				}
			}
		}
		return $ext;
	}
}
