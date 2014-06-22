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

    use Traits\Util, Traits\i18n;

    /**
     * jQuery-Token-Input
     */
    const JQUERY_TOKEN_INPUT = 'jquery-token-input';

    /**
     * jQuery UI Datepicker's string
     */
    const JQUERY_UI_DATEPICKER_STRING = 'jquery-datepicker-string';

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
     * Metabox helper
     */
    const METABOX_HELPER = 'wpametu-metabox-helper';

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
        // jQuery Datepickers string
        $day_strings = [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), ];
        wp_register_script(self::JQUERY_UI_DATEPICKER_STRING, $this->url->getLibJs('jquery.datepicker.string.js'), [], VERSION, true);
        wp_localize_script(self::JQUERY_UI_DATEPICKER_STRING, 'jQueryDatePickerString', [
            'buttonText' => __('Choose'),
            'closeText' => __('Close'),
            'currentText' => $this->__('現在'),
            'dayNames' => [__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), ],
            'dayNamesMin' => array_map(function($day){
                return mb_strlen($day) > 2 ? _mb_substr($day, 0, 2) : $day;
            }, $day_strings),
            'dayNamesShort' => $day_strings,
            'monthNames' => [__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'), ],
            'monthNamesShort' => array_map(function ($month){
                return $this->str->monthName($month, 'M');
            }, range(1, 12)),
            'nextText' => $this->__('次へ'),
            'prevText' => $this->__('前へ'),
            'showMonthAfterYear' => (false !== array_search(get_locale(), ['ja', 'zh_CN', 'fa_IR', 'ko_KR'])), // Iran, China, Japan, Korea use year before
            'timeOnlyTitle' => '時間を選択',
            'timeText' => '時間',
            'hourText' => '時',
            'minuteText' => '分',
            'secondText' => '秒',
            'yearSuffix' => (false !== array_search(get_locale(), ['ja', 'zh_CN'])) ? '年' : '',
        ]);
        // Date time picker
        wp_register_script(self::JQUERY_UI_TIMEPICKER, $this->url->getLibJs('jquery.timepicker.js'), ['jquery-ui-datepicker', 'jquery-ui-slider', self::JQUERY_UI_DATEPICKER_STRING], '1.4.1', true);
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
        // Media Selector
        wp_register_script(self::MEDIA_SELECTOR, $this->url->getLibJs('media-selector.js'), ['jquery'], VERSION, true);
        // Metabox helper
        wp_register_script(self::METABOX_HELPER, $this->url->getLibJs('metabox-helper.js'), [self::MEDIA_SELECTOR, self::JQUERY_UI_TIMEPICKER, 'jquery-effects-highlight'], VERSION, true);
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