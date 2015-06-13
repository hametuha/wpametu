<?php

namespace WPametu\Assets;


use WPametu\File\Path;
use WPametu\Traits\i18n;
use WPametu\Pattern\Singleton;
use WPametu\Utility\StringHelper;


/**
 * Load assets library
 *
 * @package WPametu\Assets
 * @property-read StringHelper $str
 */
class Library extends Singleton
{

    use Path, i18n;

    /**
     * Library common version
     *
     * @const string
     */
    const COMMON_VERSION = '1.0';

    /**
     * Scripts to register
     *
     * @var array
     */
    private $scripts = [
        'wpametu-admin-helper' => ['/assets/js/dist/admin-helper.js', ['jquery-ui-dialog', 'jquery-ui-tooltip'], self::COMMON_VERSION, true],
        'wpametu-metabox' => ['/assets/js/dist/admin-metabox.js', ['wpametu-admin-helper', 'gmap', 'jquery-ui-timepicker-i18n'], self::COMMON_VERSION, true],
	    'wpametu-batch-helper' => ['/assets/js/dist/batch-helper.js', ['jquery-form', 'jquery-effects-highlight'], self::COMMON_VERSION, true],

        'chart-js' => ['/assets/js/lib/chartjs/Chart.min.js', null, '1.0.1', true],
        'imagesloaded' => ['/assets/js/lib/imagesloaded/imagesloaded.pkgd.min.js', null, '', true],
	    'jsrender' => ['/assets/js/lib/jsrender/jsrender.min.js',  ['jquery'], '1.0.0', true],
	    'jquery-ui-timepicker' => ['/assets/js/lib/jquery-timepicker-addon/jquery-ui-timepicker-addon.js', ['jquery-ui-datepicker-i18n', 'jquery-ui-slider'], '1.5.0', true],

        'gmap' => ['//maps.googleapis.com/maps/api/js', null, null, true],
	    'google-jsapi' => ['https://www.google.com/jsapi', null, null, true],
    ];

    /**
     * CSS to register
     *
     * @var array
     */
    private $css = [
        'wpametu-metabox' => ['/assets/css/admin-metabox.css', ['jquery-ui-timepicker'], self::COMMON_VERSION, 'screen'],
        'wpametu-batch-screen' => ['/assets/css/batch-screen.css', ['jquery-ui-mp6'], self::COMMON_VERSION, 'screen'],

        'jquery-ui-mp6' => ['/assets/css/jquery-ui.css', null, '1.10.3', 'screen'],
	    'jquery-ui-timepicker' => ['/assets/css/jquery-ui-timepicker-addon.css', ['jquery-ui-mp6'], '1.5.0', 'screen'],

        'font-awesome' => ['//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', [], '4.3.0', 'all'],
    ];

    /**
     * Show all registered assets
     *
     * @return array
     */
    public function show_assets(){
        return array(
	        'js' => $this->scripts,
	        'css' => $this->css,
        );
    }

    /**
     * Show all registered assets for debugging
     */
    public static function all_assets(){
        /** @var Library $instance */
        $instance = self::get_instance();
        return $instance->show_assets();
    }

    /**
     * Constructor
     *
     * @param array $setting
     */
    protected function __construct( array $setting = [] ){
        add_action('init', [$this, 'register_libraries'], 9);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
    }

    /**
     * Register assets
     */
    public function register_libraries(){
	    // Detect Locale
	    $locale = explode('_', get_locale());
	    if( count($locale) === 1 ){
		    $locale = strtolower($locale[0]);
	    }else{
		    $locale = strtolower($locale[0]).'-'.strtoupper($locale[1]);
	    }
	    $this->scripts['jquery-ui-datepicker-i18n'] = [
		    '/assets/js/lib/jquery-ui/ui/i18n/datepicker-'.$locale.'.js',
		    ['jquery-ui-datepicker'],
		    '1.9.1',
		    true
	    ];
	    $this->scripts['jquery-ui-timepicker-i18n'] = [
		    '/assets/js/lib/jquery-timepicker-addon/i18n/jquery-ui-timepicker-'.$locale.'.js',
		    ['jquery-ui-timepicker'],
		    '1.5.0',
		    true
	    ];

        // Register all scripts
        foreach( $this->scripts as $handle => list($src, $deps, $version, $footer) ){
            $src = $this->build_src($src);
            // Google map
            if( 'gmap' == $handle ){
                $args = ['sensor' => 'true'];
                if( defined('WPAMETU_GMAP_KEY') ){
                    $args['key'] = \WPAMETU_GMAP_KEY;
                }
                $src = add_query_arg($args, $src);
            }
            wp_register_script($handle, $src, $deps, $version, $footer);
            $localized = $this->localize($handle);
            if( !empty($localized) ){
                wp_localize_script($handle, $this->str->hyphen_to_camel($handle), $localized);
            }
        }
        // Register all css
        foreach( $this->css as $handle => list($src, $deps, $version, $media) ){
            $src = $this->build_src($src);
            wp_register_style($handle, $src, $deps, $version, $media);
        }
    }

    /**
     * Build URL for library
     *
     * @param string $src
     * @return string
     */
    private function build_src($src){
        if( !preg_match('/^(https?:)?\/\//u', $src) ){
            // O.K. This is a library in WPametu!
            $src = trailingslashit($this->get_root_uri()).ltrim($src, '/');
        }
        return $src;
    }

    /**
     * Make localized script
     *
     * @param string $handle
     * @return array
     */
    private function localize($handle){
        switch( $handle ){
            case 'wpametu-admin-helper':
                return [
                    'error' => $this->__('Error'),
                    'close' => $this->__('Close'),
                ];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Load assets on admin screen
     *
     * @param string $page
     */
    public function admin_assets( $page = '' ){
        wp_enqueue_script('wpametu-admin-helper');
        wp_enqueue_style('jquery-ui-mp6');
    }

    /**
     * Getter
     *
     * @param string $name
     * @return Singleton
     */
    public function __get($name){
        switch( $name ){
            case 'str':
                return StringHelper::get_instance();
                break;
            default:
                // Do nothing
                break;
        }
    }
}
