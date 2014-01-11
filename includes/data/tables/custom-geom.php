<?php

namespace WPametu\Data\Tables;


/**
 * Class CustomFloat
 *
 * @package WPametu\Data\Tables
 */
class CustomGeom extends CustomAbstract
{

    protected $version = '1.0';

    protected $engine = \WPametu\Data\Engine::MYISAM;

    protected function additionalIndex()
    {
        return "SPATIAL INDEX meta_value (meta_value)";
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
        return "GEOMETRY NOT NULL";
    }
}