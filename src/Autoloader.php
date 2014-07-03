<?php

namespace WPametu;


use WPametu\Exception\FileLoadException;
use WPametu\Pattern\Singleton;
use WPametu\Traits\Reflection;


/**
 * Autoloader class for WPametu Framework
 *
 * @package WPametu
 */
class Autoloader extends Singleton
{
    use Reflection;

    /**
     * Constructor
     *
     * @param array $setting key 'config' is setting config file.
     * @throws FileLoadException
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
        $config = \Spyc::YAMLLoad($config_path);
        // Make instance of every Singleton class
        if( !empty($config) ){
            foreach($config as $class_name){
                if( $this->is_singleton($class_name) ){
                    $class_name::get_instance();
                }
            }
        }
    }
} 