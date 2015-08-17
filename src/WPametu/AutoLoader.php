<?php

namespace WPametu;


use WPametu\API\Ajax\AjaxBase;
use WPametu\API\Ajax\AjaxPostSearch;
use WPametu\API\QueryHighJack;
use WPametu\API\Rest\RestBase;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Tool\BatchProcessor;
use WPametu\Traits\Reflection;
use WPametu\Assets\Library;
use WPametu\DB\TableBuilder;
use WPametu\API\Rewrite;
use WPametu\UI\Admin\EditMetaBox;
use WPametu\UI\Admin\EmptyMetaBox;
use WPametu\UI\Admin\Screen;
use WPametu\UI\MetaBox;
use WPametu\UI\Widget;
use WPametu\Utility\PostHelper;
use WPametu\Utility\StringHelper;
use WPametu\Utility\WPametuCommand;
use WPametu\Utility\Command;

/**
 * AutoLoader class for WPametu Framework
 *
 * @package WPametu
 * @property-read StringHelper $str
 */
class AutoLoader extends Singleton
{
    use Reflection, Path;

	/**
	 * Namespace to scan
	 *
	 * @var array
	 */
	protected $namespaces = [];

    /**
     * Auto loaded class names
     *
     * @var array
     */
    private $default_classes = [
        Library::class,
        TableBuilder::class,
        Rewrite::class,
	    BatchProcessor::class,
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
	 * Commands
	 *
	 * @var array
	 */
	private $commands = [
		WPametuCommand::class,
	];

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
     */
    protected function __construct( array $setting = [] ){
	    // Wait until theme is setup
	    add_action('after_setup_theme', [$this, 'setup'], 1);
	    // Register Meta boxes if exists
	    add_action('admin_menu', [$this, 'register_meta_box']);
	    // Register Post helper
	    add_action('init', [$this, 'scan_post_type']);
	    // Ajax actions
	    add_action('admin_init', [$this, 'ajax_register']);
	    // Widgets register
	    add_action('widgets_init', [$this, 'register_widgets']);
    }

	/**
	 * Register namespace
	 *
	 * @param string $namespace
	 * @param string $base
	 */
	public function register_namespace($namespace = '', $base = ''){
		if( $namespace && is_dir($base) ){
			$base = rtrim($base, DIRECTORY_SEPARATOR);
			$this->namespaces[$namespace] = $base;
		}
	}

	/**
	 * Auto load
	 */
	public function setup(){
		/**
		 * wpametu_autoloaded_classes
		 *
		 * Default class names which will be initialized after frameworks' bootstrap.
		 *
		 * @param array $classes
		 * @return array
		 */
		$class_names = apply_filters('wpametu_autoloaded_classes', $this->default_classes);
		foreach( $class_names as $class_name ){
            if( $this->is_singleton($class_name) ){
                $class_name::get_instance();
            }
        }
		// Register auto loader for each namespace
		$errors = new \WP_Error();
		$autoloaded_classes = [
            'Ajax' => AjaxBase::class,
            'QueryHighJack' => QueryHighJack::class,
            'Rest' => RestBase::class,
            'Widget' => Widget::class,
            'MetaBoxes' => MetaBox::class,
            'Admin/Screens' => Screen::class,
            'Admin/MetaBox' => EmptyMetaBox::class,
		];
		if( defined('WP_CLI') && WP_CLI ){
			$autoloaded_classes['Commands'] = Command::class;
		}
		foreach( $this->namespaces as $ns => $base_dir ){
			foreach( $autoloaded_classes as $base => $sub_class ){
				$sub_base = $base_dir.'/'.$ns.'/'.$base;
				// Skip if directory doesn't exist
	            if( !is_dir($sub_base) ){
	                continue;
	            }
				// Scan directory
	            foreach( scandir($sub_base) as $file ){
		            // Parse only PHP
	                if( !preg_match('/\.php$/u', $file) ){
		                continue;
	                }
		            // Build class name
	                $class_name = $ns.'\\'.str_replace('/', '\\', $base).'\\'.preg_replace('/\.php$/u', '', basename($file));
		            // Check class exitence
	                if( !class_exists($class_name) ){
	                    $errors->add(404, sprintf('Class %s doesn\'t exist.', $class_name));
		                continue;
	                }
		            // Check requirements
	                if( $this->is_sub_class_of($class_name, $sub_class, true) ){
		                // If this is singleton, call get_instance()
		                if( $this->is_sub_class_of($class_name, Singleton::class) ){
		                    $class_name::get_instance();
		                    switch( $sub_class ){
		                        case AjaxBase::class:
		                            $this->ajax_controllers[] = $class_name;
		                            break;
		                        case RestBase::class:
		                            Rewrite::register_class($class_name);
		                            break;
			                    default:
									// Do nothing
				                    break;
		                    }
		                }else{
		                    switch( $sub_class ){
		                        case Widget::class:
		                            $this->widgets[] = $class_name;
		                            break;
			                    case Command::class:
				                    if( defined('WP_CLI') && WP_CLI ){
										$this->commands[] = $class_name;
									}
				                    break;
			                    default:
				                    break;
		                    }
		                }
	                }
	            }
	        }
		}
		// Show error messages
		if( $errors->get_error_messages() ){
			add_action('admin_notices', function() use ($errors){
				printf('<div class="error"><p>%s</p></div>', implode('<br />', $errors->get_error_messages()));
			});
		}
		// Register WP_CLI commands
		if( defined('WP_CLI') && WP_CLI ){
			$this->register_commands();
		}
    }

    /**
     * Register meta boxes
     */
    public function register_meta_box(){
        $flg = false;
	    foreach( $this->namespaces as $ns => $dir ){
		    $sub_base = $dir.'/'.$ns.'/MetaBoxes';
	        if( is_dir($sub_base) ){
	            // Enqueue script flag
	            // Load all meta boxes
	            foreach( scandir($sub_base) as $file ){
	                if( !preg_match('/\.php$/u', $file) ){
	                    $class_name = $ns.'\\MetaBoxes\\'.str_replace('.php', '', $file);
	                    if( class_exists($class_name) && $this->is_sub_class_of($class_name, EditMetaBox::class) ){
	                        $class_name::get_instance();
	                        $flg = true;
	                    }
	                }
	            }
	        }
	    }
        if( $flg ){
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
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
	 * Register All commands
	 */
	private function register_commands(){
		foreach( $this->commands as $class_name ){
			\WP_CLI::add_command($class_name::COMMAND_NAME, $class_name);
		}
	}

    /**
     * Register ajax actions
     */
    public function ajax_register(){
        if( AjaxBase::is_ajax() ){
            foreach( $this->ajax_controllers as $class_name ){
                /** @var AjaxBase $instance */
                $instance = $class_name::get_instance();
                $instance->register();
            }
        }
    }

    /**
     * Scan original post type to override
     */
    public function scan_post_type(){
        $flg = false;
	    foreach( $this->namespaces as $ns => $dir ){
	        $dir = $dir.'/'.$ns.'/ThePost';
	        if( is_dir($dir) ){
	            foreach( scandir($dir) as $file ){
	                if( !preg_match('/^\./u', $file) ){
	                    $base_class = str_replace('.php', '', $file);
	                    $class_name = $ns.'\\ThePost\\'.$base_class;
	                    if( class_exists($class_name) && $this->is_sub_class_of($class_name, PostHelper::class) ){
	                        $this->post_type_to_override[$this->str->camel_to_hyphen($base_class)] = $class_name;
	                        $flg = true;
	                    }
	                }
	            }
	        }
	    }
        if( $flg ){
            add_action('the_post', [$this, 'the_post']);
        }
    }

    /**
     * Assign global $post object
     *
     * @param \WP_Post|\stdClass $post_obj
     */
    public function the_post( &$post_obj ){
        if( isset($this->post_type_to_override[$post_obj->post_type]) ){
            // Post type exists. Let's override
            $class_name = $this->post_type_to_override[$post_obj->post_type];
            global $post;
            $post->helper = new $class_name($post_obj);
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
            case 'str':
                return StringHelper::get_instance();
                break;
            default:
                // Do nothing
                break;
        }
    }
}