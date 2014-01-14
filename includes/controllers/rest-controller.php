<?php

namespace WPametu\Controllers;

/**
 * Class RestController
 *
 * Extend this class to implement RESTfull API
 *
 * @package WPametu\Controllers
 * @author Takahashi Fumiki
 */
abstract class RestController extends RewriteController
{

    /**
     * Get method to execute
     *
     * @param \WP_Query $wp_query
     * @return string
     */
    protected function methodName( \WP_Query $wp_query ){
        $segments = explode('/', trim($wp_query->get('class_method'), '/'));
        $first_segment = array_shift($segments);
        if(!$first_segment){
            return false;
        }
        return $this->str->hyphenToCamel($this->requestMethod().'-'.$first_segment);
    }

    /**
     * Output result
     *
     * @param string $method
     * @param array $argument
     * @param \WP_Query $wp_query
     */
    protected function doResult($method, array $argument = [], \WP_Query &$wp_query ){
        $this->contentType();
        if('get' !== $this->requestMethod()){
            // If method is not 'get',
            // echo no cache headers
            nocache_headers();
        }
        if(empty($segments)){
            $var = call_user_func([$this, $method]);
        }else{
            $var = call_user_func_array([$this, $method], $segments);
        }
        $this->output($var);
    }

    /**
     * Executed if method not found
     *
     * @param \WP_Query $wp_query
     */
    protected function notFound( \WP_Query $wp_query){
        $this->contentType();
        nocache_headers();
        $this->error(404, $this->__('指定されたメソッドは存在しません'));
    }

    /**
     * Executed before output result.
     *
     * <code>
     * header('Content-Type: application/json');
     * </code>
     *
     * @return void
     */
    abstract protected function contentType();

    /**
     * Executed if error occurs
     *
     * @param int $code
     * @param string $message
     * @return void
     */
    abstract protected function error($code, $message = '');

    /**
     * Return output
     *
     * This controller parses requested method and
     * make object with proper method.
     *
     * <code>
     * echo json_encode($var);
     * </code>
     *
     * @param mixed $var
     * @return void
     */
    abstract protected function output($var);
} 