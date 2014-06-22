<?php

namespace WPametu\DB;

use WPametu\Pattern, WPametu\Traits;

/**
 * Abstract class which represents DB table
 *
 * @package WPametu\Data
 * @property-read string $table_name Table name of this Table class
 */
abstract class SchemaBase extends Pattern\Singleton
{

    use Traits\i18n, Traits\Util {
        __get as traitGet;
    }

	/**
	 * Version of this table
	 *
	 * Version is used when plugin is updated.
	 *
	 * @var string
	 */
	protected $version = '1.0';

    /**
     * Database charset
     *
     * @var string Default 'utf8'
     */
    protected $charset = 'utf8';

    /**
     * MySQL storage engine
     *
     * If you need to change storage engine,
     * override this property. Default is InnoDB.
     * You can use WPametu\Data\Engine class.
     *
     * @see WPametu\Data\Engine
     * @return String
     */
    protected $engine = Engine::INNODB;

	/**
	 * This tables name
	 *
	 * WP's table prefix will be prepend to it
	 * with magic method.
	 * e.g. 'sample_table' will be 'wp_sample_table'
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Constructor
	 *
	 * @throws \UnexpectedValueException
	 * @param array $arguments Not used.
	 */
	final protected function __construct( array $arguments = [] ){
		// If name isn't set, assign class name
        if( empty($this->name) ){
            $class_name_segments = explode('\\', get_called_class());
            $class_name = $class_name_segments[count($class_name_segments) - 1];
            $this->name = $this->str->camelToHungarian($class_name);
		}
		// Add table create action
		add_action('admin_init', [$this, 'createTable']);
	}

	/**
	 * Whether if table should be updated
	 *
	 * @return bool
	 */
	private function shouldUpdate(){
		$option = get_option($this->optionName(), false);
		return !$option || version_compare($option, $this->version) < 0;
	}

	/**
	 * Should return creation SQL for dbDelta.
	 *
     * @see http://codex.wordpress.org/Creating_Tables_with_Plugins
	 * @return string
	 */
	abstract protected function create();

    /**
     * Execute SQL if updated required
     */
    final public function createTable(){
		if( current_user_can('activate_plugins') && $this->shouldUpdate() ){
            $prev_version = get_option($this->optionName());
            $this->beforeDbDelta( $prev_version );
			$sql = $this->create();
            if( !function_exists('dbDelta') ){
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            }
            dbDelta($sql);
            $this->afterDbDelt($prev_version);
            update_option($this->optionName(), $this->version);
            $this->addMessage( sprintf($this->__('データベース<strong>%s</strong>を更新しました'), $this->table_name) );
		}
	}

	/**
	 * Executed before dbDelta
	 *
	 * If you need some process on updating,
	 * override this function.
     * Typically, deleting index, etc.
	 *
	 * <code>
     * if( version_compare($prev_versoin, $this->version) < 0 ){
     *     $this->query($this->prepare("ALTER TABLE {$this->table} DROP INDEX foo"));
     * }
	 * </code>
	 *
     * @see self::createTable
	 * @param string $prev_version Previous db version
	 */
	protected function beforeDbDelta($prev_version){

	}

    /**
     * Executed after dbDelta
     *
     * @see self::beforeDbDelta
     * @param string $prev_version
     */
    protected function afterDbDelt($prev_version){

    }

    /**
     * Short hand for $wpdb->prepare
     *
     * @global \wpdb $wpdb
     * @param string $sql
     * @return string
     */
    protected function prepare($sql){
        global $wpdb;
        return $wpdb->prepare(func_get_args());
    }

    /**
     * Short hand for $wpdb->query
     *
     * @global \wpdb $wpdb
     * @param string $sql
     * @return mixed
     */
    protected function query($sql){
        global $wpdb;
        return $wpdb->query($sql);
    }

	/**
	 * Return table name with WP's prefix
	 *
     * @global \wpdb $wpdb
	 * @return string
	 */
	private function tableName(){
        global $wpdb;
        return $wpdb->prefix.$this->name;
    }

    /**
     * Detect if MySQL supports specified storage engine
     *
     * @param string $engine Storage engine name. Use \WPametu\Data\Engine
     * @return bool
     */
    protected function supports($engine){
        global $wpdb;
        foreach( $wpdb->get_results('SHOW ENGINES') as $row ){
            if( $engine == $row->Engine ){
                return true;
            }
        }
        return false;
    }

	/**
	 * Option name
	 *
	 * @return string
	 */
	private function optionName(){
		return $this->table_name.'_version';
	}

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'table_name':
                return $this->tableName();
                break;
            default:
                return $this->traitGet($name);
                break;
        }
    }

    /**
     * Method to detect this class inherits WPametu\Data\Table
     *
     * @return bool
     */
    public static function doesInheritTable(){
        return true;
    }
}
