<?php

namespace WPametu;


use WPametu\API\Ajax\AjaxBase;
use WPametu\API\QueryHighjack;
use WPametu\Exception\FileLoadException;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\Reflection;
use WPametu\Assets\Library;
use WPametu\DB\TableBuilder;
use WPametu\API\Rewrite;
use WPametu\UI\Admin\EditMetaBox;


/**
 * Autoloader class for WPametu Framework
 *
 * @package WPametu
 * @property-read array $highjack
 * @property-read array $class_names
 * @property-read string $namespace
 * @property-read string $namespace_root
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

        // Register auto loader for theme
        if( $this->has_theme_namespace() ){
            spl_autoload_register(function($class_name){
                $class_name = ltrim($class_name, '\\');
                if( 0 === strpos($class_name, $this->namespace.'\\') ){
                    $path = str_replace('\\', '/', str_replace($this->namespace.'\\', $this->namespace_root.'\\', $class_name)).'.php';
                    if( file_exists($path) ){
                        require $path;
                    }
                }
            });
        }

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

        // Seek Query Highjacker
        foreach( $this->highjack as $class_name ){
            if( class_exists($class_name) && $this->is_sub_class_of($class_name, QueryHighjack::class) ){
                $class_name::get_instance();
            }
        }

        // metbox
        add_action('admin_menu', [$this, 'register_meta_box']);
    }

    /**
     * Register meta boxes
     */
    public function register_meta_box(){
        if( $this->has_theme_namespace() && is_dir($this->namespace_root.'/Metaboxes') ){
            // Enqueue script flag
            $flg = false;
            // Load all meta boxes
            foreach( scandir($this->namespace_root.'/Metaboxes') as $file ){
                if( !preg_match('/^\./u', $file) ){
                    $class_name = $this->namespace.'\\Metaboxes\\'.str_replace('.php', '', $file);
                    if( class_exists($class_name) && $this->is_sub_class_of($class_name, EditMetaBox::class) ){
                        $class_name::get_instance();
                        $flg = true;
                    }
                }
            }
            if( $flg ){
                add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
            }
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
     * Load assets to admin screen
     *
     * @param $page_name
     */
    public function admin_enqueue_scripts( $page_name ){
        $screen = get_current_screen();
        // If this is edit screen, load metabox helper
        if( 'post' == $screen->base ){
            wp_enqueue_script('wpametu-metabox');
            wp_enqueue_style('wpametu-metabox');
        }
    }

    /**
     * Detect if name space exists
     *
     * @return bool
     */
    protected function has_theme_namespace(){
        return defined('WPAMETU_NAMESPACE_ROOT') && defined('WPAMETU_NAMESPACE_ROOT_DIR') && is_dir(\WPAMETU_NAMESPACE_ROOT_DIR);
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
            case 'highjack':
                $config = $this->get_config_dir().'/highjack.php';
                if( file_exists($config) ){
                    include $config;
                    if( isset($highjack) && is_array($highjack) ){
                        return $highjack;
                    }else{
                        return [];
                    }
                }
                return [];
                break;
            case 'namespace':
                return $this->has_theme_namespace() ? \WPAMETU_NAMESPACE_ROOT : false;
                break;
            case 'namespace_root':
                return $this->has_theme_namespace() ? \WPAMETU_NAMESPACE_ROOT_DIR : false;
                break;
            default:
                // Do nothing
                break;
        }
    }
}