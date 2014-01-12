<?php

namespace WPametu\Controllers;


abstract class RewriteController extends BaseController
{
    protected function __construct( array $argument = [] ){

    }

    abstract public function parseRequest( \WP_Query $wp_query);
}
