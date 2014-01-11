<?php

namespace WPametu;

use WPametu\Traits;

/**
 * Class Config
 *
 * @package WPametu
 */
final class Config extends Pattern\Singleton
{

	use Traits\Util;

	private $helpers = [];

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( array $arguments = array()){
		// Register scripts
		add_action('init', [$this, 'registerScripts']);
		// Global Script Vars
		add_action('wp_head', [$this, 'registerGlobalJs'], 1);
		add_action('admin_enqueue_scripts', [$this, 'registerGlobalJs'], 1);
		// Load functions
		$this->autoLoad();
	}

	/**
	 * Register library scripts
	 *
	 * @return void
	 */
	public function registerScripts(){
		// jQuery token input
		wp_register_script('jquery-token-input', $this->url->get_lib_js('jquery.tokeninput.js'), ['jquery'], '1.6.1', true);
		wp_register_style('jquery-token-input', $this->url->lib_url('css/token-input.css'), [], '1.6.1');
		wp_register_style('jquery-token-input-facebook', $this->url->lib_url('css/token-input-facebook.css'), [], '1.6.1');
		// Font Awesome
		wp_register_style('font-awesome', $this->url->lib_url('vendor/font-awesome/css/font-awesome/font-awesome.min.css'), [], '4.0.3');
		// Date time picker
		wp_register_script('jquery-ui-timepicker', $this->url->get_lib_js('jquery.timepicker.js'), ['jquery-ui-datepicker', 'jquery-ui-slider'], '1.4.1', true);
        // ChartJS
        wp_register_script('chart-js', $this->url->get_minified_js($this->url->lib_url('vendor/chartjs/Chart.js')), ['jquery'], '0.2', true);
        // tinycolor
        wp_register_script('tinycolor', $this->url->lib_url('vendor/tinycolor/tinycolor.js'), [], '0.9.16', true);
        // Google JSAPI
        wp_register_script('google-jsapi', 'https://www.google.com/jsapi', [], null, false);
        // Google Map
        /**
         * Filter api token
         *
         * If you want to specify API Key for Google Map,
         * add filter to 'gmap_api_key'
         *
         * @param string
         */
        $api_token = apply_filters('gmap_api_key', '');
        $src = 'https://maps.googleapis.com/maps/api/js?'.( empty($api_token) ? '' : 'key='.$api_token.'&').'sensor=true';
        wp_register_script('google-map', $src, [], null, true);
	}



	/**
	 * Register global Object for WPametu
	 */
	public function registerGlobalJs(){
		?>
<script type="text/javascript">
//<![CDATA[
	WPametu = window.WPametu || {};
	WPametu.Vars = {};
//]]>
</script>
<?php
	}

	/**
	 * Autoloader required class
	 *
	 * This autoload class will be called
	 *
	 * @uses \Spyc::YAMLLoad to parse setting.yaml
	 */
	private function autoLoad(){
		// Autoloader
		$config = \Spyc::YAMLLoad(BASE_DIR.'/setting.yaml');
		if( isset($config['autoloads']) && is_array($config['autoloads']) ){
			foreach($config['autoloads'] as $class_name){
				if( method_exists($class_name, 'getInstance') ){
					call_user_func([$class_name, 'getInstance']);
				}
			}
		}
	}
} 