<?php

namespace WPametu\UI\Field;
use WPametu\Exception\ValidateException;


/**
 * Text input field
 *
 * @package WPametu\UI\Field
 * @property-read int $min
 * @property-read int $max
 */
class Text extends Input
{

    protected $type = 'text';

    /**
     * Parse arguments
     *
     * @param array $setting
     * @return array
     */
    protected function parse_args( array $setting ){
        return wp_parse_args(parent::parse_args($setting), [
            'min' => 0,
            'max' => 0,
        ]);
    }

    /**
     * Field arguments
     *
     * @return array
     */
    protected function get_field_arguments(){
        $args = parent::get_field_arguments();
        if( $this->min ){
            $args['data-min-length'] = $this->min;
        }
        if( $this->max ){
            $args['data-max-length'] = $this->max;
        }
        if( is_admin() ){
            $args['class'] = 'regular-text';
        }
        return $args;
    }

    /**
     * Validator
     *
     * @param mixed $value
     * @return bool
     * @throws \WPametu\Exception\ValidateException
     */
    protected function validate($value){
        if( parent::validate($value) ){
            $length = strlen(utf8_decode($value));
            if( $this->min &&  $this->min > $length ){
                throw new ValidateException(sprintf($this->__('Fields %s must be %d digits and more.'), $this->label, $this->min));
            }
            if( $this->max &&  $this->min > $length ){
                throw new ValidateException(sprintf($this->__('Fields %s must be %d digits and fewer.'), $this->label, $this->max));
            }
        }
        return true;
    }

} 