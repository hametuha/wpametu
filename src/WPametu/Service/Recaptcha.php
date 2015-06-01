<?php

namespace WPametu\Service;


use WPametu\Exception\FileLoadException;
use WPametu\File\Path;
use WPametu\Http\Input;
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
 * @property-read Input $input
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
     * @deprecated
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
     * @link https://developers.google.com/recaptcha/docs/language
     * @param string $theme light(default), dark
     * @param string $lang en(default), fr, nl, de, pt, ru, es, tr, ja and more.
     * @param string $type image(default) audio
     * @return string|false
     */
    public function get_html($theme = 'light', $lang = 'en', $type = 'image'){
        if( $this->enabled ){
            // Option value
            $option = [];
            // Select theme
            switch($theme){
                case 'dark':
                    $option['theme'] = $theme;
                    break;
                case 'red':
                default:
					$option['theme'] = 'light';
                    // Do nothing
                    break;
            }
	        // Select image
	        switch( $type ){
		        case 'audio':
					$option['type'] = $type;
			        break;
		        default:
					$option['type'] = 'image';
			        break;
	        }
            // Select language
            switch($lang){
	            case 'ar':
	            case 'bg':
	            case 'ca':
	            case 'zh-CN':
	            case 'zh-TW':
	            case 'hr':
	            case 'cs':
	            case 'da':
	            case 'nl':
	            case 'en-GB':
	            case 'en':
	            case 'fil':
	            case 'fi':
	            case 'fr':
	            case 'fr-CA':
	            case 'de':
	            case 'de-AT':
	            case 'de-CH':
	            case 'el':
	            case 'iw':
	            case 'hi':
	            case 'hu':
	            case 'id':
	            case 'it':
	            case 'ja':
	            case 'ko':
	            case 'lv':
	            case 'lt':
	            case 'no':
	            case 'fa':
	            case 'pl':
	            case 'pt':
	            case 'pt-BR':
	            case 'pt-PT':
	            case 'ro':
	            case 'ru':
	            case 'sr':
	            case 'sk':
	            case 'sl':
	            case 'es':
	            case 'es-419':
	            case 'sv':
	            case 'th':
	            case 'tr':
	            case 'uk':
	            case 'vi':
	                $option['lang'] = $lang;
                    break;
                default:
                    // Do nothing
					$option['lang'] = 'en';
                    break;

            }
            /**
             * wpametu_recaptcha_setting
             *
             * Filter option to set for reCaptcha
             *
             * @param array $option
             * @return array
             */
            $option = apply_filters('wpametu_recaptcha_setting', $option);
            $script = '<script src="https://www.google.com/recaptcha/api.js?hl='.$option['lang'].'" async defer></script>';
            $html = <<<HTML
<div class="g-recaptcha" data-sitekey="%s" data-theme="%s" data-type="%s"></div>
HTML;
            return $script.sprintf($html, constant(self::PUBLIC_KEY), $option['theme'], $option['type']);
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
        if( $this->enabled && ($response = $this->input->request('g-recaptcha-response')) && $this->input->remote_ip() ){
	        $endpoint = sprintf('https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s&remoteip=%s',
		        constant(self::PRIVATE_KEY), rawurlencode($response), rawurlencode($this->input->remote_ip()));
	        $response = wp_remote_get($endpoint);
	        if( is_wp_error($response) ){
		        return false;
	        }
	        $result = json_decode($response['body']);
	        return isset($result->success) && $result->success;
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
	        case 'input':
				return Input::get_instance();
		        break;
            default:
                // Do nothing.
                break;
        }
    }
} 