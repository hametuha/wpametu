<?php

namespace WPametu\Utils;


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

}