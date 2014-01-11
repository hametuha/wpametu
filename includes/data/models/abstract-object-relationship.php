<?php

namespace WPametu\Data\Models;


/**
 * Class UserFollow
 *
 * @package WPametu\Data\Models
 * @property-read string $type key name
 * @property-read string $object_relationships Relationship table name
 */
abstract class AbstractObjectRelationship extends \WPametu\Data\Model
{

    /**
     * Detect if relation exists
     *
     * @param int $subject_id
     * @param int $object_id
     * @return int Relationship ID
     */
    public function exists($subject_id, $object_id){
        $query = <<<EOS
            SELECT ID FROM {$this->object_relationships}
            WHERE type = %s AND subject_id = %d AND object_id = %d
EOS;
        $result = $this->db->get_row($this->db->prepare($query, $this->type, $subject_id, $object_id));
        return (int)$result->ID ?: 0;
    }

    /**
     * Get subject count with object_id
     *
     * @param int $object_id
     * @return int
     */
    public function subject_count($object_id){
        $query = <<<EOS
            SELECT COUNT(ID) FROM {$this->object_relationships}
            WHERE type = %s AND object_id = %d
EOS;
        return (int) $this->db->get_var($query, $this->type, $object_id);
    }


    /**
     * Get subject count with object_id
     *
     * @param int $subject_id
     * @return int
     */
    public function object_count($subject_id){
        $query = <<<EOS
            SELECT COUNT(ID) FROM {$this->object_relationships}
            WHERE type = %s AND subject_id = %d
EOS;
        return (int) $this->db->get_var($query, $this->type, $subject_id);
    }



    /**
     * Insert record
     *
     * @param $subjet_id
     * @param $object_id
     * @return bool|\WP_Error
     */
    public function insert($subjet_id, $object_id){
        return $this->insertWithTime($this->object_relationships, array(
            'type' => $this->type,
            'subject_id' => $subjet_id,
            'object_id' => $object_id,
        ), array('%s', '%d', '%d')) ? true : new \WP_Error(500, $this->__('データを保存出来ませんでした'));
    }

    /**
     * Delete record
     *
     * @param int $subject_id
     * @param int $object_id
     * @return false|int
     */
    public function delete($subject_id, $object_id){
        $query = <<<EOS
            DELETE FROM {$this->object_relationships}
            WHERE type = %s AND subject_id = %d AND object_id = %d
EOS;
        return $this->query($query, $this->type, $subject_id, $object_id)
            ?: new \WP_Error(500, $this->__('データを削除できませんでした'));
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        switch($name){
            case 'type':
                $class_segments = explode('\\', get_called_class());
                return $this->str->camelToHungarian($class_segments[count($class_segments) - 1]);
                break;
            case 'object_relationships':
                return \WPametu\Data\Tables\ObjectRelationships::table_name;
                break;
            default:
                return parent::__get($name);
                break;
        }
    }
} 