<?php

namespace WPametu\UI;


use WPametu\Http\PostRedirectGet;
use WPametu\Pattern\Singleton;
use WPametu\Traits\i18n;
use WPametu\Traits\Reflection;
use WPametu\Http\Input;
use WPametu\Utility\StringHelper;
use WPametu\Utility\IteratorWalker;

/**
 * Class MetaBox
 *
 * @package WPametu\Admin
 * @property-read string $nonce
 * @property-read Input $input
 * @property-read StringHelper $str
 * @property-read PostRedirectGet $prg
 * @property-read IteratorWalker $walker
 */
abstract class MetaBox extends Singleton
{

    use Reflection, i18n;

    /**
     * Meta box name
     *
     * Must start with underscore.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Meta box label
     *
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $capability = 'edit_posts';

    /**
     * Meta box fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Array of post types
     *
     * @var array
     */
    protected $post_types = [];

    /**
     * Constructor
     *
     * @param array $setting
     */
    protected function __construct( array $setting = [] ){
        $this->register_save_action();
        $this->register_ui();
    }

    abstract protected function register_save_action();

    abstract protected function register_ui();

    /**
     * Detect if nonce is valid
     *
     * @return bool
     */
    protected function verify_nonce(){
        return wp_verify_nonce($this->input->post($this->nonce), $this->nonce);
    }

    /**
     * Echo nonce field
     */
    protected function nonce_field(){
        wp_nonce_field($this->nonce, $this->nonce, false);
    }

    /**
     * This meta fields description
     */
    protected function desc(){
        return '';
    }

    /**
     * Render meta box content
     *
     * @param \WP_Post $post
     */
    public function render( \WP_Post $post ){
        $this->nonce_field();
        $this->desc();
        echo '<table class="table form-table">';
        foreach( $this->loop_fields() as $field ){
            if( !is_wp_error($field) ){
                /** @var \WPametu\UI\Field\Base $field */
                $field->render($post);
            }else{
                /** @var \WP_Error $field */
                printf('<div class="error"><p>%s</p></div>', $field->get_error_message());
            }
        }
        echo '</table>';
    }

    /**
     * Generator
     *
     * @return \Generator
     */
    protected function loop_fields(){
        foreach( $this->fields as $name => $args ){
            $return = null;
            if( isset($args['class']) && class_exists($args['class']) ){
                $class_name = $args['class'];
                unset($args['class']);
                $args['name'] = $name;
                try{
                    $return = new $class_name($args);
                }catch ( \Exception $e ){
                    $return = new \WP_Error($e->getCode(), $e->getMessage());
                }
            }else{
                $return = new \WP_Error(500, sprintf($this->__('%s\'s argument setting is invalid.'), $name));
            }
            yield $return;
        }
    }

    /**
     * Detect if current user has capability
     *
     * @return bool
     */
    protected function has_cap(){
        return current_user_can($this->capability);
    }


    /**
     * Detect if post type is valid
     *
     * @param string $post_type
     * @return bool
     */
    protected function is_valid_post_type($post_type = ''){
        return false !== array_search($post_type, $this->post_types);
    }

    /**
     * Getter
     *
     * @param string $name
     * @return string
     */
    public function __get($name){
        switch( $name ){
            case 'nonce':
                return $this->name.'_nonce';
                break;
            case 'input':
                return Input::get_instance();
                break;
            case 'str':
                return StringHelper::get_instance();
                break;
            case 'prg':
                return PostRedirectGet::get_instance();
                break;
            case 'walker':
                return IteratorWalker::get_instance();
                break;
            default:
                // Do nothing.
                break;
        }
    }
}
