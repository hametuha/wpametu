<?php

namespace WPametu;


use WPametu\API\Ajax\AjaxBase;
use WPametu\API\Ajax\AjaxPostSearch;
use WPametu\API\QueryHighJack;
use WPametu\API\Rest\RestBase;
use WPametu\Exception\FileLoadException;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\Reflection;
use WPametu\Assets\Library;
use WPametu\DB\TableBuilder;
use WPametu\API\Rewrite;
use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Widget;
use WPametu\Utility\PostHelper;
use WPametu\Utility\StringHelper;


/**
 * AutoLoader class for WPametu Framework
 *
 * @package WPametu
 * @property-read array $class_names
 * @property-read string $namespace
 * @property-read string $namespace_root
 * @property-read StringHelper $str
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
    private $ajax_controllers = [
        AjaxPostSearch::class,
    ];

    /**
     * Widgets
     *
     * @var array
     */
    private $widgets = [];

    /**
     * Post type to override
     *
     * @var array
     */
    private $post_type_to_override = [];

    /**
     * Constructor
     *
     * @param array $setting
     * @throws FileLoadException
     * @throws \Exception
     */
    protected function __construct( array $setting = [] ){

        if( $this->has_theme_namespace() ){

            // Register auto loader for theme
            spl_autoload_register(function($class_name){
                $class_name = ltrim($class_name, '\\');
                if( 0 === strpos($class_name, $this->namespace.'\\') ){
                    $path = str_replace('\\', '/', str_replace($this->namespace.'\\', $this->namespace_root.'\\', $class_name)).'.php';
                    if( file_exists($path) ){
                        require $path;
                    }
                }
            });

            // Register Meta boxes if exists
            add_action('admin_menu', [$this, 'register_meta_box']);

            // Register Posthelper
            add_action('init', [$this, 'scan_post_type']);

        }

        // Make instance of every Singleton class
        foreach($this->class_names as $class_name){
            if( $this->is_singleton($class_name) ){
                $class_name::get_instance();
            }
        }

        // Register auto loader
        foreach([
            'Ajax' => AjaxBase::class,
            'QueryHighJack' => QueryHighJack::class,
            'Rest' => RestBase::class,
            'Widget' => Widget::class,
                ] as $base => $sub_class){
            $base_dir = $this->namespace_root.'/'.$base;
            if( !is_dir($base_dir) ){
                continue;
            }
            foreach( scandir($base_dir) as $file ){
                if( !preg_match('/\.php$/u', $file) ){
                    continue;
                }
                $class_name = $this->namespace.'\\'.$base.'\\'.preg_replace('/\.php$/u', '', basename($file));
                if( !class_exists($class_name) ){
                    throw new \Exception(sprintf('Class %s doesn\'t exist.', $class_name));
                }
                if( !$this->is_sub_class_of($class_name, $sub_class) ){
                    throw new \Exception(sprintf('Ajax class %s must be sub class of %s.', $class_name, $sub_class));
                }
                if( $this->is_sub_class_of($class_name, Singleton::class) ){
                    $class_name::get_instance();
                    switch( $sub_class ){
                        case AjaxBase::class:
                            $this->ajax_controllers[] = $class_name;
                            break;
                        case RestBase::class:
                            Rewrite::register_class($class_name);
                            break;
                    }
                }else{
                    switch( $sub_class ){
                        case Widget::class:
                            $this->widgets[] = $class_name;
                            break;
                    }
                }
            }
        }

        // Register Ajax actions
        if( !empty($this->ajax_controllers) ){
            add_action('admin_init', [$this, 'ajax_register']);
        }

        // Register Widgets
        if( !empty($this->widgets) ){
            add_action('widgets_init', [$this, 'register_widgets']);
        }

    }

    /**
     * Register meta boxes
     */
    public function register_meta_box(){
        if( is_dir($this->namespace_root.'/Metaboxes') ){
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
     * Register widgets
     */
    public function register_widgets(){
        foreach( $this->widgets as $widget ){
            register_widget($widget);
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
     * Scan original post type to override
     */
    public function scan_post_type(){
        $dir = $this->namespace_root.'/ThePost';
        if( is_dir($dir) ){
            $flg = false;
            foreach( scandir($dir) as $file ){
                if( !preg_match('/^\./u', $file) ){
                    $base_class = str_replace('.php', '', $file);
                    $class_name = $this->namespace.'\\ThePost\\'.$base_class;
                    if( class_exists($class_name) && $this->is_sub_class_of($class_name, PostHelper::class) ){
                        $this->post_type_to_override[$this->str->camel_to_hyphen($base_class)] = $class_name;
                        $flg = true;
                    }
                }
            }
            if( $flg ){
                add_action('the_post', [$this, 'the_post']);
            }
        }
    }

    /**
     * Assign global $post object
     *
     * @param \WP_Post $post_obj
     */
    public function the_post( \WP_Post &$post_obj ){
        if( isset($this->post_type_to_override[$post_obj->post_type]) ){
            // Post type exists. Let's override
            $class_name = $this->post_type_to_override[$post_obj->post_type];
            global $post;
            $post->helper = new $class_name($post_obj);
        }
    }

    /**
     * Load assets to admin screen
     *
     * @param $page_name
     */
    public function admin_enqueue_scripts( $page_name ){
        $screen = get_current_screen();
        if( 'post' == $screen->base ){

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
            case 'namespace':
                return $this->has_theme_namespace() ? \WPAMETU_NAMESPACE_ROOT : false;
                break;
            case 'namespace_root':
                return $this->has_theme_namespace() ? \WPAMETU_NAMESPACE_ROOT_DIR : false;
                break;
            case 'str':
                return StringHelper::get_instance();
                break;
            default:
                // Do nothing
                break;
        }
    }
}