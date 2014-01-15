<?php

namespace WPametu\UI\Admin\Metabox\Factory;


class FieldText extends FieldAbstract
{

    /**
     * Echo input field
     *
     * @param mixed $data
     * @param \WP_Post $post
     * @param array $field
     * @return mixed
     */
    public static function echoField($data, \WP_Post $post, array $field){
        $data = strval($data);
        printf('<input type="text" name="%1$s" id="%1$s" class="regular-text" value="%2$s" />', esc_attr($field['name']), esc_attr($data));
    }
}