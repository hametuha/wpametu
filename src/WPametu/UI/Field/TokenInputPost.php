<?php

namespace WPametu\UI\Field;

/**
 * TokenInput UI for Post search
 *
 * @package WPametu
 * @property-read string $post_type
 * @property-read string $field
 */
class TokenInputPost extends TokenInput {


	/**
	 * Parse arguments
	 *
	 * @param array $setting
	 * @return array
	 */
	protected function parse_args( array $setting ) {
		return wp_parse_args(
			parent::parse_args( $setting ),
			array(
				'post_type' => 'post',
				'field'     => 'post_parent',
			)
		);
	}

	/**
	 * Get saved data
	 *
	 * @param \WP_Post $post
	 * @return array|mixed
	 */
	protected function get_data( \WP_Post $post ) {
		switch ( $this->field ) {
			case 'post_meta':
				return get_posts(
					array(
						'posts__in'        => explode( ',', get_post_meta( $post->ID, $this->name, true ) ),
						'post_type'        => $this->post_type,
						'author'           => $post->post_author,
						'suppress_filters' => false,
					)
				);
				break;
			default:
				if ( $post->post_parent ) {
					$args = array(
						'p'                => $post->post_parent,
						'post_type'        => $this->post_type,
						'suppress_filters' => false,
						'post_status'      => array( 'publish', 'future', 'pending', 'draft', 'private' ),
					);
					/**
					 * wpametu_token_input_post_args
					 *
					 * @param array   $args
					 * @param \WP_Post $post
					 */
					$args = apply_filters( 'wpametu_token_input_post_args', $args, $post );
					return get_posts( $args );
				} else {
					return array();
				}
				break;
		}
	}


	/**
	 * Save post data
	 *
	 * @param mixed $value
	 * @param \WP_Post $post
	 */
	protected function save( $value, \WP_Post $post = null ) {
		switch ( $this->field ) {
			case 'post_meta':
				update_post_meta( $post->ID, $this->name, $value );
				break;
			default:
				$post_id = intval( $value );
				// TODO: Reconsider updating post row logic
				/** @var \wpdb $wpdb */
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $post_id ), array( 'ID' => $post->ID ), array( '%d' ), array( '%d' ) );
				clean_post_cache( $post );
				break;
		}
	}

	/**
	 * Add button before required
	 *
	 * @param \WP_Post $post
	 * @return string
	 */
	protected function get_required( \WP_Post $post = null ) {
		if ( current_user_can( 'edit_posts' ) ) {
			$url           = admin_url( 'post-new.php?post_type=' . $this->post_type );
			$post_type_obj = get_post_type_object( $this->post_type );
			$tip           = esc_html( sprintf( $this->__( 'Add new %s' ), $post_type_obj->labels->name ) );
			$input         = <<<HTML
<a href="{$url}" data-tooltip-title="{$tip}"><i class="dashicons dashicons-plus-alt"></i></a>
HTML;
			return $input . parent::get_required( $post );
		} else {
			return parent::get_required( $post );
		}

	}

	/**
	 * Get prepopulated data
	 *
	 * @param array $data
	 * @return array
	 */
	protected function get_prepopulates( $data ) {
		$json = array();
		foreach ( $data as $post ) {
			$json[] = array(
				'id'   => $post->ID,
				'name' => $post->post_title,
			);
		}
		return $json;
	}

	/**
	 * Get endpoint
	 *
	 * @return mixed
	 */
	protected function get_endpoint() {
		return admin_url( 'admin-ajax.php?action=post_search&post_type=' . $this->post_type );
	}
}
