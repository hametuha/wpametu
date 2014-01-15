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

    /**
     * Returns field data
     *
     * @param \WP_Post $post
     * @param array $field
     * @return mixed
     */
    public static function getData( \WP_Post $post, array $field ){
        return get_post_meta($post->ID, $field['name'], true);
    }

    /**
     * Render row
     *
     * @param \WP_Post $post
     * @param array $field
     */
    public static function renderRow( \WP_Post $post, array $field ){
        $data = self::getData($post, $field);
        echo '<tr>';
        $required = '';
        if($field['required']){
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
        if( !empty($field['icon_class']) ){
            $icon = UI\Parts::icon($field['icon_class']).' ';
        }
        $label = esc_html($field['label']);
        echo <<<EOS
        <th><label for="{$field['name']}">{$icon}{$label}</label>{$required}</th>
EOS;
        echo '<td>';
        call_user_func_array([get_called_class(), 'echoField'], array($data, $post, $field));
        if( !empty($field['description']) ){
            printf('<p class="description">%s</p>', $field['description']);
        }
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Echo input field
     *
     * @param mixed $data
     * @param \WP_Post $post
     * @param array $field
     * @return mixed
     */
    public static function echoField( $data, \WP_Post $post, array $field){

    }

    /**
     * Save data
     *
     * @param \WP_Post $post
     * @param array $field
     */
    public static function save( \WP_Post $post, array $field ){
        /** @var \WPametu\HTTP\Input $input */
        $input = HTTP\Input::getInstance();
        $data = $input->post($field['name']);
        $result = self::validate($data, $field);
        if( is_wp_error($result) ){
            // Show message and do nothing.
            /** @var \WPametu\UI\PostRedirectGet $prg */
            $prg = UI\PostRedirectGet::getInstance();
            $prg->addErrorMessage($result->get_error_message());
        }elseif(!$result){
            // Empty, so delete.
            delete_post_meta($post->ID, $field['name']);
        }else{
            // Save data
            self::saveData($post, $field['name'], $data);
        }
    }

    /**
     * Save data
     *
     * Default, data will be stored at post meta.
     *
     * @param \WP_Post $post
     * @param string $name
     * @param mixed $data
     */
    protected static function saveData( \WP_Post $post, $name, $data ){
        update_post_meta($post->ID, $name, $data);
    }

    /**
     * Convert field
     *
     * @param array $field
     * @return array
     */
    protected  static function convert( array $field ){
        return $field;
    }

    /**
     * Validata data
     *
     * @param mixed $data
     * @param array $field
     * @return bool|\WP_Error
     */
    protected  static function validate( $data, array $field){
        $config = self::convert($field);
        return Utils\Validator::validate($data, $config);
    }

    /**
     * Get field type
     *
     * @return string
     */
    protected static function type(){
        $seg = explode('\\', get_called_class());
        $class_name = $seg[count($seg) - 1];
        /** @var \WPametu\Utils\String $str */
        $str = Utils\String::getInstance();
        return $str->camelToHyphen($class_name);
    }
} 