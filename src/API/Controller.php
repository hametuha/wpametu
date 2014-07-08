<?php

namespace WPametu\API;

use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Http\Input;


/*
 *
 *
 * @property-read Input $input
 */
abstract class Controller extends Singleton
{

    use i18n, Path;

    /**
     * Action names
     * @var array
     */
    private static $actions = [];

    /**
     * Action name
     *
     * Must be overridden and unique.
     *
     * @var string
     */
    protected $action = '';

    /**
     * Models
     *
     * Override this with [$key => $class_name]
     *
     * @var array
     */
    protected $models = [];


    /**
     * Check ajax notification
     *
     * If you want to change authentication,
     * override this method.
     *
     * @return bool
     */
    protected function auth(){
        return $this->verify_nonce();
    }

    /**
     * Create nonce
     *
     * @return string
     */
    public function create_nonce(){
        return wp_create_nonce($this->action);
    }

    /**
     * Make nonced URL
     *
     * @param string $url
     * @param string $key
     * @return string
     */
    public function nonce_url($url, $key = '_wpnonce'){
        return wp_nonce_url($url, $this->action, $key);
    }

    /**
     * Verify nonce
     *
     * @param string $key
     * @return bool
     */
    public function verify_nonce($key = '_wpnonce'){
        return wp_verify_nonce($this->request($key), $this->action);
    }

    /**
     * Echo nonce field
     *
     * @param string $key Default _wpnonce
     * @param bool $referer Default false.
     * @param bool $echo Default true
     * @return string
     */
    public function nonce_field($key = '_wpnonce', $referer = false, $echo = true){
        return wp_nonce_field($this->action, $key, $referer, $echo);
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'input':
                return Input::get_instance();
                break;
            default:
                if( isset($this->models[$name]) ){
                    return $this->models[$name]::get_instance();
                }
                break;
        }
    }
} 