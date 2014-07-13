<?php

namespace WPametu\API;


use WPametu\API\Ajax\AjaxBase;
use WPametu\API\Rest\RestBase;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Traits\Reflection;

/**
 * Rewrite rule manager
 *
 * @package WPametu\API
 * @property-read string $api_class
 * @property-read string $api_vars
 * @property-read bool|string $config
 * @property-read int $last_updated
 */
final class Rewrite extends Singleton
{

    use Path, i18n, Reflection;

    /**
     * Option name of WPametu's rewrite rules
     *
     * @var string
     */
    private $option_name = 'wpametu_rewrite_last_updated';

    /**
     * Query vars name for method name
     *
     * @var string
     */
    private $api_query_name = 'api_class';

    /**
     * Query vars name for method arguments
     *
     * @var string
     */
    private $vars_query_name = 'api_vars';

    /**
     * Constructor
     *
     * @param array $setting
     */
    public function __construct( array $setting = [] ){
        add_filter('query_vars', [$this, 'query_vars']);
        add_action('admin_init', [$this, 'admin_init']);
        add_filter('rewrite_rules_array', [$this, 'rewrite_rules_array']);
        add_action('pre_get_posts', [$this, 'pre_get_posts']);
    }

    /**
     * Filter query vars
     *
     * @param array $vars
     * @return array
     */
    public function query_vars($vars){
        $vars[] = $this->api_class;
        $vars[] = $this->api_vars;
        return $vars;
    }

    /**
     * Add rewrite rules.
     *
     * @param array $rules
     * @return array
     */
    public function rewrite_rules_array( array $rules ){
        if( $this->config ){
            include $this->config;
            // Normal rewrite rules
            if( isset($rewrites) && is_array($rewrites) ){
                $rules = array_merge($rewrites, $rules);
            }
            // API Rewrite rules
            if( isset($api_rewrites) && is_array($api_rewrites) ){
                $new_rewrite = [];
                foreach( $api_rewrites as $rewrite => $class_name ){
                    $new_rewrite[$rewrite] = "index.php?{$this->api_class}={$class_name}&{$this->api_vars}=\$matches[1]";
                }
                $rules = array_merge($new_rewrite, $rules);
            }
        }
        return $rules;
    }

    /**
     * Parse request and invoke REST class if possible
     *
     * @param \WP_Query $wp_query
     */
    public function pre_get_posts( \WP_Query &$wp_query ){
        if( !is_admin() && $wp_query->is_main_query() && ($api_class = $wp_query->get($this->api_class)) ){
            // Detect class is valid
            try{
                // Fix escaped namespace delimiter
                $api_class = str_replace('\\\\', '\\', $api_class);
                // Check class existence
                if( !$this->is_valid_class($api_class) ){
                    throw new \Exception($this->__('Specified URL is invalid.'), 404);
                }
                /** @var RestBase $instance */
                $instance = $api_class::get_instance();
                $instance->parse_request($wp_query->get($this->api_vars), $wp_query);
            }catch ( \Exception $e ){
                if( 404 == $e->getCode() ){
                    $wp_query->set_404();
                }else{
                    wp_die($e->getMessage(), get_status_header_desc($e->getCode()), [
                        'response' => $e->getCode(),
                        'back_link' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Detect if class name is valid
     *
     * @param string $class_name
     * @return bool
     */
    private function is_valid_class($class_name){
        return class_exists($class_name)
               &&
               $this->is_sub_class_of($class_name, RestBase::class);
    }

    /**
     * Update rewrite rules if possible
     */
    public function admin_init(){
        if( !AjaxBase::is_ajax() && current_user_can('manage_options') ){
            if( $this->config ){
                $last_updated = filemtime($this->config);
                if( get_option('rewrite_rules') && $last_updated && $this->last_updated < $last_updated ){
                    flush_rewrite_rules();
                    update_option($this->option_name, $last_updated);
                    $message = sprintf($this->__('Rewrite rules updated. Last modified date is %s'), date_i18n(get_option('date_format').' '.get_option('time_format'), $last_updated));
                    add_action('admin_notices', function() use ($message){
                        printf('<div class="updated"><p>%s</p></div>', $message);
                    });
                }
            }
        }
    }

    /**
     * Getter
     *
     * @param $name
     * @return mixed|void
     */
    public function __get($name){
        switch($name){
            case 'api_class':
                /**
                 * wpametu_api_query_name
                 *
                 * Filter query_vars name for api class detect
                 *
                 * @filter
                 * @param string $api_query_name
                 * @return string
                 */
                return apply_filters('wpametu_api_query_name', $this->api_query_name);
                break;
            case 'api_vars':
                /**
                 * wpametu_vars_query_name
                 *
                 * Filter query_vars name for api class detect
                 *
                 * @filter
                 * @param string $api_query_name
                 * @return string
                 */
                return apply_filters('wpametu_vars_query_name', $this->vars_query_name);
                break;
            case 'config':
                $config = $this->get_config_dir().'/rewrite.php';
                if( file_exists($config) ){
                    return $config;
                }else{
                    return false;
                }
                break;
            case 'last_updated':
                return (int)get_option($this->option_name, false);
                break;
            default:
                // Do nothing
                break;
        }
    }
}
