<?php

namespace WPametu\Traits;

use WPametu\UI;

/**
 * Provides Post-Redirect-Get approach
 *
 * @package WPametu\Traits
 */
trait Prg
{
    /**
     * Add message
     *
     * @param string $message
     * @param string $from
     */
    protected function addMessage($message, $from = ''){
        UI\PostRedirectGet::getInstance()->addMessage($message, $from);
    }

    /**
     * Add error message
     *
     * @param string $message
     * @param string $from
     */
    protected function addErrorMessage($message, $from = ''){
        UI\PostRedirectGet::getInstance()->addErrorMessage($message, $from);
    }
} 