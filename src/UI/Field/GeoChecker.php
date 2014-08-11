<?php

namespace WPametu\UI\Field;


use WPametu\Exception\PropertyException;

/**
 * Geo checker
 *
 * @package WPametu\UI\Field
 * @property-read string $target
 */
class GeoChecker extends GeoPoint
{

    /**
     * Do not show GeoCoder
     *
     * @var bool
     */
    protected $show_geocoder = false;

    /**
     * Do not save.
     */
    protected function save(){
        // Do nothing.
    }

    /**
     * Add error message.
     *
     * @param \WP_Post $post
     * @return string
     */
    protected function get_field( \WP_Post $post ){
        $html = parent::get_field($post);
        return $html.sprintf('<p class="inline-error"><i class="dashicons dashicons-location-alt"></i> %s</p>', $this->__('Can\'t find map point. Try another string'));
    }

    /**
     * Get field arguments
     *
     * @return array
     */
    protected function get_field_arguments(){
        return array_merge(parent::get_field_arguments(), [
            'data-no-drag' => '1',
            'data-target' => $this->target,
        ]);
    }

    /**
     * This field has no data.
     *
     * @param \WP_Post $post
     * @return mixed|string
     */
    protected function get_data( \WP_Post $post ){
        return '';
    }

    /**
     * Test setting
     *
     * @param array $setting
     * @throws \WPametu\Exception\PropertyException
     */
    protected function test_setting( array $setting ){
        parent::test_setting($setting);
        if( empty($setting['target']) ){
            throw new PropertyException('target', get_called_class());
        }
    }
}
