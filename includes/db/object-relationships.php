<?php

namespace WPametu\DB;


/**
 * ObjectRelationships table
 *
 * @package WPametu\Data\Tables
 */
class ObjectRelationships extends SchemaBase
{

    protected $version = '1.0';

    /**
     * Should return creation SQL for dbDelta.
     *
     * @see http://codex.wordpress.org/Creating_Tables_with_Plugins
     * @return string
     */
    protected function create()
    {
        return <<<EOS
            CREATE TABLE {$this->table_name} (
                ID BIGINT NOT NULL AUTO_INCREMENT,
                type VARCHAR(48) NOT NULL,
                subject_id BIGINT NOT NULL,
                object_id BIGINT NOT NULL,
                created DATETIME NOT NULL,
                updated DATETIME NOT NULL,
                PRIMARY KEY (ID),
                INDEX subject (type, subject_id, object_id),
                INDEX object (type, object_id)
            ) ENGINE = {$this->engine} DEFAULT CHARSET = {$this->charset}
EOS;
    }
}