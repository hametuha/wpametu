<?php

namespace WPametu\API;


use WPametu\Exception\OverrideException;
use WPametu\Pattern\Singleton;


/**
 * Class Ajax
 *
 * @package WPametu\API
 * @property-read Input $input
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
        if( $this->auth() ){
            $data = $this->get_data();
        }else{
            $data = [
                'error' => true,
                'code' => 403,
                'message' => $this->__('Sorry, but you have no permission.')
            ];
        }
        header('Content-Type', $this->content_type);
        echo $this->encode($data);
        exit;
    }

    /**
     * Open form
     *
     * @param array $attributes
     * @param bool $echo
     * @return string
     */
    public function form_open( array $attributes = [], $echo = true){
        $attributes = array_merge([
            'method' => $this->method,
            'action' => $this->ajax_url(),
        ], $attributes);
        $str = [];
        foreach( $attributes as $key => $value ){
            $str[] = sprintf('%s="%s"', $key, esc_attr($value));
        }
        $str = implode(' ', $str);
        $html = "<form {$str}>";
        $html .= sprintf('<input type="hidden" name="action" value="%s" />', esc_attr($this->action));
        $html .= $this->nonce_field('_wpnonce', false, false);
        if( $echo ){
            echo $html;
        }
        return $html;
    }

    /**
     * Close form
     *
     * @param bool $echo
     * @return string
     */
    public function form_close($echo = true){
        $form = '</form>';
        if( $echo ){
            echo $form;
        }
        return $form;
    }

    /**
     * Returns Ajax endpoint
     *
     * @return string
     */
    private function ajax_url(){
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
