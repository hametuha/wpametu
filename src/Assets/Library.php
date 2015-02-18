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
        'chart-js' => ['/vendor/Chart.js/Chart.js', null, '1.0.1', true, '.min'],
        'gmap' => ['//maps.googleapis.com/maps/api/js', null, null, true, false],
	    'google-jsapi' => ['https://www.google.com/jsapi', null, null, true, false],
        'wpametu-admin-helper' => ['/assets/js/admin-helper.js', ['jquery-ui-dialog', 'jquery-ui-tooltip'], self::COMMON_VERSION, true, '.min'],
        'wpametu-metabox' => ['/assets/js/admin-metabox.js', ['wpametu-admin-helper', 'gmap', 'jquery-ui-timepicker-i18n'], self::COMMON_VERSION, true, '.min'],
	    'wpametu-batch-helper' => ['/assets/js/batch-helper.js', ['jquery-form', 'jquery-effects-highlight'], self::COMMON_VERSION, true, '.min'],
        'imagesloaded' => ['/vendor/imagesloaded/imagesloaded.pkgd.min.js', null, '', true, false],
	    'jquery-ui-timepicker' => ['/vendor/jquery-timepicker-addon/dist/jquery-ui-timepicker-addon.js', ['jquery-ui-datepicker-i18n', 'jquery-ui-slider'], '1.5.0', true, '.min'],
	    'jsrender' => ['/vendor/jsrender/jsrender.js',  ['jquery'], '1.0.0', true, '.min'],
    ];

    /**
     * CSS to register
     *
     * @var array
     */
    private $css = [
        'jquery-ui-mp6' => ['/vendor/jquery-ui-mp6/src/css/jquery-ui.css', null, '1.10.3', 'screen', false],
	    'jquery-ui-timepicker' => ['/vendor/jquery-timepicker-addon/dist/jquery-ui-timepicker-addon.css', ['jquery-ui-mp6'], '1.5.0', 'screen', '.min'],
        'wpametu-metabox' => ['/assets/css/admin-metabox.css', ['jquery-ui-mp6', 'jquery-ui-timepicker'], self::COMMON_VERSION, 'screen', false],
        'wpametu-batch-screen' => ['/assets/css/batch-screen.css', ['jquery-ui-mp6'], self::COMMON_VERSION, 'screen', false],
    ];

    /**
     * Show all registered assets
     */
    public function show_assets(){
        var_dump($this->scripts, $this->css);
        exit;
    }

    /**
     * Show all registered assets for debugging
     */
    public static function all_assets(){
        /** @var Library $instance */
        $instance = self::get_instance();
        $instance->show_assets();
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
		    '/vendor/jquery-ui-i18n/datepicker-'.$locale.'.js',
		    ['jquery-ui-datepicker'],
		    '1.9.1',
		    true,
		    false
	    ];
	    $this->scripts['jquery-ui-timepicker-i18n'] = [
		    '/vendor/jquery-timepicker-addon/dist/i18n/jquery-ui-timepicker-'.$locale.'.js', ['jquery-ui-timepicker'],
		    '1.5.0',
		    true,
		    false
	    ];

        // Register all scripts
        foreach($this->scripts as $handle => list($src, $deps, $version, $footer, $ext) ){
            $src = $this->build_src($src);
            if( $ext ){
                $src = $this->add_extension($src, $ext);
            }
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
        foreach( $this->css as $handle => list($src, $deps, $version, $media, $ext) ){
            $src = $this->build_src($src);
            if( $ext ){
                $src = $this->add_extension($src, $ext);
            }
            wp_register_style($handle, $src, $deps, $version, $media);
        }
    }

    /**
     * Add min extension if not debug mode
     *
     * @param string $src
     * @param string $ext
     * @return string
     */
    private function add_extension($src, $ext){
        if( WP_DEBUG ){
            $src = preg_replace('/\A(.*)(\.js|\.css)\z/u', '$1'.$ext.'$2', $src);
        }
        return $src;
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
            $src = $this->get_root_uri().$src;
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
