<?php

namespace WPametu\Traits;


use WPametu\API\Controller;
use WPametu\Pattern\Singleton;


/**
 * Reflection helper
 *
 * @package WPametu\Traits
 */
trait Reflection
{

    /**
     * Check if specified class is singleton
     *
     * @param string $class_name
     * @return bool
     */
    protected function is_singleton($class_name){
        return $this->is_sub_class_of($class_name, Singleton::class);
    }

    /**
     * Check if specified class is controller
     *
     * @param string $class_name
     * @return bool
     */
    protected function is_controller($class_name){
        return $this->is_sub_class_of($class_name, Controller::class);
    }

    /**
     * Detect if specifies class is subclass
     *
     * @param $class_name
     * @param $should Parent class name
     * @return bool
     */
    protected function is_sub_class_of($class_name, $should){
        if( class_exists($class_name) ){
            // Check if this is subclass
            $refl = new \ReflectionClass($class_name);
            return !$refl->isAbstract() && $refl->isSubclassOf($should);
        }
        return false;
    }

    /**
     * Returns all defined constants
     *
     * @return array
     */
    public static function get_all_constants(){
        $refl = new \ReflectionClass(get_called_class());
        return $refl->getConstants();
    }
}
