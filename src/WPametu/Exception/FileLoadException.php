<?php

namespace WPametu\Exception;
use Exception;


/**
 * Exception thrown at library loading
 *
 * @package WPametu
 */
class FileLoadException extends \Exception
{

    /**
     * Error code
     *
     * @var int
     */
    protected $code = 404;

    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct($path = ""){
        parent::__construct(sprintf('Failed to load file %s. Have you uploaded fall files, ah?', $path));
    }


}
