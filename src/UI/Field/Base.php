<?php

namespace WPametu\UI\Field;


use WPametu\Exception\PropertyException;
use WPametu\Traits\i18n;

/**
 * Field base
 *
 * @package WPametu\UI\Field
 * @property-read string $name
 * @property-read string $label
 * @property-read string $description
 * @property-read bool $required
 */
abstract class Base
{

    use i18n;

    /**
     * Setting
     *
     * @var array
     */
    protected $setting = [];

    /**
     * Constructor
     *
     * @param array $setting
     */
    public function __construct( array $setting = [] ){
        try{
            $this->test_setting($setting);
            $setting = $this->parse_args($setting);
            $this->setting = $setting;
        }catch( \Exception $e ){
            if( headers_sent() ){
                // Header sent.
                printf('<div class="error"><p>%s</p></div>', $e->getMessage());
            }else{
                // Header didn't sent
                wp_die($e->getMessage(), get_status_header_desc($e->getCode()), [
                    'response' => $e->getCode(),
                    'back_link' => true,
                ]);
            }
        }
    }

    /**
     * Parse setting array
     *
     * @param array $setting
     * @return array
     */
    protected function parse_args( array $setting ){
        return wp_parse_args($setting, [
            'name' => '',
            'label' => '',
            'description' => '',
            'required' => false,
        ]);
    }

    /**
     * Render input field
     *
     * @param \WP_Post $post
     */
    public function render( \WP_Post $post = null ){
        $required = $this->required ? sprintf(' <small class="required"><i class="dashicons dashicons-yes"></i> %s</small>', $this->__('Required')) : '';
        $label = esc_html($this->label);
        $desc_str = $this->description;
        $desc = !empty($desc_str) ? sprintf('<p class="description">%s</p>', $this->description) : '';
        $input = $this->get_field($post);
        echo $this->render_row($label, $required, $input, $desc, $post);
    }

    /**
     * Render row
     *
     * @param string $label
     * @param string $required
     * @param string $input
     * @param string $desc
     * @param \WP_Post $post
     * @return string
     */
    protected function render_row($label, $required, $input, $desc, \WP_Post $post ){
        return <<<HTML
            <tr>
                <th><label for="{$this->name}">{$label}{$required}</label></th>
                <td>
                    {$input}
                    {$desc}
                </td>
            </tr>
HTML;
    }

    /**
     * Return input field
     *
     * @param \WP_Post $post
     * @return string
     */
    protected function get_field( \WP_Post $post ){
        return '';
    }

    /**
     * Get saved data
     *
     * @param \WP_Post $post
     * @return mixed
     */
    protected function get_data( \WP_Post $post ){
        switch( $this->name ){
            case 'excerpt':
                return $post->post_excerpt;
                break;
            default:
                if( false !== array_search(Taxonomy::class, class_uses(get_called_class())) ){
                    // This is taxonomy
                    $terms = get_the_terms($post, $this->name);
                    $term_id = 0;
                    if( $terms && !is_wp_error($terms) ){
                        foreach($terms as $term){
                            $term_id = $term->term_id;
                        }
                    }
                    return $term_id;

                }else{
                    // This is post meta
                    return get_post_meta($post->ID, $this->name, true);
                }
                break;
        }
    }

    /**
     * Save data as value
     *
     * @param mixed $value
     * @param \WP_Post $post
     */
    public function update($value, \WP_Post $post = null){
        if( $this->validate($value) ){
            $this->save($value, $post);
        }
    }

    /**
     * Save post data
     *
     * @param mixed $value
     * @param \WP_Post $post
     */
    protected function save($value, \WP_Post $post = null){
        update_post_meta($post->ID, $this->name, $value);
    }

    /**
     * Test setting arguments
     *
     * Check argument's property and throw error if invalid.
     *
     * @param array $setting
     * @throws PropertyException
     */
    protected function test_setting( array $setting ){
        if( empty($setting['name']) ){
            throw new PropertyException('name', get_called_class());
        }
    }

    /**
     * Validate value
     *
     * If validation failed, return false.
     * You can override this.
     *
     * @param mixed $value
     * @return bool
     */
    protected function validate($value){
        return true;
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed|null
     */
    public function __get($name){
        if( isset($this->setting[$name]) ){
            return $this->setting[$name];
        }else{
            return null;
        }
    }

} 