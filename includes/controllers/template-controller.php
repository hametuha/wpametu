<?php

namespace WPametu\Controllers;


/**
 * Class TemplateController
 *
 * @package WPametu\Controllers
 * @author Takahashi Fumiki
 */
abstract class TemplateController extends RewriteController
{

    /**
     * Current page
     *
     * @var int
     */
    protected $cur_page = 1;

    /**
     * Data passed to template
     *
     * @var array
     */
    private $data = [];

    /**
     * Post data.
     *
     * Even if you treat not post data,
     * You must assign this variables.
     *
     * @var array
     */
    protected $posts = [];

    /**
     * Reference to $wp_query
     * @var \WP_Query
     */
    protected $query = null;

    /**
     * Page title
     *
     * @var string
     */
    private $title = '';

    /**
     * Invoke public methods
     *
     * @param string $method
     * @param array $arguments
     * @param \WP_Query $wp_query
     */
    protected function doResult($method, array $arguments, \WP_Query &$wp_query = null){
        $this->query = $wp_query;
        $this->beforeExecute($method, $arguments);
        if(empty($arguments)){
            call_user_func([$this, $method]);
        }else{
            call_user_func_array([$this, $method],$arguments);
        }
        $this->afterExecute($method, $arguments);
        exit;
    }

    /**
     * Executed before method call
     *
     * Override this function if you need.
     *
     * @param string $method
     * @param array $arguments
     */
    protected function beforeExecute( $method, array $arguments){}

    /**
     * Executed after method call
     *
     * Override this function if you need.
     *
     * @param string $method
     * @param array $arguments
     */
    protected function afterExecute( $method, array $arguments){}

    /**
     * Load 404 template
     *
     * @param \WP_Query $wp_query
     */
    protected function notFound( \WP_Query $wp_query = null){
        if( is_null($wp_query) ){
            global $wp_query;
        }
        $wp_query->set_404();
        nocache_headers();
        do_action('template_redirect');
        include get_404_template();
    }

    /**
     * Returns method name
     *
     * @param \WP_Query $wp_query
     * @return string
     */
    protected function methodName( \WP_Query $wp_query){
        $segment = $wp_query->get('class_method');
        if(empty($segment)){
            return 'index';
        }else{
            $segments = explode('/', trim($segment, '/'));
            $segment = (string) array_shift($segments);
        }
        $method_name = $this->str->hyphenToCamel($segment);
        if( 0 === strpos($method_name, '_') ){
            $method_name = '';
        }
        return $method_name;
    }

    /**
     * Retrieve specified arguments
     *
     * Avoid path like /page/6 like string
     *
     * @param \WP_Query $wp_query
     * @return array
     */
    protected function methodArguments( \WP_Query $wp_query){
        $methods = parent::methodArguments($wp_query);
        $replace = [];
        // Exclude /page/6
        for($i = 0, $l = count($methods); $i < $l; $i++){
            if( ('page' ==  $methods[$i] && isset($methods[$i + 1]) && is_numeric($methods[$i + 1]) )
                || (is_numeric($methods[$i]) && isset($methods[$i - 1]) && 'page' == $methods[$i - 1] )
            ){
                if( is_numeric($methods[$i]) && isset($methods[$i - 1]) && 'page' == $methods[$i - 1] ){
                    $this->cur_page = intval($methods[$i]);
                }
                continue;
            }
            $replace[] = $methods[$i];
        }
        return $replace;
    }

    /**
     * Set data to store
     *
     * @param string $key
     * @param mixed $var
     */
    protected function set($key, $var){
        $this->data[$key] = $var;
    }

    /**
     * Get stored data
     *
     * @param string $key
     * @param mixed $var
     * @return mixed
     */
    protected function get($key, $var){
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Assign default data
     *
     * @param array $default
     * @return void
     */
    protected function assignDefault( array $default = []){
        $this->data = wp_parse_args($this->data, $default);
    }

    /**
     * Set page title
     *
     * @param $title
     */
    protected function setTitle( $title ){
        $this->title = $title;
    }

    /**
     * Short hand for wp_die
     *
     * @param string $message
     * @param int $http_status_code
     * @param bool $back_link
     */
    protected function error($message, $http_status_code = 500, $back_link = true){
        wp_die($message, get_status_header_desc($http_status_code), array(
            'response' => intval($http_status_code),
            'back_link' => (bool) $back_link,
        ));
    }

    /**
     * Returns template path to load.
     *
     * @param string $file Path to file
     * @throws \Exception
     * @return void
     */
    protected function load( $file ){
        if( file_exists($file) ){
            if( !did_action('template_redirect') ){
                /**
                 * Fires before determining which template to load.
                 *
                 * @since 1.5.0
                 */
                do_action( 'template_redirect' );
                switch( $this->requestMethod() ){
                    case 'head':
                        // Finish output
                        exit;
                        break;
                    case 'get':
                        // Do nothing
                        break;
                    default:
                        // Prevent caching
                        nocache_headers();
                        break;
                }
                if( !empty($this->title) ){
                    // Assign wp_title hook
                    $this_title = $this->title;
                    add_action('wp_title', function ($title, $sep, $seplocation) use ($this_title){
                        if( 'right' == $seplocation){
                            return esc_html($this_title)." {$sep} ";
                        }else{
                            return " {$sep} ".esc_html($this_title);
                        }
                    }, 10, 3);
                }
                include $file;
            }else{
                throw new \Exception(sprintf($this->__('%s::loadメソッドは1度しか呼び出せません'), get_called_class()));
            }
        }else{
            throw new \Exception(sprintf($this->__('テンプレートファイル%sが存在しません'), $file));
        }
    }

    /**
     * Load theme template if exists
     *
     * @param string $path
     * @param string $default
     */
    protected function loadTheme($path, $default = ''){
        $file = $this->getTemplatePart($path);
        if( !$file ){
            $file = $default;
        }
        $this->load($file);
    }

    /**
     * Returns path to template file in theme folder
     *
     * This method considers child theme:
     * <code>
     * $file = $this->getTemplatePart('archive-my-author');
     * </code>
     *
     * @param string $path Extension(.php) is not required
     * @return bool|string
     */
    protected function getTemplatePart($path){
        if( !preg_match('/\.php$/u', $path) ){
            $path .= '.php';
        }
        $path = DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
        $file = '';
        $temp_dir = get_template_directory();
        $css_dir = get_stylesheet_directory();
        if( file_exists($css_dir.$path) ){
            $file = $css_dir.$path;
        }else if( file_exists($temp_dir.$path) ){
            $file = $temp_dir.$path;
        }else{
            // Oops..
            $file = false;
        }
        return $file;
    }

    /**
     * This method is called on root page
     *
     * This method calls $this->page(1).
     * If you want to cusomize root URL,
     * implement $this->page. Argument $paged will be 1.
     */
    final public function index(){
        $this->page(1);
    }

    /**
     * This method is called on root page and its pagination
     *
     * @param int $paged
     * @return void
     */
    abstract public function page( $paged = 1 );
}