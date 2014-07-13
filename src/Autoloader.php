<?php

namespace WPametu;


use WPametu\API\Ajax\AjaxBase;
use WPametu\Exception\FileLoadException;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\Reflection;
use WPametu\Assets\Library;
use WPametu\DB\TableBuilder;
use WPametu\API\Rewrite;


/**
 * Autoloader class for WPametu Framework
 *
 * @package WPametu
 * @property-read array $class_names
 */
class AutoLoader extends Singleton
{
    use Reflection, Path;

    /**
     * Auto loaded class names
     *
     * @var array
     */
    private $default_classes = [
        Library::class,
        TableBuilder::class,
        Rewrite::class,
    ];

    /**
     * Ajax Controller classes
     *
     * @var array
     */
    private $ajax_controllers = [];

    /**
     * Constructor
     *
     * @param array $setting
     * @throws FileLoadException
     * @throws \Exception
     */
    protected function __construct( array $setting = [] ){
        // Make instance of every Singleton class
        foreach($this->class_names as $class_name){
            if( $this->is_singleton($class_name) ){
                $class_name::get_instance();
            }
        }
        // Seek API
        $path = $this->get_config_dir().'/api.php';
        if( file_exists($path) ){
            require $path;
            if( !isset($apis) || !is_array($apis) ){
                throw new \Exception(sprintf('You must define $apis as class name array in %s.', $path));
            }
            foreach( $apis as $class_name ){
                if( $this->is_controller($class_name) ){
                    $class_name::get_instance();
                    if( $this->is_sub_class_of($class_name, AjaxBase::class) ){
                        $this->ajax_controllers[] = $class_name;
                    }
                }
            }
        }
        // Register Ajax actions
        if( !empty($this->ajax_controllers) ){
            add_action('admin_init', [$this, 'ajax_register']);
        }
    }

    /**
     * Register ajax actions
     */
    public function ajax_register(){
        if( AjaxBase::is_ajax() ){
            foreach( $this->ajax_controllers as $class_name ){
                if( $this->is_sub_class_of($class_name, AjaxBase::class) ){
                    /** @var AjaxBase $instance */
                    $instance = $class_name::get_instance();
                    $instance->register();
                }
            }
        }
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed|void
     */
    public function __get($name){
        switch( $name ){
            case 'class_names':
                /**
                 * wpametu_autoloaded_classes
                 *
                 * Default class names after frameworks' bootstrap.
                 *
                 * @param array $classes
                 * @return array
                 */
                return apply_filters('wpametu_autoloaded_classes', $this->default_classes);
                break;
            default:
                // Do nothing
                break;
        }
    }
}