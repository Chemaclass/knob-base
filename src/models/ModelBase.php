<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Models;

/**
 * Base abstract for all Models
 *
 * @author José María Valera Reales
 */
abstract class ModelBase
{

    /**  @var array Columns from the table of our Model */
    protected static $columns = array();

    /**  @var string Primary Key */
    protected static $PK = 'ID';

    /** @var int ID */
    public $ID;

    /** @var \DateTime reated_at */
    public $created_at;

    /** @var \DateTime updated_at */
    public $updated_at;

    /**
     * Constructor
     *
     * @param integer $ID            
     */
    public function __construct($ID = 0)
    {
        $this->ID = $ID;
        global $wpdb;
        static::$columns = $wpdb->get_col_info();
    }

    /**
     * Return the ID
     *
     * @return string|NULL
     */
    public function getId()
    {
        $PK = static::$PK;
        if (isset($this->$PK)) {
            return $this->$PK;
        }
        
        return null;
    }

    /**
     * Get the table name with the WP prefix
     *
     * @return string
     */
    public static function getTableName()
    {
        global $wpdb;
        
        return $wpdb->prefix . static::$table;
    }

    /**
     * Return all objects
     *
     * @return array
     */
    public static function all()
    {
        global $wpdb;
        
        $dbResults = $wpdb->get_results('SELECT * FROM ' . static::getTableName());
        $result = [];
        $model = get_called_class();
        foreach ($dbResults as $dbResult) {
            $modelObject = new $model();
            foreach (self::$columns as $column) {
                $modelObject->$column = $dbResult->$column;
            }
            $result[] = $modelObject;
        }
        
        return $result;
    }

    /**
     * Search and return the Object across his ID
     *
     * @param integer $ID            
     * @return object
     */
    public static function find($ID = false)
    {
        if (empty($ID) || ! is_numeric($ID)) {
            return null;
        }
        global $wpdb;
        $calledClass = get_called_class();
        // Remove the Knob as possible prefix from called_class
        if (0 === strpos($calledClass, 'Knob\\')) {
            $calledClass = substr($calledClass, strpos($calledClass, '\\') + 1);
        }
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE ' . static::$PK . '= %d';
        if (! $row = $wpdb->get_row($wpdb->prepare($sql, $ID))) {
            return null;
        }
        
        $new = new $calledClass();
        foreach ($row as $column => $val) {
            $new->$column = $val;
        }
        
        return $new;
    }

    /**
     * Get all elements from DB who's contain the same criteria.
     *
     * @param array $criteria            
     * @param string $single            
     *
     * @return unknown|multitype:NULL
     */
    public static function findBy($criteria = [], $single = false)
    {
        global $wpdb;
        $return = [];
        $query = static::getQueryByCriteria($criteria);
        $dbResults = $wpdb->get_results($query);
        
        if ($single && isset($dbResults[0])) {
            return static::getModelFromDBObject($dbResults[0]);
        }
        
        foreach ($dbResults as $object) {
            $return[] = static::getModelFromDBObject($object);
        }
        
        return $return;
    }

    /**
     *
     * @param array $criteria            
     * @param int $limit            
     * @param int $offset            
     *
     * @return string
     */
    private static function getQueryByCriteria($criteria = [], $limit = false, $offset = false)
    {
        $sqlQuery = 'SELECT * FROM ' . static::getTableName();
        if (! count($criteria)) {
            return $sqlQuery;
        }
        $criteriaKeys = array_keys($criteria);
        $firstKey = $criteriaKeys[0];
        // Add the first key to sql sentence
        $sqlQuery .= ' WHERE ' . $firstKey . ' = "' . $criteria[$firstKey] . '"';
        unset($criteriaKeys[$firstKey]);
        // Add the rest of the criteria
        foreach ($criteriaKeys as $key) {
            $sqlQuery .= ' AND ' . $key . ' = "' . $criteria[$key] . '"';
        }
        
        if ($limit) {
            $sqlQuery .= ' LIMIT ' . $limit;
        }
        if ($offset) {
            $sqlQuery .= ' OFFSET ' . $offset;
        }
        
        return $sqlQuery;
    }

    /**
     * Mount the ModelBase using the data from the DB.
     *
     * @param object $dbObject            
     *
     * @return ModelBase
     */
    private static function getModelFromDBObject($dbObject)
    {
        $modelName = get_called_class();
        $object = new $modelName();
        foreach ($dbObject as $column => $val) {
            $object->$column = $val;
        }
        
        return $object;
    }

    /**
     * Delete the object
     *
     * @return boolean
     */
    public function delete()
    {
        if (! $this->ID) {
            return false;
        }
        
        try {
            global $wpdb;
            $sql = 'DELETE FROM ' . static::getTableName() . ' WHERE ' . static::$PK . ' = %d';
            
            return $wpdb->query($wpdb->prepare($sql, $this->ID));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the first elem by criteria
     *
     * @param array $criteria            
     *
     * @return ModelBase
     */
    public static function first($criteria)
    {
        return $this->findBy($criteria, true);
    }

    /**
     * __toArray
     *
     * @return array
     */
    public function __toArray()
    {
        return call_user_func('get_object_vars', $this);
    }

    /**
     * Create one key for the nonce request from ajax
     *
     * @param string $kindOfNonce
     *            Type of nonce
     * @return string Nonce
     */
    protected function createNonce($kindOfNonce)
    {
        return wp_create_nonce($kindOfNonce . $this->ID);
    }
}
