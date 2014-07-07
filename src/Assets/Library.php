<?php

namespace WPametu\Assets;


use WPametu\File\Path;
use WPametu\Pattern\Singleton;


class Library extends Singleton
{

    use Path;

    private $scripts = [
        'chart-js' => ['/vendor/Chart.js/Chart.js', null, '1.0.1', true, '.min'],
        'gmap' => ['//maps.googleapis.com/maps/api/js', null, null, true, false],
    ];

    private $css = [

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
        add_action('init', [$this, 'register_libraries']);
    }

    /**
     * Register assets
     */
    public function register_libraries(){
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
        if( !WP_DEBUG ){
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
}
