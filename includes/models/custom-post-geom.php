<?php

namespace WPametu\Models;


class CustomPostGeom extends CustomAbstract
{

    /**
     * @var string
     */
    protected $type = 'post';

    /**
     * Insert meta data
     *
     * @param int $owner_id
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function insert($owner_id, $key, $value){
        $query = <<<EOS
            INSERT INTO {$this->table_name}
            ( type, owner_id, meta_key, meta_value, created, updated)
            VALUES
            ( %s, %d, %s, GeomFromText(%s), %s, %s )
EOS;
        $now = current_time('mysql');
        if($this->query($query, $this->type, $owner_id, "POINT($value)", $now, $now)){
            return $this->db->insert_id;
        }else{
            return 0;
        }
    }

    /**
     * Update meta data
     *
     * @param int $meta_id
     * @param mixed $value
     * @return int
     */
    public function update($meta_id, $value){
        $query = <<<EOS
            UPDATE {$this->table_name}
            SET meta_value = GeomFromText(%s)
EOS;
        return (int)$this->query($query, $meta_id, "POINT($value)");
    }

    /**
     * Change latlng to MySQL POINT data
     *
     * <code>
     *
     * </code>
     *
     * @param float $latitude
     * @param float $longitude
     * @return string
     */
    public function latlngToDB($latitude, $longitude){
        return "$longitude $latitude";
    }
}