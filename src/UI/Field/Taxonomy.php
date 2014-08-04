<?php

namespace WPametu\UI\Field;


use WPametu\Exception\PropertyException;

/**
 * Taxonomy field
 *
 * @package WPametu\UI\Field
 */
trait Taxonomy
{

    /**
     * Override class method
     *
     * @param array $setting
     * @throws \WPametu\Exception\PropertyException
     */
    protected function test_setting( array $setting = [] ){
        if( !taxonomy_exists($setting['name']) ){
            throw new PropertyException('taxonomy', get_called_class());
        }
    }

    /**
     * Save post data
     *
     * @param mixed $value
     * @param \WP_Post $post
     */
    protected function save($value, \WP_Post $post = null){
        // Do nothing, because taxonomy will be automatically save.
    }

    /**
     * Get terms as option
     *
     * @return array
     */
    protected function get_options(){
        $terms = get_terms($this->name);
        if( !$terms || is_wp_error($terms) ){
            return [];
        }else{
            $result = [];
            foreach( $terms as $term ){
                $result[$term->term_id] = $term->name;
            }
            return $result;
        }
    }


    /**
     * Get input name
     *
     * @param int $index
     * @return string
     */
    protected function get_name( $index = 0 ){
        if( 'category' == $this->name ){
            return 'post_category[]';
        }else{
            return 'tax_input['.$this->name.'][]';
        }
    }
}
