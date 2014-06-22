<?php

namespace WPametu\UI\Admin\Metabox;

use WPametu\Pattern, WPametu\Traits;

/**
 * Class MetaBox
 *
 * @package WPametu\UI\Admin
 * @author Takahashi Fumiki
 * @property-read string $name
 * @property-read string $nonce
 * @property-read string $nonce_key
 * @property-read string $id
 */
abstract class Base extends Pattern\Singleton
{


    use Traits\Util, Traits\i18n{
        Traits\Util::__get as traitGet;
    }

    /**
     * Initialized counter
     *
     * @var int
     */
    private static $counter = 0;

    /**
     * Title of meta box
     *
     * @var string
     */
    protected $title = '';

    /**
     * Post types to enable
     *
     * @var array
     */
    protected $post_types = [];

    /**
     * Context to display
     *
     * @var string 'normal', 'advanced', 'side'
     */
    protected $context = 'normal';

    /**
     * Priority
     *
     * @var string 'high', 'low', 'default', 'core'
     */
    protected $priority = 'default';

    /**
     * Constructor
     *
     * @param array $argument
     */
    protected function __construct( array $argument){
        self::$counter++;
        if( self::$counter == 1 ){
            add_action('admin_enqueue_scripts', [get_called_class(), 'registerStyle']);
        }
        $this->setTitle();
        if( empty($this->title) ){
            $this->title = sprintf($this->__('追加情報 %d'), self::$counter);
        }
        add_action('add_meta_boxes', [$this, 'registerMetaBoxes'], 10, 2);
    }

    /**
     * Override title
     *
     * <code>
     * $this->title = \WPametu\UI\Parts::icon('checked', false).' ';
     * </code>
     */
    protected function setTitle(){

    }


    /**
     * Register css and scripts
     */
    public static function registerStyle(){
        wp_enqueue_style(\WPametu\Css::METABOX);
        wp_enqueue_script(\WPametu\Script::METABOX_HELPER);
        $class_name = get_called_class();
        /** @var \WPametu\UI\Admin\Metabox\Multiple $instance */
        $instance = $class_name::getInstance();
        wp_localize_script(\WPametu\Script::METABOX_HELPER, 'MetaboxHelper', array(
            'required' => $instance->__('必須項目です'),
        ));
    }

    /**
     * Register meta box callback
     *
     * @param $post_type
     * @param $post
     */
    public function registerMetaBoxes($post_type, $post){
        if( $this->shouldRegister($post) ){
            add_meta_box($this->id, $this->title, [$this, 'doMetaBox'], $post_type, $this->context, $this->priority);
        }
    }

    /**
     * Output metabox
     *
     * @param \WP_Post $post
     * @param array $screen
     * @return void
     */
    abstract public function doMetaBox( \WP_Post $post, array $screen);

    /**
     * If meta box should be register
     *
     * Override this function to change display condition
     *
     * @param \WP_Post $post
     * @return bool
     */
    protected function shouldRegister($post){
        return false !== array_search($post->post_type, $this->post_types);
    }

    /**
     * Output nonce field
     */
    protected function nonceField(){
        wp_nonce_field($this->nonce, $this->nonce_key, false);
    }

    /**
     * Detect nonce is OK
     *
     * @return bool
     */
    protected function verifyNonce(){
        $nonce = $this->input->request($this->nonce_key);
        return !empty($nonce) && wp_verify_nonce($nonce, $this->nonce);
    }

    /**
     * Getter
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'name';
                return $this->str->classNameToHungarian(get_called_class());
                break;
            case 'id':
                return $this->str->classNameToHyphen(get_called_class());
                break;
            case 'nonce_key':
                return '_'.$this->name;
                break;
            case 'nonce':
                return $this->name.'_'.get_current_user_id();
                break;
            default:
                return $this->traitGet($name);
                break;
        }
    }
}