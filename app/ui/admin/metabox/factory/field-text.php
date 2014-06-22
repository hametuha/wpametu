<?php

namespace WPametu\UI\Admin\Metabox\Factory;


class FieldText extends FieldAbstract
{

    /**
     * Data attribute to assign
     *
     * Must be same as $fields' property name
     *
     * @var array
     */
    protected $attributes = ['length', 'min_length', 'max_length', 'placeholder'];

    /**
     * CSS class name to assign
     *
     * @var array
     */
    protected $class_attr = ['regular-text'];

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
            esc_attr($this->field['name']), esc_attr($data), $this->buildClass(), $this->buildAttribute());
    }
}