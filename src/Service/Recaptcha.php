<?php

namespace WPametu\Service;


use WPametu\Exception\FileLoadException;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;


/**
 * Recaptcha Class
 *
 * You have to define 2 constants,
 * 'WPAMETU_RECAPTURE_PUBLIC_KEY' and 'WPAMETU_RECAPTURE_PRIVATE_KEY'.
 * Defines these on wp-config.php.
 *
 * <code>
 * define('WPAMETU_RECAPTURE_PUBLIC_KEY', 'YOUR_PUBLIC_KEY');
 * define('WPAMETU_RECAPTURE_PRIVATE_KEY', 'YOUR_PRIVATE_KEY');
 * </code>
 *
 *
 * @package WPametu\Service
 * @property-read bool $enabled
 * @property-read string $lib Library path.
 */
class Recaptcha extends Singleton
{

    use Path;


    /**
     * Name of public key
     */
    const PUBLIC_KEY = 'WPAMETU_RECAPTURE_PUBLIC_KEY';


    /**
     * Name of private key
     */
    const PRIVATE_KEY = 'WPAMETU_RECAPTURE_PRIVATE_KEY';

    /**
     * Load reCaptcha library
     *
     * @throws \WPametu\Exception\FileLoadException
     */
    private function load_lib(){
        if( file_exists($this->lib) ){
            require_once $this->lib;
        }else{
            throw new FileLoadException($this->lib);
        }
    }

    /**
     * Return reCaptcha's HTML
     *
     * @param string $theme clean(default), white, red, blackglass
     * @param string $lang en(default), fr, nl, de, pt, ru, es, tr, ja
     * @return string|false
     */
    public function get_html($theme = 'clean', $lang = 'en'){
        if( $this->enabled ){
            // Option value
            $option = [];
            // Select theme
            switch($theme){
                case 'clean':
                case 'white':
                case 'blackglass':
                    $option['theme'] = $theme;
                    break;
                case 'red':
                default:
                    // Do nothing
                    break;
            }
            // Select language
            switch($lang){
                case 'nl':
                case 'de':
                case 'fr':
                case 'pt':
                case 'ru':
                case 'es':
                case 'tr':
                    $option['lang'] = $lang;
                    break;
                case 'en':
                default:
                    // Do nothing
                    break;

            }
            /**
             * wpametu_recaptcha_setting
             *
             * Filter option to set for reCaptcha
             *
             * @param array $option
             * @param string $theme
             * @param string $lang
             * @return array
             */
            $option = apply_filters('wpametu_recaptcha_setting', $option, $theme, $lang);
            $script = '';
            if( !empty($option) ){
                $json = json_encode($option);
                $script .= <<<EOS
<script type="text/javascript">
window.RecaptchaOptions = {$json};
</script>
EOS;
            }
            $this->load_lib();
            return $script.recaptcha_get_html( constant(self::PUBLIC_KEY), null, is_ssl() );
        }else{
            return false;
        }
    }

    /**
     * Validate reCaptcha
     *
     * @return bool
     */
    public function validate(){
        if( $this->enabled && isset($_REQUEST["recaptcha_challenge_field"], $_REQUEST["recaptcha_response_field"]) ){
            $this->load_lib();
            $resp = recaptcha_check_answer(constant(self::PRIVATE_KEY), $_SERVER['REMOTE_ADDR'],
                $_REQUEST["recaptcha_challenge_field"], $_REQUEST["recaptcha_response_field"]);
            return $resp->is_valid;
        }else{
            return false;
        }
    }

    /**
     * Getter
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key){
        switch($key){
            case 'enabled':
                return defined(self::PUBLIC_KEY) && defined(self::PRIVATE_KEY);
                break;
            case 'lib':
                return $this->get_vendor_dir().'/reCaptcha/recaptchalib.php';
                break;
            default:
                // Do nothing.
                break;
        }
    }
} 