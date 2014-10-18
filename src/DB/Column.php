<?php

namespace WPametu\DB;


use WPametu\Traits\Reflection;

/**
 * Column shorthand
 *
 * @package WPametu\DB
 */
class Column
{

    use Reflection;

    const TINYINT = 'TINYINT';

    const INT = 'INT';

    const MEDIUMINT = 'MEDIUMINT';

    const BIGINT = 'BIGINT';

    const FLOAT = 'FLOAT';

    const REAL = 'REAL';

    const DOUBLE = 'DOUBLE';

    const DECIMAL = 'DECIMAL';

    const CHAR = 'CHAR';

    const VARCHAR = 'VARCHAR';

    const BINARY = 'BINARY';

    const VARBINARY = 'VARBINARY';

    const TINYBLOB = 'TINYBLOB';

    const BLOB = 'BLOB';

    const MEDIUMBLOB = 'MEDIUMBLOB';

    const LONGBLOB = 'LONGBLOB';

    const TINYTEXT = 'TINYTEXT';

    const TEXT = 'TEXT';

    const MEDIUMTEXT = 'MEDIUMTEXT';

    const LONGTEXT = 'LONGTEXT';

    const ENUM = 'ENUM';

    const SET = 'SET';

    const DATETIME = 'DATETIME';

    const DATE = 'DATE';

    const TIMESTAMP = 'TIMESTAMP';

    const TIME = 'TIME';

    const YEAR = 'YEAR';

    const GEOMETRY = 'GEOMETRY';

    const POINT = 'POINT';

    const LINESTRING = 'LINESTRING';

    const POLYGON = 'POLYGON';

    /**
     * Detect if column type exists
     *
     * @param string $type
     * @return bool
     */
    public static function exists($type){
        $constants = self::get_all_constants();
        return false !== array_search($type, $constants);
    }

    /**
     * If type requires length
     *
     * @param string $type
     * @return bool
     */
    private static function is_length_required($type){
        return false !== array_search($type, [self::VARCHAR, self::VARBINARY]);
    }

    /**
     * Type is numeric
     *
     * @param string $type
     * @return bool
     */
    private static function is_numeric($type){
        switch($type){
            case self::INT:
            case self::TINYINT:
            case self::MEDIUMINT:
            case self::BIGINT:
            case self::DOUBLE:
            case self::FLOAT:
            case self::REAL:
            case self::DECIMAL:
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Type is SET or ENUM
     *
     * @param string $type
     * @return bool
     */
    private static function is_set($type){
        return false !== array_search($type, [self::ENUM, self::SET]);
    }


    public static function build_query($name, $column){
        global $wpdb;
        $column = self::filter($column);
        $sql = sprintf('`%s` %s', $name, self::data_type($column));
        return $sql;
    }

    /**
     * Build data type on create statement
     *
     * @param array $column
     * @return string
     */
    private static function data_type($column){
        /** @var $wpdb \wpdb */
        global $wpdb;
        // Let's create query
        $sql = $column['type'];
        // Is DECIMAL?
        if( self::DECIMAL == $column['type'] ){
            $sql .= sprintf('(%d, %d)', $column['max_digit'], $column['float']);
        }
        // Add length
        if( isset($column['length']) ){
            $sql .= sprintf('(%d)', $column['length']);
        }
        // Is enum or set?
        if( self::is_set($column['type']) ){
            $values = [];
            foreach($column['values'] as $val){
                $values[] = $wpdb->prepare('%s', $val);
            }
            $sql .= sprintf('(%s)', implode(', ', $values));
        }
        // Is unsigned?
        if( self::is_numeric($column['type']) && isset($column['signed']) && !$column['signed'] ){
            $sql .= ' UNSIGNED';
        }
        // Is not null?
        if( !$column['null'] ){
            $sql .= ' NOT NULL';
        }
        // Is auto increment?
        if( isset($column['auto_increment']) && $column['auto_increment'] ){
            $sql .= ' AUTO_INCREMENT';
        }
        // Is primary key?
        if( isset($column['primary']) && $column['primary'] ){
            $sql .= ' PRIMARY KEY';
        }
        // Has default?
        if( isset($column['default']) ){
            if( self::is_numeric($column['default']) ){
                $repl = ' DEFAULT '.$column['default'];
            }else{
	            switch( $column['default'] ){
		            case 'CURRENT_TIMESTAMP':
						// Without replace
						$sql .= " DEFAULT {$column['default']}";
			            break;
		            default:
						// Replace
		                $sql .= $wpdb->prepare(' DEFAULT %s', $column['default']);
			            break;
	            }
            }
        }
        return $sql;
    }

    /**
     * Filter column array
     *
     * @param array $column
     * @return array
     * @throws \Exception
     */
    public static function filter($column){
        $column = wp_parse_args($column, [
            'type' => '',
            'null' => false,
        ]);
        // Type required.
        if( !self::exists($column['type']) ){
            throw new \Exception('You must properly specify the data type of column.');
        }
        // Check varchar length
        if( self::is_length_required($column['type']) && !isset($column['length']) ){
            throw new \Exception(sprintf('Column %s requires length property.', $column['type']));
        }
        // Signed or Unsigned?
        if( self::is_numeric($column['type']) ){
            $column['signed'] = isset($column['signed']) ? (bool)$column['signed'] : true;
        }
        // If decimal, test_required params
        if( $column['type'] == self::DECIMAL ){
            if( !isset($column['max_digit'], $column['float']) ){
                throw new \Exception(sprintf('Column %s requires max_digit and float property.', self::DECIMAL));
            }
        }
        // If enum or set, test required properties.
        if( self::is_set($column['type']) && ( !is_array($column['values']) || empty($column['values']) ) ){
            throw new \Exception(sprintf('Column %s requires values property as array.', $column['type']));
        }
        return $column;
    }

}
