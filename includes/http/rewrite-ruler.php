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
        // TODO: Implement __construct() method.
        add_action('init', [$this, 'checkRewrite'], 11);
        add_filter('query_vars', [$this, 'filterQueryVars']);
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
            if( false === strpos($regexp, $class_name) ){
                $regexp .= '&rewrite_class='.$this->urlize($class_name);
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
        $reflexion = new \ReflectionClass($class_name);
        if( $reflexion->isAbstract() || !$reflexion->isSubclassOf('\\WPametu\\Controllers\\RewriteController') ){
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
}