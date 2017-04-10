<?php

namespace WPametu\API\Rest;


/**
 * REST API with static content
 *
 * @package WPametu
 */
abstract class RestFormat extends RestBase
{

    /**
     * Handle result object
     *
     * @param mixed $result
     */
    protected function handle_result( $result ){
        if( isset($this->content_type) ){
            header("Content-Type: {$this->content_type}");
            echo $this->convert_result($result);
        }
    }

    /**
     * Convert result object to some format
     *
     * @param $result
     * @return string
     */
    abstract protected function convert_result( $result );

} 