<?php

namespace WPametu;

/**
 * CSS manager
 *
 * @package WPametu
 * @author Takahashi Fumiki
 */
class Css extends Pattern\Singleton
{

    use Traits\Util;

    /**
     * Token input
     */
    const JQUERY_TOKEN_INTPUT = 'jquery-tokeninput';

    /**
     * Token input for admin panel
     */
    const JQUERY_TOKEN_INTPUT_ADMIN = 'jquery-tokeninput-admin';

    /**
     * jQuery UI Theme for admin
     */
    const JQUERY_UI = 'jquery-ui-mp6';

    /**
     * Font Awesome
     */
    const FONT_AWESOME = 'font-awesome';

    /**
     * Metabox
     */
    const METABOX = 'wpametu-metabox';

    /**
     * Constructor
     *
     * @param array $arguments
     */
    protected function __construct(array $arguments){
        add_action('init', [$this, 'registerStyles']);
        add_action('admin_enqueue_scripts', [$this, 'removeWPMP'], 100);
    }

    /**
     * Register CSS
     */
    public function registerStyles(){
        // jQuery UI
        wp_register_style(self::JQUERY_UI, $this->url->libUrl('vendor/jquery-ui-mp6/src/css/jquery-ui.css'), [self::JQUERY_UI], '1.0');
        // jQuery token input
        wp_register_style(self::JQUERY_TOKEN_INTPUT, $this->url->libUrl('css/token-input.css'), [], '1.6.1');
        wp_register_style(self::JQUERY_TOKEN_INTPUT_ADMIN, $this->url->libUrl('css/token-input-admin.css'), [], '1.6.1');
        // Font Awesome
        wp_register_style(self::FONT_AWESOME, $this->url->getMinifiedFile($this->url->libUrl('vendor/font-awesome/css/font-awesome.css')), [], '4.0.3');
        // Metabox
        wp_register_style(self::METABOX, $this->url->libUrl('css/metabox.css'), [self::JQUERY_UI], VERSION);

    }

    /**
     * Remove WP Mutitbyte Patch CSS
     *
     * WPMP set all font family to 'normal' and kill
     * Font-Awesome.
     */
    public function removeWPMP(){
        wp_dequeue_style('wpmp-admin-custom');
    }
} 