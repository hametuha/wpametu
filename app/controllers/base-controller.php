<?php

namespace WPametu\Controllers;

use WPametu\Pattern, WPametu\Traits;

/**
 * Class Controller
 *
 * @package WPametu
 * @author Takahashi Fumiki
 * @property-read \WPametu\HTTP\RewriteRuler $rewrite_ruler
 */
abstract class BaseController extends Pattern\Singleton
{
    use Traits\i18n, Traits\Util {
        Traits\Util::__get as traitGet;
    }


    /**
     * Executed at constructor
     *
     * You can override this method
     * for some stuff.
     *
     * <code>
     * // For example, assign member
     * // in generic way
     * $this->regexp = 'index.php?some='.$this->some_func();
     * </code>
     */
    protected function initialized(){

    }


    /**
     * Return current request method
     *
     * @return string
     */
    protected function requestMethod(){
        if(isset($_SERVER['REQUEST_METHOD'])){
            switch(strtolower($_SERVER['REQUEST_METHOD'])){
                case 'get':
                case 'put':
                case 'post':
                case 'delete':
                case 'head':
                    return strtolower($_SERVER['REQUEST_METHOD']);
                    break;
                default:
                    // Do nothing
                    break;
            }
        }
        return 'get';
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'rewrite_ruler':
                return \WPametu\HTTP\RewriteRuler::getInstance();
                break;
            default:
                return $this->traitGet($name);
                break;
        }
    }
} 