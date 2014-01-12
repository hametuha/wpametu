<?php

namespace WPametu\DB;


/**
 * Custom meta data table
 *
 * @package WPametu\Data\Tables
 */
abstract class CustomAbstract extends SchemaBase
{

    /**
     * Should return creation SQL for dbDelta.
     *
     * @see http://codex.wordpress.org/Creating_Tables_with_Plugins
     * @return string
     */
    protected function create()
    {
        $meta_value_type = $this->metaValueType();
        $index = $this->additionalIndex();
        return <<<EOS
            CREATE TABLE {$this->table_name} (
                meta_id BIGINT NOT NULL AUTO_INCREMENT,
                type VARCHAR(12) NOT NULL,
                meta_key VARCHAR(12) NOT NULL,
                owner_id BIGINT NOT NULL,
                meta_value {$meta_value_type},
                created DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                updated DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY (meta_id),
                INDEX owner (type, owner_id, meta_key),
                {$index}
            ) ENGINE = {$this->engine} DEFAULT CHARSET = {$this->charset}
EOS;
    }

    /**
     * Should return additional index
     *
     * To change index, override this function
     *
     * <code>
     * return ' INDEX meta_value (type, key, meta_value)';
     * </code>
     *
     * @return string
     */
    protected function additionalIndex(){
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
    abstract protected function metaValueType();
}