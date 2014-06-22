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
     * First path segment
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Extension
     *
     * @var string
     */
    protected $extension = '';

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
        if( empty($this->prefix) || !$this->str->isUrlSegments($this->prefix, false, false) ){
            throw new \Exception($this->__('変数\$prefixは半角英数（小文字のみ）およびスラッシュ、ハイフンのみ使用できます。'));
        }
        $rewrites = $this->generateRewriteRules();
        $this->rewrites = array_merge($this->rewrites, $rewrites);
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
     * Generate rewrite rules
     *
     * Overriding this function, rewrite rules can be customize
     *
     * @return array
     */
    protected function generateRewriteRules(){
        if( !empty($this->extension) && $this->str->isAlphaNumeric($this->extension) ){
            $rewrite = "{$this->prefix}/(.+)".'\.'.$this->extension.'$';
        }else{
            $rewrite = "{$this->prefix}(/.+)?";
        }
        return [
            $rewrite => 'index.php?class_method=$matches[1]',
        ];
    }

    /**
     * Detect if method is invokable as framework
     *
     * @param string $method
     * @return bool
     */
    protected function isInvokable($method){
        if( empty($method) ){
            return false;
        }
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
    final public function parseRequest( \WP_Query &$wp_query){
        $method = $this->methodName($wp_query);
        $arguments = $this->methodArguments($wp_query);
        if( $this->isInvokable($method) ){
            $this->doResult($method, $arguments, $wp_query);
        }else{
            $this->notFound($wp_query);
        }
        exit;
    }

    /**
     * Get method to execute from $wp_query
     *
     * @param \WP_Query $wp_query
     * @return mixed
     */
    abstract protected function methodName( \WP_Query $wp_query);

    /**
     * Returns method arguments
     *
     * @param \WP_Query $wp_query
     * @return array
     */
    protected function methodArguments( \WP_Query $wp_query ){
        $segments = explode('/', trim($wp_query->get('class_method'), '/') );
        array_shift($segments);
        return $segments;
    }

    /**
     * Do results if method found
     *
     * @param string $method
     * @param array $arguments
     * @param \WP_Query $wp_query
     * @return void
     */
    abstract protected function doResult($method, array $arguments, \WP_Query &$wp_query);

    /**
     * Executed if method not found
     *
     * @param \WP_Query $wp_query
     * @return void
     */
    abstract protected function notFound( \WP_Query $wp_query );
}
