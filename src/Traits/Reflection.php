<?php

namespace WPametu\Traits;


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
        if( class_exists($class_name) ){
            // Check if this is subclass
            $refl = new \ReflectionClass($class_name);
            return !$refl->isAbstract() && $refl->isSubclassOf(Singleton::class);
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
