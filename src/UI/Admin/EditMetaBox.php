<?php

namespace WPametu\UI\Admin;

use WPametu\UI\MetaBox;

class EditMetaBox extends MetaBox
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
     * Register save post hook
     */
    protected function register_save_action(){
        add_action('save_post', [$this, 'save_post'], 10, 2);
    }


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

    /**
     * Save post data
     *
     * @param int $post_id
     * @param \WP_Post $post
     */
    public function save_post($post_id, \WP_Post $post ){
        // Skip auto save
        if( wp_is_post_revision($post) || wp_is_post_autosave($post) ){
            return;
        }
        // Check Nonce
        if( !$this->verify_nonce() ){
            return;
        }
        // O.K., let's save
        foreach( $this->loop_fields() as $field ){
            try{
                if( is_wp_error($field) ){
                    /** @var \WP_Error $field */
                    throw new \Exception($field->get_error_message(), $field->get_error_code());
                }
                /** @var \WPametu\UI\Field\Base $field */
                $field->update($this->input->post($field->name), $post);
            }catch ( \Exception $e ){
                $this->prg->addErrorMessage($e->getMessage());
            }

        }
    }
}
