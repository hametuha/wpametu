<?php

namespace WPametu;

/**
 * Script manager
 *
 * @package WPametu
 * @author Takahashi Fumiki
 */
class Script extends Pattern\Singleton
{

    use Traits\Util;

    /**
     * jQuery-Token-Input
     */
    const JQUERY_TOKEN_INPUT = 'jquery-token-input';

    /**
     * Timepicker
     */
    const JQUERY_UI_TIMEPICKER = 'jquery-ui-timepicker';

    /**
     * ChartJS
     */
    const CHART_JS = 'chart-js';

    /**
     * TinyColor
     */
    const TINYCOLOR = 'tinycolor';

    /**
     * JS API for Google
     */
    const GOOGLE_JSAPI = 'google-jsapi';

    /**
     * Google Map
     */
    const GOOGLE_MAP = 'google-map';

    /**
     * Media selector
     */
    const MEDIA_SELECTOR = 'wp-media-selector';

    /**
     * Constructor
     *
     * @param array $arguments
     */
    protected function __construct( array $arguments = []){
        // Register Script
        add_action('init', [$this, 'registerScripts']);
        // Global Script Vars
        add_action('wp_head', [$this, 'registerGlobalJs'], 1);
        add_action('admin_enqueue_scripts', [$this, 'registerGlobalJs'], 1);
    }

    /**
     * Register library scripts
     *
     * @return void
     */
    public function registerScripts(){
        // jQuery token input
        wp_register_script(self::JQUERY_TOKEN_INPUT, $this->url->getLibJs('jquery.tokeninput.js'), ['jquery'], '1.6.1', true);
        // Date time picker
        wp_register_script(self::JQUERY_UI_TIMEPICKER, $this->url->getLibJs('jquery.timepicker.js'), ['jquery-ui-datepicker', 'jquery-ui-slider'], '1.4.1', true);
        // ChartJS
        wp_register_script(self::CHART_JS, $this->url->getMinifiedFile($this->url->libUrl('vendor/chartjs/Chart.js')), ['jquery'], '0.2', true);
        // tinycolor
        wp_register_script(self::TINYCOLOR, $this->url->libUrl('vendor/tinycolor/tinycolor.js'), [], '0.9.16', true);
        // Google JSAPI
        wp_register_script(self::GOOGLE_JSAPI, 'https://www.google.com/jsapi', [], null, false);
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
        wp_register_script(self::GOOGLE_MAP, $src, [], null, true);
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

} 