<?php

namespace WPametu\UI\Admin;

use WPametu\Pattern;

abstract class Metabox extends Pattern\Singleton
{

    /**
     * If subclass is intialized
     * @var bool
     */
    private static $initialized = false;

    protected $name = '';


}