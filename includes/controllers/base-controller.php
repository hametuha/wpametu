<?php

namespace WPametu\Controllers;

use WPametu\Pattern, WPametu\Traits;

/**
 * Class Controller
 *
 * @package WPametu
 * @author Takahashi Fumiki
 */
abstract class BaseController extends Pattern\Singleton
{
    use Traits\Util, Traits\i18n;

    /**
     * Constructor
     *
     * @param array $arguments
     */
    protected function __construct( array $arguments = [] ){

    }
} 