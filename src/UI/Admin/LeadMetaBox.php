<?php

namespace WPametu\UI\Admin;


class LeadMetaBox extends EditMetaBox
{

    protected function __construct( array $setting = [] ){
        add_action('edit_form_after_title', [$this, 'edit_form_after_title']);
        add_action('save_post', [$this, 'save_post'], 10, 2);
    }

    public function edit_form_after_title( \WP_Post $post ){
        if( $this->is_valid_post_type($post->post_type) && $this->has_cap() ){
            echo '<div class="wpametu-after-title">';
            $this->render($post);
            echo '</div>';
        }
    }
} 