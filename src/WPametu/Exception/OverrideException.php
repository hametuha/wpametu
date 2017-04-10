<?php

namespace WPametu\Exception;


/**
 * Thrown when class override is wrong
 *
 * @package WPametu
 */
class OverrideException extends \Exception
{

    /**
     * Error code
     *
     * @var int
     */
    protected $code = 500;


    /**
     * Constructor
     *
     * @param string $class_name
     */
    public function __construct($class_name){
        parent::__construct(sprintf('%s is not properly override parent class. See Parent class\'s documentation.', $class_name));
    }

} 