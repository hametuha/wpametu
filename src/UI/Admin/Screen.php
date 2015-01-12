<?php

namespace WPametu\UI\Admin;

use WPametu\Http\Input;
use WPametu\Http\PostRedirectGet;
use WPametu\Pattern\Singleton;

/**
 * Admin screen
 *
 * @package WPametu\UI\Admin
 * @property-read Input $input
 * @property-read PostRedirectGet $prg
 * @property-read string $base_url
 */
abstract class Screen extends Singleton
{

	/**
	 * Page title
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Menu title
	 *
	 * Use page title if empty
	 *
	 * @var string
	 */
	protected $menu_title = '';

	/**
	 * Parent page name
	 *
	 * @var string
	 */
	protected $parent = '';

	/**
	 * Capability
	 *
	 * @var string
	 */
	protected $caps = 'edit_posts';

	/**
	 * Page slug
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Template name
	 *
	 * @var string
	 */
	protected $template = '';

	/**
	 * Icon class
	 *
	 * @var string
	 */
	protected $icon = 'dashicons-chart-bar';

	/**
	 * Menu position
	 *
	 * @var int
	 */
	protected $position = 25;

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = [ ] ) {
		if( is_admin() ){
			if( empty($this->menu_title) ){
				$this->menu_title = $this->title;
			}
			if( empty($this->template) ){
				$class_name = explode('\\', get_called_class());
				$this->template = $class_name[count($class_name) - 1];
			}
			add_action("admin_menu", array($this, 'adminMenu'));
			add_action('admin_init', array($this, 'adminInit'));
			add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
		}
	}

	/**
	 * Add menu
	 */
	public function adminMenu(){
		if( $this->parent ){
			add_submenu_page($this->parent, $this->title, $this->menu_title, $this->caps, $this->slug, array($this, 'render'));
		}else{
			add_menu_page($this->title, $this->menu_title, $this->caps, $this->slug, array($this, 'render'), $this->icon, $this->position);
		}
	}

	/**
	 * Executed on admin_init
	 */
	abstract public function adminInit();

	/**
	 * Enqueue script
	 *
	 * @param string $admin_page
	 */
	public function adminEnqueueScripts($admin_page){
		if( false !== strpos($admin_page, $this->slug) ){
			$this->enqueueScript();
		}
	}

	/**
	 * Enqueue script
	 */
	protected function enqueueScript(){
		// Override this
	}

	/**
	 * Render admin screen
	 */
	public function render(){
		ob_start();
		$this->content();
		$template = ob_get_contents();
		ob_end_clean();
		echo <<<HTML
<div class="wrap">
	<h2><i class="dashicons {$this->icon}"></i> {$this->title}</h2>
	{$template}
</div>
HTML;
	}

	/**
	 * Render content
	 */
	protected function content(){
		$this->load($this->template);
	}

	/**
	 * Load template
	 *
	 * @param string $template
	 */
	protected function load($template){
		$template = get_stylesheet_directory()."/templates/admin/{$template}.php";
		/**
		 * wpametu_admin_screen_template
		 *
		 * @param string $template
		 * @param string $class_name
		 * @return string
		 */
		$template = apply_filters('wpametu_admin_screen_template', $template, get_called_class());
		if( file_exists($template) ){
			include $template;
		}
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch( $name ){
			case 'input':
				return Input::get_instance();
				break;
			case 'prg':
				return PostRedirectGet::get_instance();
				break;
			case 'base_url':
				if( $this->parent && preg_match('/(.*\.php)/', $this->parent, $match) ){
					return admin_url($match[1].'?page='.$this->slug);
				}else{
					return admin_url('admin.php?page='.$this->slug);
				}
				break;
			default:
				// Do nothing
				return null;
				break;
		}
	}
}