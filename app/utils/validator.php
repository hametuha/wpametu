<?php

namespace WPametu\Utils;

use WPametu\Utils;

/**
 * Class Validator
 *
 * @package WPametu\Utils
 * @author Takahashi Fumiki
 */
class Validator
{

    /**
     * i18n Domain
     *
     * @var string
     */
    static protected $i18n_domain = 'wpametu';

    /**
     * Default config array
     *
     * @var array
     */
    static private $default = [
        'type' => 'text',
        'label' => '',
        'length' => 0,
        'min_length' => 0,
        'max_length' => 0,
        'max' => 0,
        'min' => 0,
        'required' => false,
    ];


    /**
     * Validate data with config array
     *
     * @param mixed $data
     * @param array $config
     * @return bool|\WP_Error If error occurs, return WP_Error. Empty is false.
     */
    public static function validate($data, array $config){
        // Setup config
        extract( wp_parse_args($config, self::$default) );
        /** @var string $type */
        /** @var string $label */
        /** @var int $min_length */
        /** @var int $max_length */
        /** @var int $min */
        /** @var int $max */
        /** @var bool $required */
        // Start validation
        if( empty($data) && 0 !== $data && '0' !== $data ){
            // Data is not set
            if( $required ){
                return self::__('%sは必須項目です。', $label);
            }else{
                return false;
            }
        }
        // Data is set, start validation
        switch( $type ){
            case 'text':
                break;
            case 'url':
                if(!self::str()->isUrl($data)){
                    return self::__('%sはURL形式でなければなりません。', $label);
                }
                break;
            case 'alnum':
                if( !self::str()->isAlphaNumeric($data)){
                    return self::__('%sは半角英数でなければなりません。', $label);
                }
                break;
            case 'alnumhyphen':
                if( !self::str()->isAlnumHyphen($data) ){
                    return self::__('%sは半角英数およびハイフンでなければなりません。');
                }
                break;
            case 'int':
            case 'numeric':
                if( !preg_match('/^[0-9]+$/', $data) ){
                    return self::__('%sは整数でなければなりません。', $label);
                }
                break;
            case 'float':
                if( !is_numeric($data) ){
                    return self::__('%sは整数および少数でなければなりません', $label);
                }
                break;
            case 'email':
                if( !is_email($data) ){
                    return self::__('%sはメールアドレスでなければなりません。', $label);
                }
                break;
            case 'datetime':
                if( !self::str()->isDatetime($data) ){
                    return self::__('%sは日付形式（YYYY-MM-DD HH:ii:ss）でなければなりません。', $label);
                }
                break;
        }
        return true;
    }

    /**
     * Short hand for string
     *
     * @return \WPametu\Utils\String
     */
    public static function str(){
        return Utils\String::getInstance();
    }

    /**
     * Short hand for i18n
     *
     * @param string $string
     * @return \WP_Error
     */
    public static function __($string){
        $args = func_get_args();
        if( 1 < count($args) ){
            $args[0] = __($string, self::$i18n_domain);
            return new \WP_Error(500, call_user_func_array('sprintf', $args));
        }else{
            return new \WP_Error(500, __($string, self::$i18n_domain));
        }
    }
}
