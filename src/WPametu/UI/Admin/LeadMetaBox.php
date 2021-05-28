<?php

namespace WPametu\UI\Admin;


abstract class LeadMetaBox extends EditMetaBox {


	protected function register_ui() {
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
	}

	public function edit_form_after_title( \WP_Post $post ) {
		if ( $this->is_valid_post_type( $post->post_type ) && $this->has_cap() ) {
			echo '<div class="wpametu-after-title">';
			$this->render( $post );
			echo '</div>';
		}
	}
}
