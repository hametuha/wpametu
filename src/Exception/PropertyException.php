<?php

namespace WPametu\Exception;


use WPametu\Traits\i18n;

/**
 * Argument exception
 *
 * Fires when argument's length or property
 *
 * @package WPametu\Exception
 */
class PropertyException extends \RuntimeException
{

    use i18n;

    protected $code = 500;

    /**
     * @param string $argument
     * @param string $for
     */
    public function __construct($argument, $for){
        parent::__construct(sprintf($this->__('Property \'%1$s\' is required for %2$s.'), $argument, $for), $this->code);
    }
} 