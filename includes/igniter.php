<?php

namespace WPametu;

/**
 * Class Igniter
 *
 * This class fires WPametu Framework
 *
 * @package WPametu
 * @author Takahashi Fumiki
 */
final class Igniter extends Pattern\Singleton
{

	use Traits\Util, Traits\i18n;

    /**
     * @var bool If true, initialization never fired.
     */
    protected $fired = false;

    /**
     * Table's class names to load
     * @var array
     */
    private $tables_to_load = [
        'default' => []
    ];

    /**
     * Framework users group and root directory
     * @var array
     */
    private $framework_users = [];


    /**
     * Folder and class mapping
     *
     * This parameter assigns folder to class name
     *
     * @var array
     */
    private $class_map = [
        'controllers' => '\\WPametu\\Controllers\\BaseController',
        'schemata' => '\\WPametu\\DB\\SchemaBase',
        'models' => '\\WPametu\\Models\\ModelBase',
        'metabox' => '\\WPametu\\UI\\Admin\\Metabox\\Base',
    ];

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( array $arguments = [] ){
        // Register scripts and css.
        Script::getInstance();
        Css::getInstance();

        // Register Init action
        add_action('init', [$this, 'initializeFrameworks'], 2);
	}

    /**
     * Register user framework
     *
     * @param string $group Unique name for user
     * @param string $dir Root directory of Framework
     */
    public function addFramework($group, $dir){
        if(did_action('wp_loaded')){
            trigger_error($this->__('フレームワークの登録はwpametu_initフックで行ってください'), E_USER_WARNING);
        }elseif('default' == $group){
            trigger_error($this->__('defaultはフレームワークの名称として許可されていません'), E_USER_WARNING);
        }elseif(!$this->str->isAlnumHyphen($group, false)){
            trigger_error($this->__('$groupに使用できるのは半角英数（小文字）とハイフンのみです'), E_USER_WARNING);
        }elseif( isset($this->framework_users[$group]) ){
            trigger_error( sprintf($this->__('フレムワーク%sはすでに登録済みです'), $group), E_USER_WARNING);
        }elseif( !is_dir($dir) ){
            trigger_error( sprintf($this->__('%sはディレクトリではありません'), $dir), E_USER_WARNING );
        }else{
            $this->framework_users[$group] = untrailingslashit($dir);
        }

    }


	/**
	 * Autoloader required class
	 *
	 * This autoload class will be called
	 *
	 * @uses \Spyc::YAMLLoad to parse setting.yaml
	 */
	public function initializeFrameworks(){
        if($this->fired){
            return;
        }
        /**
         * Fires when wpametu ready
         *
         * Use this to add Framework.
         */
        do_action('wpametu_init');

        // Autoloader
        spl_autoload_register([$this, 'autoLoad']);

		// Read setting file
		$config = \Spyc::YAMLLoad(BASE_DIR.'/setting.yaml');
		if( isset($config['autoloads']) && is_array($config['autoloads']) ){
			foreach($config['autoloads'] as $class_name){
				if( method_exists($class_name, 'getInstance') ){
					call_user_func([$class_name, 'getInstance']);
				}
			}
        }
        // Scan directory and auto load.
        foreach($this->framework_users as $group => $dir){
            // Scan directory
            foreach( $this->class_map as $folder => $class_required){
                $target_dir = $dir.DIRECTORY_SEPARATOR.$folder;
                if(is_dir($target_dir)){
                    foreach(scandir($target_dir) as $file_name){
                        if(preg_match('/\.php$/u', $file_name)){
                            $class_name = implode('\\', [
                                '',
                                $this->str->hyphenToCamel($group, true),
                                $this->str->hyphenToCamel($folder, true),
                                $this->str->hyphenToCamel(str_replace('.php', '', $file_name), true)
                            ]);
                            if( $this->reflection->satisfies($class_name, $class_required)){
                                $class_name::getInstance();
                            }
                        }
                    }
                }
            }
        }
        $this->fired = true;
	}

    /**
     * Scan specific directory and fires table
     *
     * @param string $dir Frameworks directory
     * @param string $namespace Namespace of framework
     * @param string $key
     */
    private function fireTables($dir, $namespace, $key){
        if( is_dir($dir) ){
            foreach(glob(untrailingslashit($dir).'/tables/*.php') as $file){
                require $file;
                $base_class = $this->str->hyphenToCamel(str_replace('.php', '', basename($file)), true);
                $class_name = $namespace.'\\'.$this->str->hyphenToCamel($key, true).'\\'.$base_class;
                // Detect if class inherits Table class
                if( $this->reflection->satisfies($class_name, $this->class_map[$key]) ){
                    $class_name::getInstance();
                }
            }
        }
    }

    /**
     * Class autoloader for user framework
     *
     * @param string $class_name
     */
    public function autoLoad($class_name){
        $str = $this->str;
        $path_segments = array_map(function ($path) use ($str){
            return $str->camelToHyphen($path);
        }, explode('\\', ltrim($class_name, '\\')));
        if( 1 < count($path_segments) && isset($this->framework_users[$path_segments[0]]) ){
            $path_segments[0] = $this->framework_users[$path_segments[0]];
            $file_path = implode(DIRECTORY_SEPARATOR, $path_segments).'.php';
            if( file_exists($file_path) ){
                require $file_path;
            }
        }
    }

    /**
     * Convert Namespace to directory
     *
     * @param string $namespace
     * @return false|string
     */
    private function convertNamespace($namespace){
        $group = $this->str->camelToHyphen($namespace);
        if( isset($this->framework_users[$group]) ){
            $dir = $this->framework_users[$group];
            if(is_dir($dir)){
                return $dir;
            }
        }
        return false;
    }
} 