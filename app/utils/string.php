<?php

namespace WPametu\Utils;

use WPametu\Pattern;

/**
 * String Utility
 *
 * @package WPametu\Utils
 */
final class String extends Pattern\Singleton
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
     * Make camel-cased string to hungarian
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
     * @param bool $allow_capital Specify capital letters allowance. Default true
     * @return bool
     */
    public function isAlphaNumeric($string, $allow_capital = true){
        return (bool) preg_match('/^[a-z'.( $allow_capital ? 'A-Z' : '' ).'0-9]+$/u', $string);
    }

    /**
     * Convert full class name to hyphenated string
     *
     * @param string $string
     * @return string
     */
    public function classNameToHyphen($string){
        return implode('-', array_map([$this, 'camelToHyphen'], explode('\\', trim($string, '\\'))));
    }

    /**
     * Convert full class name to hungarian string
     *
     * @param string $string
     * @return string
     */
    public function classNameToHungarian($string){
        return str_replace('-', '_', $this->classNameToHyphen($string));
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
        return (bool) preg_match('/^[0-9a-z'.($allow_capital ? 'A-Z' : '').'\-'.($allow_under_score ? '_' : '').']+$/u', $string);
    }

    /**
     * Detect if string is suitable for url segments
     *
     * @param string $string
     * @param bool $allow_slush
     * @return bool
     */
    public function isUrlSegments($string, $allow_slush = false){
        return (bool) preg_match('/^[a-z0-9.~'.($allow_slush ? '' : '\/').']+$/u', $string);
    }

    /**
     * Detect if string is MySQL datetime
     *
     * @param $string
     * @return bool
     */
    public function isDatetime($string){
        return (bool) preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/u', $string);
    }

    /**
     * Detect if string is MySQL Date
     *
     * @param string $string
     * @return bool
     */
    public function isDate($string){
        return (bool) preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/u', $string);
    }

    /**
     * Detect if string is URL like
     *
     * @param string $string
     * @return bool
     */
    public function isUrl($string){
        return (bool) preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $string);
    }

    /**
     * Get translated month name
     *
     * @param int $month 1~12
     * @param string $format 'F' or 'M'
     * @return string
     */
    public function monthName($month, $format = 'F'){
        $month = (int) $month;
        $format = $format == 'F' ? 'F' : 'M';
        $month = sprintf('2013-%02d-01 00:00:00', $month);
        return mysql2date($format, $month);
    }
}