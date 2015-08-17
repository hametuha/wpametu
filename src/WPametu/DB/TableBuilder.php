<?php

namespace WPametu\DB;


use WPametu\Exception\OverrideException;
use WPametu\File\Path;
use WPametu\Pattern\Singleton;


/**
 * Table Builder
 *
 * Assistance for table creation.
 * Putting files on YOUR_THEME_DIR/config/db/*.php.
 *
 * @package WPametu\DB
 * @property-read \wpdb $db
 * @property-read array $option
 */
final class TableBuilder extends Singleton
{

    use Path;

    /**
     * @var string
     */
    private $option_key = '_wpametu_table_versions';

    /**
     * Constructor
     *
     * @param array $setting
     */
    protected function __construct( array $setting = [] ){
        // Add admin init
        add_action('admin_init', [$this, 'table_test']);
    }

    /**
     * Check authentication and do update
     *
     */
    public function table_test(){
        if( !defined('DOING_AJAX') && $this->has_auth() ){
            // Grab all config file and test them.
            $config_files = glob($this->get_config_dir().'/db/*.php');
            if( !empty($config_files) ){
                try{
                    $messages = [];
                    foreach($config_files as $file){
                        $message= $this->db_update($file);
                        if( !empty($message) ){
                            $messages[] = $message;
                        }
                    }
                    if( !empty($messages) ){
                        add_action('admin_notices', function() use ($messages){
                            printf('<div class="updated">%s</div>', implode('', array_map(function($message){
                                return sprintf('<p>%s</p>', $message);
                            }, $messages)));
                        });
                    }
                }catch ( \Exception $e ){
                    wp_die(sprintf('[DB Error] Failed to parse DB configs: '.$e->getMessage()), get_status_header_desc(500), ['response' => 500]);
                }
            }
        }
    }


    /**
     * Update db if possible
     *
     * @param string $file path to config
     * @return string
     * @throws \Exception
     */
    private function db_update( $file ){
        require $file;
        if( isset($table) ){
            // Test required members
            $config = wp_parse_args($table, [
                'name' => str_replace('.php', '', basename($file)),
                'version' => false,
                'engine' => Engine::INNODB,
                'columns' => false,
                'primary_key' => [],
                'indexes' => [],
            ]);
            $table_name = $config['name'];
            if( $config['name'] && $config['version'] && $config['indexes'] ){
                if( $this->need_update($table_name, $config['version']) ){
                    // Add prefix
                    $config['name'] = $this->db->prefix.$config['name'];
                    // Need update. Let's make query!
                    $query = $this->make_query($config);
                    if( !function_exists('dbDelta') ){
                        require_once ABSPATH . "wp-admin/includes/upgrade.php";
                    }
                    // Do dbDelta
                    $result = dbDelta($query);
                    // Return message
                    if( isset( $result[$config['name']] ) ){
                        $message = sprintf('<code>%s</code> created.', $config['name']);
                    }else{
                        $message = sprintf('<code>%s</code> updated.', $config['name']);
                    }
                    if( !empty($this->db->last_error) ){
                        $message .= sprintf(' <small>(Error: %s)</small>', $this->db->last_error);
                    }
                    // Check table existence
                    if( $this->table_exists($config['name']) ){
                        update_option($this->option_key, array_merge($this->option, [
                            $table_name => $config['version'],
                        ]));
                    }else{
                        $message = sprintf('Failed to create or update <code>%s</code>', $config['name']);
                    }
                    return $message;
                }
            }else{
                throw new \Exception(sprintf('Config file %s is wrong.', $file));
            }
        }else{
            throw new \Exception(sprintf('Config file %s must be PHP Array format.', $file));
        }
        return '';
    }

    /**
     * Detect if table exists
     *
     * @param string $table_name
     * @return bool
     */
    private function table_exists($table_name){
        return (bool)$this->db->get_row($this->db->prepare('SHOW TABLES LIKE %s', $table_name));
    }

    /**
     * Build create query
     *
     * @param array $config
     * @return string
     * @throws \Exception
     */
    private function make_query($config){
        /** @var $name string */
        /** @var $version string */
        /** @var $engine string */
        /** @var $columns array */
        /** @var $primary_key array */
        /** @var $indexes array */
	    /** @var $fulltext array */
	    /** @var $charset string */
        extract($config);
        if( empty($columns) ){
            throw new \Exception(sprintf('Columns of %s shouldn\'t be empty.', $name), 500);
        }
        $column_query = [];
        foreach( $columns as $name => $column ){
            $column_query[] = Column::build_query($name, $column);
        }
        // Is primary key is specified.
        if( !empty($primary_key) ){
            $column_query[] = sprintf('PRIMARY KEY (%s)', implode(', ', $primary_key));
        }
        // Is index exists?
        if( !empty($indexes) ){
            foreach( $indexes as $name => $index ){
                $keys = (array)$index;
                $column_query[] = sprintf('KEY %s (%s)', $name, implode(', ', $index));
            }
        }
	    // has full text index?
	    if( !empty($fulltext) ){
		    foreach( $fulltext as $name => $index ){
			    $keys = (array) $index;
			    $column_query[] = sprintf('FULLTEXT %s (%s)', $name, implode(', ', $index));
		    }
	    }
	    if( !isset($charset) ){
		    $charset = 'utf8';
	    }
	    // Normalize charset
        $sql = <<<SQL
CREATE TABLE %s (
    %s
) ENGINE = %s CHARACTER SET %s
SQL;
        $sql = sprintf($sql, $config['name'], implode(','.PHP_EOL.'    ', $column_query), $engine, $charset);
        return $sql;
    }

    /**
     * Detect if user can create table
     *
     * @return bool
     */
    protected function has_auth(){
        /**
         * wpametu_table_create_auth
         *
         * @param bool $has_auth
         * @return bool
         */
        return apply_filters('wpametu_table_create_auth', current_user_can('manage_options'));
    }

    /**
     * Detect if source version is greater than existing db
     *
     * @param string $name
     * @param string $version
     * @return bool
     */
    protected function need_update($name, $version){
        return !isset($this->option[$name]) || !$this->option[$name] || version_compare($this->option[$name], $version, '<');
    }

    /**
     * Fires at the end of constructor
     *
     * Constructor is final, so override this.
     */
    protected function init_handler(){
        // Do something.
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'db':
                global $wpdb;
                return $wpdb;
                break;
            case 'option':
                return get_option($this->option_key, array());
                break;
            default:
                // Do nothing
                break;
        }
    }

}
