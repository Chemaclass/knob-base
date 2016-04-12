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

    /**
     *
     * @var array Columns from the table of our Model
     */
    protected static $columns = array();

    /**
     *
     * @var string Primary Key
     */
    protected static $PK = 'ID';

    /*
     * Members
     */
    public $ID;

    public $created_at;

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
     * @return array<Object>
     */
    public static function all()
    {
        global $wpdb;
        // Son class
        $model = get_called_class();
        $whatryResults = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . $model::$table);
        $result = [];
        foreach ($whatryResults as $qr) {
            $a = new $model();
            foreach (self::$columns as $c) {
                $a->$c = $qr->$c;
            }
            $result[] = $a;
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
        if (empty($ID) || !is_numeric($ID)) {
            return null;
        }
        global $wpdb;
        $calledClass = get_called_class();
        // Remove the Knob as possible prefix from called_class
        if (0 === strpos($calledClass, 'Knob\\')) {
            $calledClass = substr($calledClass, strpos($calledClass, '\\') + 1);
        }
        $whatry = 'SELECT *
        FROM ' . $wpdb->prefix . static::$table . '
        WHERE ' . static::$PK . '= %d';
        $row = $wpdb->get_row($wpdb->prepare($whatry, $ID));
        if (!$row) {
            return null;
        }
        
        $new = new $calledClass();
        foreach ($row as $column => $val) {
            $new->$column = $val;
        }
        
        return $new;
    }

    /**
     * Search all values across one column
     *
     * @param string $column
     * @param string $value
     * @param boolean $single Por defecto false. True si es sólo 1.
     *
     * @deprecated see: ModelBase::findBy(criteria)
     *
     * @return array<object>
     */
    public static function findByColumn($column, $value, $single = false)
    {
        global $wpdb;
        $objects = [];
        $model = get_called_class();
        $query = 'SELECT * FROM ' . $wpdb->prefix . static::$table . ' WHERE ' . $column . '= %s';
        $resultsQuery = $wpdb->get_results($wpdb->prepare($query, $value));

        /*
         * Mount the object
         */
        $mountTheObject = function ($_object) use($model)
        {
            $object = new $model();
            foreach ($_object as $column => $val) {
                $object->$column = $val;
            }
            return $object;
        };

        if ($single) {
            foreach ($resultsQuery as $_object) {
                return $mountTheObject($_object);
            }
        }

        foreach ($resultsQuery as $_object) {
            $objects[] = $mountTheObject($_object);
        }

        return $objects;
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
        $objects = [];
        $query = static::getQueryByCriteria($criteria);
        $resultsQuery = $wpdb->get_results($query);

        if ($single && isset($resultsQuery[0])) {
            return static::getModelFromDBObject($resultsQuery[0]);
        }

        foreach ($resultsQuery as $object) {
            $objects[] = static::getModelFromDBObject($object);
        }

        return $objects;
    }

    /**
     *
     * @param array $criteria
     *
     * @return string
     */
    private static function getQueryByCriteria($criteria = [])
    {
        $sqlQuery = 'SELECT * FROM ' . $wpdb->prefix . static::$table;
        if (!count($criteria)) {
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
     * Todo one DELETE
     *
     * @return Exception|boolean
     */
    public function delete()
    {
        if (!$this->ID) {
            return false;
        }
        global $wpdb;
        try {
            $sql = 'DELETE FROM ' . $wpdb->prefix . static::$table . ' WHERE ID = %d';

            return $wpdb->query($wpdb->prepare($sql, $this->ID));
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Get the first element from the where
     *
     * @param unknown $column
     * @param unknown $what
     * @param unknown $value
     */
    public static function first($column, $what, $value)
    {
        $w = self::where($column, $what, $value);
        if ($w && is_array($w)) {
            return $w[0];
        }

        return null;
    }

    /**
     * Return the filter results
     *
     * @param string $column
     * @param string $what
     * @param string $value
     */
    public static function where($column, $what, $value)
    {
        global $wpdb;
        // TODO: Improve it. Generate the correct select instead of get all elements and
        // filter by php...
        $all = self::all();
        $result = [];
        foreach ($all as $item) {
            if (isset($item->$column)) {
                if (self::isCompareColumn($item->$column, $what, $value)) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    /**
     *
     * @param string $column
     * @param string $what
     * @param string $value
     *
     * @return boolean
     */
    private static function isCompareColumn($column, $what, $value)
    {
        switch ($what) {
            case "=":
                if ($column == $value) {
                    return true;
                }
                break;
            case "<":
                if ($column < $value) {
                    return true;
                }
                break;
            case ">":
                if ($column > $value) {
                    return true;
                }
                break;
            case ">=":
                if ($column >= $value) {
                    return true;
                }
                break;
            case "<=":
                if ($column <= $value) {
                    return true;
                }
                break;
        }

        return false;
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
     * @param string $kindOfNonce Type of nonce
     * @return string Nonce
     */
    protected function createNonce($kindOfNonce)
    {
        return wp_create_nonce($kindOfNonce . $this->ID);
    }
}
