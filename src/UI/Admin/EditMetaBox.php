<?php

namespace WPametu\UI\Admin;

use WPametu\UI\MetaBox;

abstract class EditMetaBox extends MetaBox
{

    /**
     * Meta box context
     *
     * @var string 'normal', 'side', and 'advanced'
     */
    protected $context = 'advanced';

    /**
     * Meta box priority
     *
     * @var string 'high', 'low', and 'default'
     */
    protected $priority = 'default';


    /**
     * Register UI hook
     */
    protected function register_ui(){
        add_action('add_meta_boxes', [$this, 'add_meta_boxes'], 10, 2);
    }

    /**
     * Register meta box
     *
     * @param string $post_type
     * @param \WP_Post $post
     */
    public function add_meta_boxes($post_type, \WP_Post $post){
        if( $this->is_valid_post_type($post_type) && $this->has_cap() ){
            if( empty($this->name) || empty($this->label) ){
                $message = sprintf($this->__('<code>%s</code> has invalid name or label.'), get_called_class());
                add_action('admin_notices', function() use ($message) {
                    printf('<div class="error"><p>%s</p></div>', $message);
                });
            }else{
                add_meta_box($this->name, $this->label, [$this, 'render'], $post_type, $this->context, $this->priority);
            }
        }
    }
}
