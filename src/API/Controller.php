<?php

namespace WPametu\API;

use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Http\Input;
use WPametu\Utility\IteratorWalker;


/**
 * Controller base class
 *
 * @package WPametu\API
 * @property-read Input $input
 * @property-read IteratorWalker $walker
 */
abstract class Controller extends Singleton
{

    use i18n, Path;

    /**
     * Action names
     * @var array
     */
    protected static $actions = [];

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
     * Authentication isn't required
     *
     * If authentication with nonce is not required
     *
     * @var bool
     */
    protected $no_auth = false;

    /**
     * Check ajax notification
     *
     * If you want to change authentication,
     * override this method.
     *
     * @return bool
     */
    protected function auth(){
        return $this->no_auth || $this->verify_nonce();
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
        return wp_verify_nonce($this->input->request($key), $this->action);
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
     * Load template
     *
     * @param string $slug
     * @param string $name
     * @param array $args This array will be extract
     */
    public function load_template($slug, $name = '', array $args = []){
        $base_path = $slug.'.php';
        $original_path = $slug.( !empty($name) ? '-'.$name : '' ).'.php';
        $found_path = '';
        foreach( [$original_path, $base_path] as $file ){
            foreach( [get_stylesheet_directory(), get_template_directory()] as $dir ){
                $path = $dir.'/'.ltrim($file, '\\');
                if( file_exists($path) ){
                    $found_path = $path;
                    break 2;
                }
            }
        }
        if( $found_path ){
            // Path found. Let's locate template.
            global $post, $posts, $wp_query;
            if( !empty($args) ){
                extract($args);
            }
            include $found_path;
        }
    }

    /**
     * Throws error
     *
     * @param string $message
     * @param int $code
     * @throws \Exception
     */
    protected function error($message, $code = 400){
        throw new \Exception($message, $code);
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
            case 'walker':
                return IteratorWalker::get_instance();
                break;
            default:
                if( isset($this->models[$name]) ){
                    $class_name = $this->models[$name];
                    return $class_name::get_instance();
                }
                break;
        }
    }
} 