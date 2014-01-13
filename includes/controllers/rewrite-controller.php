<?php

namespace WPametu\Controllers;


/**
 * Class RewriteController
 *
 * @package WPametu\Controllers
 * @author Takahashi Fumiki
 */
abstract class RewriteController extends BaseController
{

    /**
     * Rewrite rule string
     *
     * @var array
     */
    protected $rewrites = [];

    /**
     * Additional query vars
     *
     * @var array
     */
    protected $query_vars = [];

    /**
     * Constructor
     *
     * @param array $argument
     * @throws \Exception
     */
    final protected function __construct( array $argument = [] ){
        $this->initialized();
        if( empty($this->rewrites) ){
            throw new \Exception( $this->__('変数\$rewriteにリライトルールを設定する必要があります') );
        }
        // Add query vars
        if(!empty($this->query_vars)){
            $this->rewrite_ruler->addQueryVars($this->query_vars);
        }
        // Register rewrite
        foreach($this->rewrites as $rewrite => $regexp){
            $this->rewrite_ruler->registerRewrite($rewrite, $regexp, get_called_class());
        }
    }

    /**
     * Detect if method is invokable as framework
     *
     * @param string $method
     * @return bool
     */
    protected function isInvokable($method){
        if( !method_exists($this, $method) ){
            return false;
        }
        $reflection = new \ReflectionMethod(get_called_class(), $method);
        return $reflection->isPublic();
    }

    /**
     * Parse request and call method
     *
     * @param \WP_Query $wp_query
     * @return mixed
     */
    abstract public function parseRequest( \WP_Query $wp_query);

}
