<?php

namespace WPametu\API\Rest;
use WPametu\Http\PostRedirectGet;


/**
 * Rest Page template
 *
 * @package WPametu\API\Rest
 * @property-read PostRedirectGet $prg
 */
class RestTemplate extends RestBase
{

    /**
     * Page title
     *
     * @var string
     */
    protected $title = '';

    /**
     * Data object
     *
     * @var array
     */
    private $data = [];

    /**
     * Detect if filter hook was registered
     *
     * @var bool
     */
    private $filtered = false;

    /**
     * Is always public page
     *
     * @var string
     */
    protected $screen = 'public';

    /**
     * Content type
     *
     * @var string
     */
    protected $content_type = 'application/json';

    /**
     * Set data
     *
     * @param array|mixed $data
     * @param string $key
     */
    final protected function set_data($data, $key = ''){
        if( empty($key) ){
            if( is_array($data) ){
                $this->data = array_merge($this->data, $data);
            }
        }else{
            $this->data[$key] = $data;
        }
    }

    /**
     * Template loader
     *
     * @param string $slug
     * @param string $name
     * @param array $args
     */
    public function load_template($slug, $name = '', array $args = []){
        if( !did_action('template_redirect') ){
            do_action('template_redirect');
        }
        if( !$this->filtered ){
            add_filter('wp_title', [$this, 'wp_title'], 10, 3);
        }
        parent::load_template($slug, $name, $this->data, $args);
    }

    /**
     * Filter wp title
     *
     * @param string $title
     * @param string $sep
     * @param string $location
     * @return string
     */
    public function wp_title($title, $sep, $location){
        if( !empty($this->title) ){
            $array = [$this->title];
            $sep = ' '.trim($sep).' ';
            if( 'right' == $location ){
                $array[] = $sep;
            }else{
                array_unshift($array, $sep);
            }
            $title = implode('', $array);
        }
        return $title;
    }



    /**
     * Do response
     *
     * Echo JSON with set data.
     */
    protected function response(){
        header('Content-Type', $this->content_type);
        $this->format($this->data);
        exit;
    }

    /**
     * Echo data
     *
     * @param array $data This data
     */
    protected function format($data){
        echo json_encode($data);
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed|null|\WP_User|\WPametu\Pattern\Singleton
     */
    public function __get($name){
        switch( $name ){
            case 'prg':
                return PostRedirectGet::get_instance();
                break;
            default:
                return parent::__get($name);
                break;
        }
    }
}
