<?php

namespace WPametu\UI\Admin\Metabox\Factory;

use WPametu\Utils, WPametu\UI, WPametu\Traits, WPametu\HTTP;

/**
 * Class FieldAbstract
 *
 * @package WPametu\UI\Admin\Metabox\Factory
 * @author takahasi Fumiki
 */
abstract class FieldAbstract
{

    use Traits\i18n, Traits\Util;

    /**
     * Data attribute to assign
     *
     * Must be same as $fields' property name
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * CSS class name to assign
     *
     * @var array
     */
    protected $class_attr = [];

    /**
     * Field setting
     *
     * @var array
     */
    protected $field = [];

    /**
     * Constructor
     *
     * @param array $field
     */
    final public function __construct( array $field ){
        $this->field = $field;
    }

    /**
     * Returns field data
     *
     * @param \WP_Post $post
     * @return mixed
     */
    public function getData( \WP_Post $post ){
        return get_post_meta($post->ID, $this->field['name'], true);
    }

    /**
     * Render row
     *
     * @param \WP_Post $post
     */
    public function renderRow( \WP_Post $post ){
        $data = $this->getData($post);
        echo '<tr>';
        $required = '';
        if($this->field['required']){
            if( empty($data) && 0 !== $data && '0' !== $data ){
                $icon_class = 'exclamation-circle';
                $span_class = 'required';
            }else{
                $icon_class = 'check-circle';
                $span_class = 'satified';
            }
            $required = sprintf('<small class="%s">%s %s</small>', $span_class, UI\Parts::icon($icon_class), __('必須', 'wpametu'));
        }
        $icon = '';
        if( !empty($this->field['icon_class']) ){
            $icon = UI\Parts::icon($this->field['icon_class']).' ';
        }
        $label = esc_html($this->field['label']);
        echo <<<EOS
        <th><label for="{$this->field['name']}">{$icon}{$label}</label>{$required}</th>
EOS;
        echo '<td>';
        $this->echoField($data, $post);
        if( !empty($this->field['description']) ){
            printf('<p class="description">%s</p>', $this->field['description']);
        }
        echo '<p class="validator"></p>';
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Echo input field
     *
     * @param mixed $data
     * @param \WP_Post $post
     * @return mixed
     */
    public function echoField( $data, \WP_Post $post){

    }

    /**
     * Save data
     *
     * @param \WP_Post $post
     */
    public function save( \WP_Post $post ){
        $data = $this->captureData();
        $result = $this->validate($data);
        if( is_wp_error($result) ){
            // Show message and do nothing.
            $this->addErrorMessage($result->get_error_message());
        }elseif(!$result){
            // Empty, so delete.
            delete_post_meta($post->ID, $this->field['name']);
        }else{
            // Save data
            $this->saveData($post, $this->field['name'], $data);
        }
    }

    /**
     * Capture field data
     *
     * @return mixed
     */
    protected function captureData(){
        return $this->input->post($this->field['name']);
    }

    /**
     * Save data
     *
     * Default, data will be stored as post meta.
     *
     * @param \WP_Post $post
     * @param string $name
     * @param mixed $data
     */
    protected function saveData( \WP_Post $post, $name, $data ){
        update_post_meta($post->ID, $name, $data);
    }

    /**
     * Convert field
     *
     * @param array $field
     * @return array
     */
    protected  function convert( array $field ){
        return $field;
    }

    /**
     * Validate data
     *
     * @param mixed $data
     * @return bool|\WP_Error
     */
    protected  function validate( $data){
        $config = $this->convert($this->field);
        return Utils\Validator::validate($data, $config);
    }

    /**
     * Get field type
     *
     * @return string
     */
    protected function type(){
        $seg = explode('\\', get_called_class());
        $class_name = $seg[count($seg) - 1];
        return $this->str->camelToHyphen($class_name);
    }

    /**
     * Crete attributes
     *
     * @return string
     */
    protected function buildAttribute(){
        $atts = ' ';
        foreach($this->field as $param => $var){
            if( false !== array_search($param, $this->attributes) ){
                switch($param){
                    case 'placeholder':
                    case 'rows':
                        $attr = $param;
                        break;
                    default:
                        $attr = 'data-'.str_replace('_', '-', $param);
                        break;
                }
                $atts .= sprintf('%s="%s" ', $attr,  esc_attr($var));
            }
        }
        return $atts;
    }

    /**
     * Class name to assign
     *
     * @return string
     */
    protected function buildClass(){
        $classes = [];
        if( $this->field['required'] ){
            $classes[] = 'required';
        }
        return implode(' ', array_merge($classes, $this->class_attr));
    }
} 