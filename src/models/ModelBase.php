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
        if ($ID == null || !is_numeric($ID)) {
            return null;
        }
        global $wpdb;
        $model = get_called_class();
        $whatry = 'SELECT *
				FROM ' . $wpdb->prefix . static::$table . '
				WHERE ' . static::$PK . '= %d';
        $row = $wpdb->get_row($wpdb->prepare($whatry, $ID));
        if (!$row) {
            return null;
        }

        $new = new $model();
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
        if (!count($criteria)) {
            return null;
        }
        global $wpdb;
        $objects = [];
        $query = 'SELECT * FROM ' . $wpdb->prefix . static::$table;

        reset($criteria);
        $firstKey = key($criteria);
        // Add the first key to sql sentence
        $query .= ' WHERE ' . $firstKey . ' = "' . $criteria[$firstKey] . '"';
        unset($criteria[$firstKey]);
        // Add the rest of the criteria
        foreach ($criteria as $column => $value) {
            $query .= ' AND ' . $column . ' = "' . $value . '"';
        }

        $resultsQuery = $wpdb->get_results($query);

        if ($single && isset($resultsQuery[0])) {
            return $this->mountModelFromDBObject($resultsQuery[0]);
        }

        foreach ($resultsQuery as $object) {
            $objects[] = $this->mountModelFromDBObject($object);
        }

        return $objects;
    }

    /**
     * Mount the ModelBase using the data from the DB.
     *
     * @param object $dbObject
     *
     * @return ModelBase
     */
    private function mountModelFromDBObject($dbObject)
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