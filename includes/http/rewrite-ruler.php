<?php

namespace WPametu\HTTP;

use WPametu\Traits, WPametu\Pattern;

/**
 * Rewriter Rule manager
 *
 * @package WPametu\HTTP
 * @author Takahashi Fumiki
 */
class RewriteRuler extends Pattern\Singleton
{
    use Traits\Util, Traits\i18n;


    /**
     * Rewrite prefixes to highjack
     * @var array
     */
    private $rewrites = [];

    /**
     * Additional query vars
     * @var array
     */
    private $query_vars = ['rewrite_class', 'class_method'];

    /**
     * Constructor
     *
     * Constructor should not be public.
     *
     * @param array $argument
     */
    protected function __construct(array $argument = []){
        add_action('init', [$this, 'checkRewrite'], 11);
        add_filter('query_vars', [$this, 'filterQueryVars']);
        add_filter('rewrite_rules_array', [$this, 'filterRewriteRules']);
        add_action('admin_notices', [$this, 'adminNotices']);
        add_action('pre_get_posts', [$this, 'preGetPosts']);
    }

    /**
     * Check rewrite rule and update if neccessary
     *
     * @todo Concerning performance issue
     */
    public function checkRewrite(){
        $rewrites = $this->buildRewrite();
        $registered_rewrite = get_option('rewrite_rules');
        if( is_array($registered_rewrite) && !empty($rewrites) ){
            foreach($rewrites as $rewrite => $regexp){
                if( !isset($registered_rewrite[$rewrite]) || $regexp != $registered_rewrite[$rewrite] ){
                    flush_rewrite_rules();
                    break;
                }
            }
        }
    }

    /**
     * Rewrite rule filter
     *
     * @param array $rules
     * @return array
     */
    public function filterRewriteRules($rules){
        $rewrites = $this->buildRewrite();
        if( !empty($rewrites) ){
            foreach($rewrites as $rewrite => $regexp){
                if( !isset($rules[$rewrite]) ){
                    $rules = array_merge([
                        $rewrite => $regexp,
                    ], $rules);
                }
            }
        }
        return $rules;
    }


    /**
     * Returns rewrite rules
     *
     * @return array
     */
    private function buildRewrite(){
        $rewrite_rules = array();
        foreach($this->rewrites as $data){
            /** @var string $rewrite */
            /** @var string $regexp */
            /** @var string $class_name */
            extract($data);
            if( !empty($class_name) && false === strpos($regexp, ($url_class_name = $this->urlize($class_name))) ){
                $regexp .= '&rewrite_class='.$url_class_name;
            }
            $rewrite_rules[$rewrite] = $regexp;
        }
        return $rewrite_rules;
    }

    /**
     * Change class name to URL ready string
     *
     * @param string $class_name
     * @return string
     */
    private function urlize($class_name){
        return implode('/', array_map([$this->str, 'camelToHyphen'], explode('\\', ltrim($class_name, '\\'))));
    }

    /**
     * Add additional query vars
     *
     * @param array $vars
     * @return array
     */
    public function filterQueryVars($vars){
        foreach( $this->query_vars as $var){
            $vars[] = $var;
        }
        return $vars;
    }

    /**
     * Indicates permalink setting error
     */
    public function adminNotices(){
        if( current_user_can('manage_options') && !get_option('rewrite_rules') ){
            printf('<div class="error"><p>%s</p></div>', sprintf(
                $this->__('パーマリンク設定が有効になっていません。<a href="%s">設定画面</a>より有効にしてください。'),
                admin_url('options-permalink.php')));
        }
    }

    /**
     * Register rewrite rules for RewriterController
     *
     * @param string $rewrite
     * @param string $regexp
     * @param string $class_name
     * @throws \Exception
     */
    public function registerRewrite($rewrite, $regexp, $class_name){
        if( !class_exists($class_name) ){
            throw new \Exception(sprintf($this->__('クラス%sを見つけられません。'), $class_name));
        }
        if( !$this->isValidClass($class_name) ){
            throw new \Exception(sprintf($this->__('クラス%sはRewriteControllerのサブクラスでなくてはなりません。'), $class_name));
        }
        $this->rewrites[] = [
            'rewrite' => $rewrite,
            'regexp' => $regexp,
            'class_name' => $class_name,
        ];
    }

    /**
     * Add query vars to parse
     *
     * @param array $vars
     */
    public function addQueryVars( array $vars){
        foreach($vars as $var){
            if(false === array_search($var, $this->query_vars)){
                $this->query_vars[] = $var;
            }
        }
    }

    /**
     * Check class exists and call public method
     *
     * @param \WP_Query $wp_query
     */
    public function preGetPosts( \WP_Query &$wp_query){
        if( !is_admin() && $wp_query->is_main_query() ){
            $class_name = $wp_query->get('rewrite_class');
            if( $class_name ){
                $str = $this->str;
                $class_name = implode('\\', array_map(function ($path) use ($str){
                    return $str->hyphenToCamel($path, true);
                }, explode('/', $class_name)));
                if( $this->isValidClass($class_name) ){
                    $class_name::getInstance()->parseRequest($wp_query);
                }
            }
        }
    }

    /**
     * Detect specified class is valid
     *
     * @param string $class_name
     * @return bool
     */
    private function isValidClass($class_name){
        $reflexion = new \ReflectionClass($class_name);
        return !$reflexion->isAbstract() && $reflexion->isSubclassOf('\\WPametu\\Controllers\\RewriteController');
    }
}