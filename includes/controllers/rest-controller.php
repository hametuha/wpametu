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
    protected $extension = 'json';

    /**
     * Create rewrite rule
     *
     * @throws \Exception
     */
    protected function initialized(){
        if(empty($this->prefix) || !$this->str->isUrlSegments($this->prefix, false, false)){
            throw new \Exception($this->__('変数\$prefixは半角英数（小文字のみ）およびスラッシュ、ハイフンのみ使用できます。'));
        }
        $this->rewrites = [
            "{$this->prefix}/(.+)\.{$this->extension}$" => 'index.php?class_method=$matches[1]',
        ];
    }

    /**
     * Parse request and execute method
     *
     * @param \WP_Query $wp_query
     * @return void
     */
    public function parseRequest( \WP_Query $wp_query ){
        $segments = explode('/', $wp_query->get('class_method'));
        $method = $this->str->hyphenToCamel($this->getRequestMethod().'-'.array_shift($segments));
        $this->contentType();
        if( method_exists($this, $method)
            && ($reflexion = new \ReflectionMethod(get_called_class(), $method))
            && $reflexion->isPublic()
        ){
            if(empty($segments)){
                $var = call_user_func([$this, $method]);
            }else{
                $var = call_user_func_array([$this, $method], $segments);
            }
            $this->output($var);
        }else{
            $this->notFound();
        }
        exit;
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
     * Executed if method not found
     *
     * @return void
     */
    abstract protected function notFound();

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
     * @param $var
     * @return void
     */
    abstract protected function output($var);

    /**
     * Return current request method
     *
     * @return string
     */
    private function getRequestMethod(){
        if(isset($_SERVER['REQUEST_METHOD'])){
            switch(strtolower($_SERVER['REQUEST_METHOD'])){
                case 'get':
                case 'put':
                case 'post':
                case 'delete':
                    return strtolower($_SERVER['REQUEST_METHOD']);
                    break;
                default:
                    // Do nithing
                    break;
            }
        }
        return 'get';
    }
} 