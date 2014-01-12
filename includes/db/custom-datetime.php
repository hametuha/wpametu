<?php

namespace WPametu\DB;


/**
 * Class CustomDatetime
 * @package WPametu\Data\Tables
 */
class CustomDatetime extends CustomAbstract
{

    protected $version = '1.0';

    protected function additionalIndex()
    {
        return "INDEX meta_value (type, meta_key, meta_value)";
    }

    /**
     * Should return meta_value type
     *
     * <code>
     * return "DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL";
     * </code>
     *
     * @return string
     */
    protected function metaValueType()
    {
        return "DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL";
    }
}