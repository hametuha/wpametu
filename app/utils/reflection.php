<?php

namespace WPametu\Utils;

use WPametu\Pattern;

/**
 * Class Reflection
 *
 * @package WPametu\Utils
 * @author Takahashi Fumiki
 */
class Reflection extends Pattern\Singleton
{

    /**
     * Constructor
     *
     * @param array $argument
     */
    protected function __construct( array $argument = [] ){}

    /**
     * Detect if method is publicly callable
     *
     * @param string $class_name
     * @param string $method
     * @return bool
     */
    public function isMethodPublic($class_name, $method){
        if( !class_exists($class_name) ){
            return false;
        }
        $reflection = new \ReflectionMethod($class_name, $method);
        return $reflection->isPublic();
    }

    /**
     * Detect if specified class is subclass of required class
     *
     * @param string $class_name
     * @param string $required_class
     * @return bool
     */
    public function satisfies($class_name, $required_class){
        if( class_exists($class_name) && class_exists($required_class)){
            $reflection = new \ReflectionClass($class_name);
            if(!$reflection->isAbstract() && $reflection->isSubclassOf($required_class)){
                return true;
            }
        }
        return false;
    }
} 