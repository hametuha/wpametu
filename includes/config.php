<?php

namespace WPametu;

use Wpametu\Traits;

/**
 * Class Config
 *
 * @package WPametu
 */
final class Config extends Pattern\Singleton
{

	use Traits\URL;

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( array $arguments = array()){
		// Register scripts
		add_action('init', array($this, 'register_scripts'));
		// Global Script Vars
		add_action('wp_head', array($this, 'register_global_js'), 1);
		add_action('admin_enqueue_scripts', array($this, 'register_global_js'), 1);
		// Kill WPMP's CSS
		add_action('admin_enqueue_scripts', function(){
			wp_dequeue_style('wpmp-admin-custom');
		}, 10000);
	}

	/**
	 * Register library scripts
	 *
	 * @return void
	 */
	public function register_scripts(){
		// jQuery token input
		wp_register_script('jquery-token-input', $this->get_lib_js('jquery.tokeninput.js'), array('jquery'), '1.6.1', true);
		wp_register_style('jquery-token-input', $this->lib_url('css/token-input.css'), array(), '1.6.1');
		wp_register_style('jquery-token-input-facebook', $this->lib_url('css/token-input-facebook.css'), array(), '1.6.1');
		// Font Awesome
		wp_register_style('font-awesome', $this->lib_url('css/font-awesome/font-awesome.css'), array(), '4.0.3');
		// Date time picker
		wp_register_script('jquery-ui-timepicker', $this->get_lib_js('jquery.timepicker.js'), array('jquery-ui-datepicker', 'jquery-ui-slider'), '1.4.1', true);
	}



	/**
	 * Register global Object for WPametu
	 */
	public function register_global_js(){
		?>
<script type="text/javascript">
//<![CDATA[
	WPametu = window.WPametu || {};
	WPametu.Vars = {};
//]]>
</script>
<?php
	}
} 