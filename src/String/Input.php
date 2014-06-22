<?php

namespace WPametu\String;
use WPametu\Pattern\Singleton;


/**
 * Input utility class
 *
 * @package WPametu\String
 */
class Input extends Singleton
{
    /**
     * Return GET Request
     *
     * @param string $key
     * @return null|string|array
     */
    public function get($key){
        if( isset($_GET[$key]) ){
            return $_GET[$key];
        }else{
            return null;
        }
    }

    /**
     * Return POST Request
     *
     * @param string $key
     * @return null|string|array
     */
    public function post($key){
        if( isset($_POST[$key]) ){
            return $_POST[$key];
        }else{
            return null;
        }
    }

    /**
     * Return REQUEST
     *
     * @param string $key
     * @return null|string|array
     */
    public function request($key){
        if( isset($_REQUEST[$key]) ){
            return $_REQUEST[$key];
        }else{
            return null;
        }
    }

    /**
     * Returns post body
     *
     * This method is useful for typical XML API.
     *
     * @return string
     */
    public function post_body(){
        return file_get_contents('php://input');
    }

    /**
     * Sanitize super globals
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitize($value){
        // TODO: Sanitize
        return $value;
    }
}
