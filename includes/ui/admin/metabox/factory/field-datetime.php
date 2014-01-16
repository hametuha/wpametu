<?php

namespace WPametu\UI\Admin\Metabox\Factory;


class FieldDatetime extends FieldText
{

    protected $attributes = ['placeholder', 'precede', 'follow'];

    protected $class_attr = ['datetime-text', 'datetime-picker'];

    /**
     * Echo input field
     *
     * @param mixed $data
     * @param \WP_Post $post
     * @return mixed
     */
    public function echoField($data, \WP_Post $post){
        $data = strval($data);
        printf('<input type="text" name="%1$s" id="%1$s" class="%3$s" value="%2$s"%4$s/>',
            esc_attr($this->field['name']), esc_attr($data),
            $this->buildClass(), $this->buildAttribute());
    }
}