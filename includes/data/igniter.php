<?php

namespace WPametu\Data;

use WPametu\Pattern;

/**
 * Database igniter
 *
 * @package WPametu\Data
 * @property-read array $default_models
 */
final class Igniter extends Pattern\Singleton
{

    use \WPametu\Traits\Util;

    /**
     * Model's class names to load
     * @var array
     */
    private $models_to_load = [];

	/**
	 * Constructor
	 *
	 * Constructor should not be public.
	 *
	 * @param array $argument
	 */
	protected function __construct(array $argument){
		// Load all tables
        foreach(glob(dirname(__FILE__).'/tables/*.php') as $file){
            // Do not load abstract table
            if( false !== strpos($file, 'abstract') ){
                continue;
            }
            require $file;
            $class_name = '\\WPametu\\Data\\Tables\\'.$this->str->hyphenToCamel(str_replace('.php', '', basename($file)), true);
            // Detect if class inherits Table class
            if( class_exists($class_name) && method_exists($class_name, 'doesInheritTable') ){
                $reflection = new \ReflectionClass($class_name);
                if(!$reflection->isAbstract() && $class_name::doesInheritTable()){
                    $class_name::getInstance();
                }
            }
        }
	}

    public function addModel($key, $className){

    }
}