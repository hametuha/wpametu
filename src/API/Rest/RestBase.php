<?php

namespace WPametu\API\Rest;
use WPametu\API\Controller;
use WPametu\API\RewriteParser;
use WPametu\Exception\AuthException;


/**
 * Rest Controller base
 *
 * @package WPametu\API
 */
class RestBase extends RewriteParser
{

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
}
