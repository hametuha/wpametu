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
    const JQUERY_TOKEN_INTPUT = 'jquery-token-input';

    /**
     * Token input for admin panel
     */
    const JQUERY_TOKEN_INTPUT_ADMIN = 'jquery-token-input-facebook';

    /**
     * Font Awesome
     */
    const FONT_AWESOME = 'font-awesome';

    /**
     * Constructor
     *
     * @param array $arguments
     */
    protected function __construct(array $arguments){
        add_action('init', [$this, 'registerStyles']);
    }

    /**
     * Register CSS
     */
    public function registerStyles(){
        // jQuery token input
        wp_register_style(self::JQUERY_TOKEN_INTPUT, $this->url->libUrl('css/token-input.css'), [], '1.6.1');
        wp_register_style(self::JQUERY_TOKEN_INTPUT_ADMIN, $this->url->libUrl('css/token-input-admin.css'), [], '1.6.1');
        // Font Awesome
        wp_register_style(self::FONT_AWESOME, $this->url->getMinifiedFile($this->url->libUrl('vendor/font-awesome/css/font-awesome/font-awesome.css')), [], '4.0.3');

    }

} 