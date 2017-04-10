<?php

namespace WPametu\File;


use WPametu\Pattern\Singleton;

/**
 * Image controller
 *
 * @package WPametu
 * @property-read Mime $mime
 */
class Image extends Singleton
{

	/**
	 * Include Media libraries
	 */
	public function include_wp_libs(){
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}

    /**
     * Clone of image resize
     *
     * @see image_resize
     * @param string $file Image file path.
     * @param int $max_w Maximum width to resize to.
     * @param int $max_h Maximum height to resize to.
     * @param bool $crop Optional. Whether to crop image or resize.
     * @param string $suffix Optional. File suffix.
     * @param string $dest_path Optional. New image file path.
     * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
     * @return mixed WP_Error on failure. String with new destination path.
     */
    public function trim( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ){
        $editor = wp_get_image_editor( $file );
        if ( is_wp_error( $editor ) )
            return $editor;
        $editor->set_quality( $jpeg_quality );

        $resized = $editor->resize( $max_w, $max_h, $crop );
        if ( is_wp_error( $resized ) )
            return $resized;

        $dest_file = $editor->generate_filename( $suffix, $dest_path );
        $saved = $editor->save( $dest_file );

        if ( is_wp_error( $saved ) )
            return $saved;

        return $dest_file;
    }

    /**
     * Fit small image to specified bound
     *
     * @param string $src
     * @param string $dest
     * @param int $width
     * @param int $height
     * @return bool
     */
    public function fit($src, $dest, $width, $height){
        // Calculate
        $size = getimagesize($src);
        $ratio = max($width / $size[0], $height / $size[1]);
        $old_width = $size[0];
        $old_height = $size[1];
        $new_width = intval($old_width * $ratio);
        $new_height = intval($old_height * $ratio);
        // Resize
        @ini_set( 'memory_limit', apply_filters( 'image_memory_limit', WP_MAX_MEMORY_LIMIT ) );
        $image = imagecreatefromstring( file_get_contents( $src ) );
        $new_image = wp_imagecreatetruecolor( $new_width, $new_height );
        imagecopyresampled( $new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);
        if ( IMAGETYPE_PNG == $size[2] && function_exists('imageistruecolor') && !imageistruecolor( $image ) ){
            imagetruecolortopalette( $new_image, false, imagecolorstotal( $image ) );
        }
        // Destroy old image
        imagedestroy( $image );
        // Save
        switch($size[2]){
            case IMAGETYPE_GIF:
                $result = imagegif($new_image, $dest);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($new_image, $dest);
                break;
            default:
                $result = imagejpeg($new_image, $dest);
                break;
        }
        imagedestroy($new_image);
        return $result;
    }

    /**
     * Return image width and height
     *
     * @param string $path
     * @return array
     */
    public function get_image_size($path){
        if( !file_exists($path) || !($info = getimagesize($path)) ){
            return [0, 0];
        }
        return [$info[0], $info[1]];
    }

	/**
	 * Replace IMG tag's src
	 *
	 * @param string $img_tag
	 * @param string $new_src
	 * @return string
	 */
	public function replace_url($img_tag, $new_src){
		return preg_replace('/src=[\'"][^\'"]+[\'"]/u', 'src="'.$new_src.'"', $img_tag);
	}


    /**
     * Getter
     *
     * @param string $name
     * @return null|Singleton
     */
    public function __get($name){
        switch($name){
            case 'mime':
                return Mime::get_instance();
                break;
            default:
                return null;
                break;
        }
    }
} 