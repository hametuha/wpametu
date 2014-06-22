<?php

namespace WPametu\Models;

use WPametu\DB;

/**
 * Class CustomAbstract
 *
 * @package WPametu\Data\Models
 * @property-read string $datetime Custome datetime table name
 * @property-read string $decimal Custome datetime table name
 * @property-read string $geom Custome datetime table name
 * @property-read string $table_name Table name
 */
abstract class CustomAbstract extends ModelBase
{

    /**
     * Object type
     *
     * You can override this property with 'user', 'term', etc.
     *
     * @var string
     */
    protected $type = 'post';

    /**
     * Data type
     *
     * You can override this property with 'datetime', 'decimal', 'geom'
     *
     * @var string
     */
    protected $data_type = 'decimal';

    /**
     * データ型が利用可能か調べる
     */
    protected function initialized(){
        if(! ($this->{$this->data_type})){
            trigger_error(sprintf($this->__('%sの%sは対応していないデータ型です'), get_called_class(), $this->data_type), E_USER_WARNING);
        }
    }

    /**
     * Returns get Query string
     *
     * @param string $select Default *
     * @param string $order_by
     * @param string $date_field Default 'updated' or 'created'
     * @return string
     */
    private function getQuery($select = '*', $order_by = 'DESC', $date_field = 'updated'){
        switch($order_by){
            case 'asc':
            case 'ASC':
                $order_by = 'meat_value IS NULL ASC, meta_value ASC';
                break;
            case 'desc':
            case 'DESC':
                $order_by = 'meta_value DESC';
                break;
            case null:
                $order_by = '';
                break;
            default:
                // Do nothing
                break;
        }
        if(!empty($order_by)){
            $order_by .= ',';
        }
        return <<<EOS
            SELECT {$select} FROM {$this->table_name}
            WHERE type = %s
              AND owner_id = %d
              AND meta_key = %s
            ORDER BY {$order_by}
                     {$date_field} DESC
EOS;
    }

    /**
     * Returns single var
     *
     * @param $owner_id
     * @param $key
     * @param string $order_by
     * @return null|string
     */
    public function get($owner_id, $key, $order_by = 'created'){
        return $this->getVar($this->getQuery('meta_value', 'DESC'), $this->type, $owner_id, $key);
    }

    /**
     * Returns all result
     *
     * @param int $owner_id
     * @param string $key
     * @param string $order_by Default 'created'
     * @return array
     */
    public function getAll($owner_id, $key, $order_by = 'created'){
        return $this->getResults($this->getQuery('meta_value', 'DESC'), $this->type, $owner_id, $key);
    }


    public function getPaged($owner_id, $key, $page = 1, $per_page = 0, $order_by = 'created'){

    }

    /**
     * Insert meta data
     *
     * @param int $owner_id
     * @param string $key
     * @param mixed $value
     * @return int
     */
    abstract public function insert($owner_id, $key, $value);

    /**
     * Update meta data
     *
     * @param int $meta_id
     * @param mixed $value
     * @return mixed
     */
    abstract public function update($meta_id, $value);

    /**
     * Check existance
     *
     * @param $owner_id
     * @param $key
     * @return int meta_id if exists
     */
    public function exists($owner_id, $key){
        $query = <<<EOS
            SELECT meta_id FROM {$this->table_name}
            WHERE type = %s
              AND owner_id = %d
              AND meta_key = %s
EOS;
        return (int)$this->getVar($query, $this->type, $owner_id, $key);
    }

    public function valueExists($owner_id, $key, $value){
        $query = <<<EOS
            SELECT meta_id FROM {$this->table_name}
            WHERE type = %s
              AND owner_id = %d
              AND meta_key = %s
              AND meta_value = %s
EOS;

    }

    /**
     * Update single meta_key
     *
     * @param int $owner_id
     * @param string $key
     * @param mixed $value
     * @return int|mixed
     */
    public function singleUpdate($owner_id, $key, $value){
        $meta_id = $this->exists($owner_id, $key);
        if($meta_id){
            return (bool) $this->update($meta_id, $value);
        }else{
            return (bool) $this->insert($owner_id, $key, $value);
        }
    }

    /**
     * Clear all params
     *
     * @param int $owner_id
     * @param string $key
     * @return false|int Count of deleted data
     */
    public function clear($owner_id, $key){
        $query = <<<EOS
            DELETE FROM {$this->table_name}
            WHERE type = %s
              AND owner_id = %d
              AND meta_key = %s
EOS;
        return $this->query($query, $this->type, $owner_id, $key);
    }

    /**
     * Drop all data for specified owner
     *
     * @param int $owner_id
     * @return false|int Count of deleted data
     */
    public function dropOwner($owner_id){
        $query = <<<EOS
            DELETE FROM {$this->table_name}
            WHERE type = %s
              AND owner_id = %d
EOS;
        return $this->query($query, $this->type, $owner_id);
    }

    /**
     * Drop all data which have specified key
     *
     * @param string $key
     * @return false|int Count of deleted data
     */
    public function dropKey($key){
        $query = <<<EOS
            DELETE FROM {$this->table_name}
            WHERE type = %s
              AND meta_key = %s
EOS;
        return $this->query($query, $this->type, $key);
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'datetime':
            case 'decimal':
            case 'geom':
                return call_user_func(['Custom'.ucfirst($name), 'getInstance'])->table_name;
                break;
            case 'table_name':
                return $this->{$this->data_type};
                break;
            default:
                return parent::__get($name);
                break;
        }
    }
} 