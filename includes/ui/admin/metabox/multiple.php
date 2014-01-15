<?php

namespace WPametu\UI\Admin\Metabox;


abstract class Multiple extends Base
{

    /**
     * Default field
     *
     * @var array
     */
    private static $default_field = [
        'name' => '',
		'label' => '',
        'description' => '',
        'default' => '',
        'required' => false,
        'type' => 'text',
        'sub_type' => 'text',
        'rows' => 3,
        'min_length' => 0,
        'max_length' => 0,
        'max' => 0,
        'min' => 0,
        'follow' => '',
        'precede' => '',
        'values' => [],
        'icon_class' => '',
        'taxonomy' => '',
        'multiple' => false,
        'show_empty' => false,
        'prefix' => '',
        'suffix' => '',
    ];

    /**
     * Field to store
     * @var array
     */
    private $fields = [];

    /**
     * Constructor
     *
     * @param array $argument
     */
    protected function __construct( array $argument = []){
        parent::__construct($argument);
        add_action('save_post', [$this, 'callSave'], 10, 2);
    }

    /**
     * Call save action
     *
     * Checking nonce and post type,
     * call $this->save.
     *
     * @param $post_id
     * @param \WP_Post $post
     */
    final public function callSave($post_id, \WP_Post $post){
        if( $this->shouldSave($post) && $this->verifyNonce() ){
            $this->registerFields($post);
            $this->save($post);
        }
    }

    /**
     * Save field data
     *
     * To customize save action,
     * override this method
     *
     * @param \WP_Post $post
     */
    protected function save( \WP_Post $post ){
        foreach($this->fields as $field){
            $class_name = $this->getClass($field);
            if( $class_name ){
                call_user_func_array([$class_name, 'save'], array($post, $field));
            }
        }
    }

    /**
     * Add field to save
     *
     * @param array $field
     */
    protected function addField( array $field ){
        $this->fields[] = wp_parse_args($field, self::$default_field);
    }

    /**
     * Bulk register
     *
     * @see \WPametu\UI\Admin\Metabox\Multiple::addField
     * @param array $fields
     */
    protected function addFields( array $fields){
        foreach($fields as $field){
            $this->addField($field);
        }
    }

    /**
     * Register fields.
     *
     * Use $this->addField.
     * Default field is self::$default_fields
     *
     * <code>
     * $this->addField(['name' = 'var']);
     * </code>
     *
     * @see \WPametu\UI\Admin\Metabox\Multiple::$default_fields
     * @param \WP_Post $post
     * @return mixed
     */
    abstract protected function registerFields( \WP_Post $post );

    /**
     * Detect if save action should fire
     *
     * @param \WP_Post $post
     * @return bool
     */
    protected function shouldSave( \WP_Post $post ){
        // Avoid auto save and revisions
        if( wp_is_post_autosave($post) || wp_is_post_revision($post)){
            return false;
        }
        // Detect post type
        return $this->shouldRegister($post);
    }

    /**
     * Output metabox
     *
     * @param \WP_Post $post
     * @param array $screen
     * @return mixed
     */
    public function doMetaBox(\WP_Post $post, array $screen){

        // echo nonce field
        $this->nonceField();
        $this->registerFields($post);
        if( empty($this->fields) ){
            printf('<p class="description">%s</p>', $this->__('このボックスには表示する要素がありません'));
        }else{
            echo '<table class="form-table wpametu-metabox-table"><tbody>';
            foreach($this->fields as $field){
                $class_name = $this->getClass($field);
                if( $class_name ){
                    call_user_func_array([$class_name, 'renderRow'], [$post, $field]);
                }else{
                    trigger_error(sprintf($this->__('データ型%sは許可されていません。'), $field['type']), E_USER_NOTICE);
                }
            }
            echo '</tbody></table>';
        }
    }

    /**
     * Output something in meta box
     *
     * For example,
     *
     * @param \WP_Post $post
     */
    protected function beforeMetabox( \WP_Post $post){

    }

    /**
     * Output something in metabox
     *
     * @see \WPametu\UI\Admin\Metabox\Multiple::beforeMetabox
     * @param \WP_Post $post
     */
    protected function afterMetabox( \WP_Post $post){

    }


    /**
     * Get class name from type
     *
     * @param array $field
     * @return bool|string
     */
    public function getClass( array $field){
        $type = 'field-'.$field['type'];
        $class_name = '\\WPametu\\UI\\Admin\\Metabox\\Factory\\'.$this->str->hyphenToCamel($type, true);
        if( class_exists($class_name) ){
            return $class_name;
        }else{
            return false;
        }
    }
}
