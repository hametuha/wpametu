<?php

namespace WPametu;


use WPametu\Exception\FileLoadException;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\Reflection;


/**
 * Autoloader class for WPametu Framework
 *
 * @package WPametu
 */
class Autoloader extends Singleton
{
    use Reflection, Path;

    /**
     * Constructor
     *
     * @param array $setting key 'config' is setting config file.
     * @throws FileLoadException
     * @throws \Exception
     */
    protected function __construct( array $setting = [] ){
        // Check if file exists.
        if( !isset($setting['config']) || !file_exists($setting['config']) ){
            throw new FileLoadException($setting['config']);
        }
        /**
         * wpametu_config_path
         *
         * Filter config path to framework setting file.
         * You can override this setting.
         *
         * @param string $path
         * @return string
         */
        $config_path = apply_filters('wpametu_config_path', $setting['config']);
        // Load config file.
        require $setting['config'];
        // Make instance of every Singleton class
        if( isset($autoloads) && !empty($autoloads) ){
            foreach($autoloads as $class_name){
                if( $this->is_singleton($class_name) ){
                    $class_name::get_instance();
                }
            }
        }

        // Seek API
        $path = $this->get_theme_dir().'/config/api.php';
        if( file_exists($path) ){
            require $path;
            if( !isset($apis) || !is_array($apis) ){
                throw new \Exception(sprintf('You must define $apis as class name array in %s.', $path));
            }
            foreach( $apis as $class_name ){
                if( $this->is_controller($class_name) ){
                    $class_name::get_instance();
                }
            }
        }
    }
} 