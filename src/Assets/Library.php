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
        'wpametu-admin-helper' => ['/assets/js/admin-helper.js', ['jquery-ui-dialog'], self::COMMON_VERSION, true, '.min'],
        'wpametu-metabox' => ['/assets/js/admin-metabox.js', ['wpametu-admin-helper', 'gmap'], self::COMMON_VERSION, true, '.min'],
    ];

    /**
     * CSS to register
     *
     * @var array
     */
    private $css = [
        'jquery-ui-mp6' => ['/vendor/jquery-ui-mp6/src/css/jquery-ui.css', null, '1.10.3', 'screen', false],
        'wpametu-metabox' => ['/assets/css/admin-metabox.css', ['jquery-ui-mp6'], self::COMMON_VERSION, 'screen', false],
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
    }

    /**
     * Register assets
     */
    public function register_libraries(){
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
        if( !preg_match('/^(https?)?\/\//u', $src) ){
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
