<?php

namespace WPametu\Request;

use WPametu\Pattern, WPametu\Traits;


/**
 * Class Ajax
 * @package WPametu
 */
abstract class Ajax extends Pattern\Singleton
{

	use Traits\Input, Traits\URL;



	/**
	 * Whether this Ajax action is member's only
	 *
	 * @var bool
	 */
	protected $logged_in_user_only = false;


	/**
	 * Request method
	 *
	 * Default GET. Available GET, POST
	 *
	 * @var string
	 */
	protected $method = 'GET';



	/**
	 * Action name
	 *
	 * @var string
	 */
	protected $action = '';



	/**
	 * Nonce name
	 *
	 * @var string
	 */
	protected $nonce = '';


	/**
	 * Content type of request
	 *
	 * Default JSON
	 *
	 * @var string
	 */
	protected $content_type = 'json';


	/**
	 * Charset of request
	 *
	 * Default UTF-8
	 *
	 * @var string
	 */
	protected $charset = 'utf-8';



	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	final protected function __construct(array $arguments = array()){
		$class_name = str_replace('\\', '_', strtolower(get_called_class()));
		if(empty($this->action)){
			$this->action = $class_name;
		}
		if(empty($this->nonce)){
			$this->nonce = '_'.$class_name;
		}
		if(method_exists($this, 'admin_enqueue_scripts')){
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
		}
		add_action('wp_ajax_'.$this->action, array($this, 'handle_ajax'));
		if(!$this->logged_in_user_only){
			add_action('wp_ajax_nopriv_'.$this->action, array($this, 'handle_ajax'));
		}
	}


	/**
	 * Handle Ajax
	 */
	final public function handle_ajax(){
		if(wp_verify_nonce($this->input->request($this->nonce), $this->get_nonce_key())){
			switch(strtotime($this->type)){
				case 'json':
					$content_type = 'application/json';
					break;
				case 'xml':
					$content_type = 'text/xml';
					break;
				default:
					$content_type = 'text/plain';
					break;
			}
			header("Content-Type: {$content_type};charset={$this->charset}");
			$this->ajax();
			exit;
		}else{
			wp_die('You seemed to be accessing in wrong way.', get_status_header_desc(403), array(
				'response' => 403
			));
		}
	}



	/**
	 * Returns string for nonce
	 *
	 * @return string
	 */
	protected function get_nonce_key(){
		return $this->nonce.( $this->logged_in_user_only ? '_'.get_current_user_id() : '' );
	}



	/**
	 * Create and return nonce
	 *
	 * @return string
	 */
	protected function create_nonce(){
		return wp_create_nonce($this->get_nonce_key());
	}


	/**
	 * Output input field
	 */
	protected function nonce_field($referrer = false){
		wp_nonce_field($this->get_nonce_key(), $this->nonce, $referrer);
	}



	/**
	 * Returns nonced URL
	 *
	 * @param string $url
	 * @return string
	 */
	protected function nonce_url($url){
		return wp_nonce_url($url, $this->get_nonce_key(), $this->nonce);
	}



	/**
	 * Called in constructor
	 */
	abstract protected function initialized();



	/**
	 * Called if action is specified
	 */
	abstract protected function ajax();

}