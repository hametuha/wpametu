<?php

namespace WPametu\API;


use WPametu\Exception\OverrideException;
use WPametu\Pattern\Singleton;


/**
 * Class Ajax
 *
 * @package WPametu\API
 */
abstract class Ajax extends Controller
{

    /**
     * Which user can access
     *
     * @var string user, guest, all.
     */
    protected $target = 'user';

    /**
     * HTTP Method
     *
     * @var string get or post
     */
    protected $method = 'get';

    /**
     * Content Type
     *
     * @var string
     */
    protected $content_type = 'application/json';

    /**
     * Which screen to enqueue scripts
     *
     * @var string 'admin', 'public', 'both'. Default 'admin';
     */
    protected $screen = 'admin';


    /**
     * Required parameters
     *
     * @var array
     */
    protected $required = [];

    /**
     * Constructor
     *
     * @param array $setting
     * @throws OverrideException
     */
    protected function __construct( array $setting = [] ){
        // Check if action exists and is unique.
        if( empty($this->action) || false !== array_search($this->action, self::$actions) ){
            throw new OverrideException(get_called_class());
        }
        // Save action
        self::$actions[] = $this->action;
        // Occur on Ajax request
        if( defined('DOING_AJAX') && DOING_AJAX ){
            // Add action
            add_action('admin_init', [$this, 'register']);
        }
        // Register scripts
        switch( $this->screen ){
            case 'public':
                add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
                break;
            case 'both':
                add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
                add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
                break;
            default:
                add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
                break;
        }
    }

    /**
     * Convert Data to JSON format
     *
     * You can override this action
     *
     * @param array $data
     * @return mixed|string|void
     */
    protected function encode($data){
        return json_encode($data);
    }

    /**
     * Enqueue scripts and asset
     *
     * @param string $page
     * @return mixed
     */
    abstract public function enqueue_assets( $page = '' );

    /**
     * Register ajax.
     */
    public function register(){
        switch( $this->target ){
            case 'all':
                add_action('wp_ajax_nopriv_'.$this->action, [$this, 'ajax']);
                add_action('wp_ajax_'.$this->action, [$this, 'ajax']);
                break;
            case 'guest':
                add_action('wp_ajax_nopriv_'.$this->action, [$this, 'ajax']);
                break;
            default:
                add_action('wp_ajax_'.$this->action, [$this, 'ajax']);
                break;
        }
    }

    /**
     * Do Ajax
     */
    public function ajax(){
        if('post' == $this->method){
            nocache_headers();
        }
        try{
            // Authenticate
            if( !$this->auth() ){
                $this->error($this->__('Sorry, but you have no permission.'), 403);
            }
            // Validate
            if( !empty($this->required) ){
                foreach( $this->required as $key ){
                    if( !isset($_REQUEST[$key]) ){
                        $this->error($this->__('Sorry, but request parameters are wrong.'), 400);
                    }
                }
            }
            // O.K.
            $data = $this->get_data();
        }catch( \Exception $e ){
            $data = [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
        header('Content-Type', $this->content_type);
        echo $this->encode($data);
        exit;
    }


    /**
     * Returns Ajax endpoint
     *
     * @return string
     */
    protected function ajax_url(){
        $url = admin_url('admin-ajax.php');
        if( is_ssl() ){
            $url = str_replace('http://', 'https://', $url);
        }else{
            $url = str_replace('https://', 'http://', $url);
        }
        return $url;
    }

    /**
     * Returns data as array.
     *
     * @return array
     */
    abstract protected function get_data();
}
