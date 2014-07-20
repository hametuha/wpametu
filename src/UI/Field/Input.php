<?php

namespace WPametu\UI\Field;
use WPametu\Exception\ValidateException;


/**
 * Input field base
 *
 * @package WPametu\UI\Field
 * @property-read string $placeholder
 */
abstract class Input extends Base
{

    /**
     * Input type
     *
     * @var string
     */
    protected $type = '';

    /**
     * Get field
     *
     * @param \WP_Post $post
     * @return string
     */
    protected function get_field( \WP_Post $post ){
        $fields = [];
        foreach( $this->get_field_arguments() as $key => $val ){
            $fields[] = sprintf('%s="%s"', $key, esc_attr($val));
        }
        $fields = implode(' ', $fields);
        return sprintf('<input id="%1$s" name="%1$s" type="%2$s" %3$s value="%4$s" />',
            $this->name, $this->type, $fields, esc_attr($this->get_data($post)));
    }

    /**
     * @param array $setting
     * @return array
     */
    protected function parse_args( array $setting ){
        return wp_parse_args(parent::parse_args($setting), [
            'placeholder' => '',
        ]);
    }

    /**
     * @return array
     */
    protected function get_field_arguments(){
        return [
            'placeholder' => $this->placeholder,
        ];
    }

    /**
     * Validate values
     *
     * @param mixed $value
     * @return bool
     * @throws ValidateException
     */
    protected function validate($value){
        if( parent::validate($value) ){
            if( $this->required && empty($value) ){
                throw new ValidateException(sprintf($this->__('Field %s is required.'), $this->label));
            }
        }
        return true;
    }
}