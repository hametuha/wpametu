<?php

namespace WPametu\API\Rest;
use WPametu\API\Controller;
use WPametu\API\RewriteParser;
use WPametu\Exception\AuthException;


/**
 * Rest Controller base
 *
 * @package WPametu\API
 * @property-read \WP_User $user
 */
class RestBase extends RewriteParser
{

    /**
     * Rewrite rules array
     *
     * @var string
     */
    public static $prefix = '';

    /**
     * WP_Query instance
     *
     * @var \WP_Query
     */
    protected $wp_query = null;

    /**
     * Handle request
     *
     * @param string $method
     * @param string $request_method
     * @param array $arguments
     */
    protected function handle_request($method, $request_method, array $arguments = []){
        if( empty($method) || 'page' === $method ){
            $page = max( (isset($arguments[0]) ? intval($arguments[0]) : 1), 1);
            $this->pager($page);
            exit;
        }else{
            // Call method if exists
            if( $this->invoke($method, $request_method, $arguments) ){
                exit;
            }else{
                $this->method_not_found();
            }
        }
    }

    /**
     * Pager
     *
     * @param int $page
     */
    protected function pager($page = 1){
        $this->method_not_found();
    }

    /**
     * Check auth and redirect if not logged in
     */
    protected function auth_redirect(){
        if( !is_user_logged_in() ){
            auth_redirect();
            exit;
        }
    }

    /**
     * Return url
     *
     * @param string $uri
     * @param bool $ssl
     * @return string
     */
    public function url($uri = '', $ssl = false){
        $class_name = get_called_class();
        $seg = explode('\\', $class_name);
        $base = $seg[count($seg) - 1];
        $prefix = trim($class_name::$prefix ?: $this->str->camel_to_hyphen($base), '/');
        $uri = ltrim($uri, '/');
        return home_url($prefix.'/'.$uri, $ssl ? 'https' : 'http');
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed|null|\WP_User
     */
    public function __get($name){
        switch( $name ){
            case 'user':
                $user = wp_get_current_user();
                return $user->ID ? $user : null;
                break;
            default:
                return parent::__get($name);
                break;
        }
    }
}
