<?php

namespace WPametu\Utils;

/**
 * String Utility
 *
 * @package WPametu\Utils
 */
final class String extends \WPametu\Pattern\Singleton
{

	/**
	 * Constructor
	 *
	 * Constructor should not be public.
	 *
	 * @param array $argument
	 */
	protected function __construct(array $argument){}


    /**
     * Make camel-cased string hypenated
     *
     * @param string $string
     * @return string
     */
    public function camelToHyphen($string){
        return \WPametu\deCamelize($string);
    }

    /**
     *
     *
     * @param $string
     * @return string
     */
    public function camelToHungarian($string){
        return str_replace('-', '_', $this->camelToHyphen($string));
    }

    /**
     * Make hypnenated string to camel case
     *
     * @param string $string
     * @param bool $upper_first Retuns Uppercase first letter if true. Defalt false.
     * @return string
     */
    public function hyphenToCamel($string, $upper_first = false){
        $str = preg_replace_callback('/-(.)/u', function($match){
            return strtoupper($match[1]);
        }, strtolower($string));
        if($upper_first){
            $str = ucfirst($str);
        }
        return $str;
    }

    /**
     * Detect if string is alphanumeric
     *
     * @param string $string
     * @return bool
     */
    public function isAlphaNumeric($string){
        return (bool) preg_match('/^[a-zA-Z0-9]+$/u', $string);
    }

    /**
     * Detect if string is alph-numeric and hyphen
     *
     * @param string $string
     * @param bool $allow_capital If true, allow Capital. Defautl true
     * @param bool $allow_under_score If true, allow underscore. Default false.
     * @return bool
     */
    public function isAlnumHyphen($string, $allow_capital = true, $allow_under_score = false){
        return (bool) preg_match_all('/^[0-9a-z'.($allow_capital ? 'A-Z' : '').'\-'.($allow_under_score ? '_' : '').']+$/u', $string);
    }

    /**
     * Detect if string is MySQL datetime
     *
     * @param $string
     * @return bool
     */
    public function isDatetime($string){
        return (bool) preg_match_all('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/u', $string);
    }
}