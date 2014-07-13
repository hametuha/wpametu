<?php

namespace WPametu\API\Rest;
use WPametu\API\Controller;
use WPametu\Exception\AuthException;


/**
 * Rest Controller base
 *
 * @package WPametu\API
 */
class RestBase extends Controller
{

    /**
     * WP_Query instance
     *
     * @var \WP_Query
     */
    protected $wp_query = null;

    /**
     * Parse request
     *
     * @param string $uri
     * @param \WP_Query $wp_query
     */
    public function parse_request($uri, \WP_Query &$wp_query = null){
        // Set wp_query if exists
        if( isset($wp_query) ){
            $this->wp_query = $wp_query;
        }
        // Parse URI
        $uri = explode('/', trim($uri, '/'));
        $method = array_shift($uri);
        $request_method = $this->input->request_method();
        // Call method if exists
        if( $this->invoke($method, $request_method, $uri) ){
            exit;
        }else{
            $this->method_not_found();
        }
    }

    /**
     * Search method and execute if exists.
     *
     * @param string $method_name
     * @param string $request_method
     * @param array $arguments
     * @return bool
     */
    protected  function invoke($method_name, $request_method, array $arguments = []){
        $method_name = strtolower($request_method).'_'.$this->str->to_snake_case($method_name);
        // Check if method exists
        if( !is_callable([$this, $method_name]) ){
            return false;
        }
        // Check accessibility
        $reflection = new \ReflectionMethod($this, $method_name);
        if( !$reflection->isPublic() || $reflection->isStatic() ){
            return false;
        }
        // Check required arguments length
        if( $reflection->getNumberOfRequiredParameters() > count($arguments) ){
            return false;
        }
        // O.K. It's public method. Call it.
        $this->handle_result(call_user_func_array([$this, $method_name], $arguments));
        return true;
    }

    /**
     * Handle request
     *
     * @param $result
     */
    protected function handle_result($result){
        // Do nothing. override this.
    }

    /**
     * Fires when 404
     */
    protected function method_not_found(){
        $this->error($this->__("Sorry, but this request doesn't exist."), 404);
    }

    /**
     * Throws authentication error
     *
     * @throws \WPametu\Exception\AuthException
     */
    protected function auth_error(){
        throw new AuthException($this->__('Authentication require.'));
    }

    /**
     * Check authentication
     */
    protected function check_login(){
        if( !is_user_logged_in() ){
            $this->auth_error();
        }
    }
}
