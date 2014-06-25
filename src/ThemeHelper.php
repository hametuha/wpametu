<?php

namespace WPametu;


use WPametu\Http\Input;
use WPametu\Pattern\Singleton;
use WPametu\Service\Recaptcha;
use WPametu\String\Utility;


/**
 * WPametu's ThemeHelper
 *
 * Globally callable with wpametu() function.
 *
 * <code>
 * if( 'foo' === wpmaetu()->input->get('var') ){
 *     wp_redirect('SOME_URL');
 *     exit;
 * }
 * </code>
 *
 * @package WPametu
 * @property-read Utility $str
 * @property-read Input $input
 * @property-read Recaptcha $recaptcha
 */
class ThemeHelper extends Singleton
{
    public function __get($key){
        switch( $key ){
            case 'str':
                return Utility::get_instance();
                break;
            case 'input':
                return Input::get_instance();
                break;
            case 'recaptcha':
                return Recaptcha::get_instance();
                break;
        }
    }
} 