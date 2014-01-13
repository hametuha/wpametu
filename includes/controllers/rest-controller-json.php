<?php

namespace WPametu\Controllers;


/**
 * Class RestControllerJson
 *
 * @package WPametu\Controllers
 * @author Takahashi Fumiki
 */
abstract class RestControllerJson extends RestController
{

    /**
     * File extension
     *
     * @var string
     */
    protected $extension = 'json';

    /**
     * Executed before output result.
     *
     * <code>
     * header('Content-Type: application/json');
     * </code>
     *
     * @return void
     */
    protected function contentType(){
        header('Content-Type: application/json; charset=UTF-8');
    }

    /**
     * Executed if method not found
     *
     * @param int $code
     * @param string $message
     * @return void
     */
    protected function error($code, $message = ''){
        $json = empty($message) ? [] : ['message' => $message];
        $this->contentType();
        $this->output($json);
    }

    /**
     * Echo output
     *
     * @param mixed $var
     * @return void
     */
    protected function output($var){
        echo json_encode($var);
    }
}