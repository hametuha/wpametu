<?php

namespace WPametu\Exception;

/**
 * Validation exception
 * 
 * @package WPametu\Exception
 */
class ValidateException extends \RuntimeException
{
    protected $code = 500;

    /**
     * Constructor
     *
     * @param string $message
     */
    public function __construct($message){
        parent::__construct($message, $this->code);
    }
}
