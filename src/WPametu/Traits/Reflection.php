<?php

namespace WPametu\Traits;


use WPametu\API\Controller;
use WPametu\Pattern\Singleton;


/**
 * Reflection helper
 *
 * @package WPametu
 */
trait Reflection
{

	use \Hametuha\Pattern\Utility\Reflection;

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
}
