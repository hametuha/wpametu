<?php

namespace WPametu\UI\Field;


/**
 * GeoPoint
 *
 * @package WPametu
 */
class GeoPoint extends Hidden {


	/**
	 * If true, filed isn't automatically saved.
	 *
	 * @var bool
	 */
	protected $original_callback = false;

	/**
	 * If true, show GeoCoder helper
	 *
	 * @var bool
	 */
	protected $show_geocoder = true;

	/**
	 * Default center
	 *
	 * @var array 'lat' and 'lng' property
	 */
	protected $default_center = array(
		'lat' => '35.685175',
		'lng' => '139.752799',
	);

	/**
	 * Default zoom
	 *
	 * @var int
	 */
	protected $default_zoom = 14;

	/**
	 * Add map canvas
	 *
	 * @param \WP_Post $post
	 * @return string
	 */
	protected function get_field( \WP_Post $post ) {
		$field     = parent::get_field( $post );
		$automatic = $this->original_callback ? ' original' : '';
		$geocder   = $this->show_geocoder ? $this->geocoder() : '';
		return <<<HTML
            {$field}
            <div id="{$this->name}-map" class="wpametu-map{$automatic}"></div>
            {$geocder}
HTML;
	}

	/**
	 * Override rendered row
	 *
	 * @param string $label
	 * @param string $required
	 * @param string $input
	 * @param string $desc
	 * @param \WP_Post $post
	 * @return string
	 */
	protected function render_row( $label, $required, $input, $desc, \WP_Post $post ) {
		return <<<HTML
            <tr>
                <td colspan="2" class="geo-row">
                    <label class="block">{$label}{$required}</label>
                    {$input}
                    {$desc}
                </td>
            </tr>
HTML;
	}

	/**
	 * Field arguments
	 *
	 * @return array
	 */
	protected function get_field_arguments() {
		return array(
			'data-lat'  => $this->default_center['lat'],
			'data-lng'  => $this->default_center['lng'],
			'data-zoom' => $this->default_zoom,
		);
	}

	/**
	 * Get GeoCoder input
	 *
	 * @return string
	 */
	protected function geocoder() {
		$helper = $this->__( 'Input address' );
		$btn    = $this->__( 'Move point' );
		$fail   = esc_attr( $this->__( 'Sorry, but nothing found with input address' ) );
		return <<<HTML
        <p>
            <input type="text" class="gmap-geocoder regular-text" placeholder="{$helper}" />
            <a class="button gmap-geocoder-btn" href="#" data-failure="{$fail}">{$btn}</a>
        </p>
HTML;
	}
}
