<?php

namespace WPametu\DB;


/**
 * Class CustomFloat
 *
 * @package WPametu\Data\Tables
 */
class CustomDecimal extends CustomAbstract
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
        return "DECIMAL(28, 4) NOT NULL";
    }
}