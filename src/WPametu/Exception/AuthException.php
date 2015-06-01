<?php

namespace WPametu\Exception;

/**
 * Authenctication error
 *
 * @package WPametu\Exception
 */
class AuthException extends \Exception
{

    /**
     * Error Code
     *
     * @var int
     */
    protected $code = 403;

    /**
     * Constructor
     *
     * @param string $message
     */
    public function __construct($message){
        parent::__construct($message, $this->code);
    }

} 