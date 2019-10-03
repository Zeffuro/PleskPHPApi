<?php
/**
 * A mysqli wrapper class
 *
 * @author Andrew Lowndes (APL Web)
 * @date 20/11/2010
 */
class Database2
{
    public static $db = null;
    private static $host;
    private static $user;
    private static $password;
    private static $database;
    
    //connect to the database
    public static function connect()
    {
        self::$db = new mysqli(DBHOST2, DBUSER2, DBPASS2, DBDATABASE2);

        if (mysqli_connect_errno()) {
            throw new Exception('Connection failed: ' . mysqli_connect_error());
        }

        self::$db->set_charset("utf8");
    }

    //close the connection
    public static function close()
    {
        if (self::$db) {
            self::$db->close();
        }
    }

    /**
     * Run a query and return the result
     * @param {string} query to run (with '?' for values)
     * @param {array} values to execute in prepared statement (optional)
     * @return {resource} result
     */
    public static function query($query, $objs = array())
    {
        if (!self::$db)
            self::connect();

        $objs = (array )$objs; //automagically cast single values into an array

        $statement = self::$db->prepare($query);

        if (!$statement) {
            throw new Exception('Query failed: ' . self::$db->error);
        }

        //go through all of the provided objects and bind them
        $types = array();
        $values = array();

        if (count($objs) > 0) {
            foreach ($objs as $obj) {
                //get the object type and translate it ready for bind parameter
                $type = gettype($obj);

                switch ($type) {
                    case 'boolean':
                    case 'integer':
                        $types[] = 'i';
                        $values[] = intval($obj);
                        break;
                    case 'double':
                        $types[] = 'd';
                        $values[] = doubleval($obj);
                        break;
                    case 'string':
                        $types[] = 's';
                        $values[] = (string )$obj;
                        break;
                    case 'array':
                    case 'object':
                        $paramTypes[] = 's';
                        $values[] = json_encode($obj);
                        break;
                    case 'resource':
                    case 'null':
                    case 'unknown type':
                    default:
                        throw new Exception('Unsupported object passed through as query prepared object!');
                }
            }

            $params = makeRefArr($values);
            array_unshift($params, implode('', $types));
            call_user_func_array(array($statement, 'bind_param'), $params);
        }

        if (!$statement->execute()) {
            echo self::$db->error;
            return null;
        } else {
            $statement->store_result();
            return $statement;
        }
    }

    /**
     * Determine if an object exists
     * @param {string} query to run
     * @param {array} objects to use in prepare query (optional)
     * @return {boolean} object exists in database
     */
    public static function objectExists($query, $objs = array())
    {
        $statement = self::query($query, $objs);

        return (is_object($statement) && $statement->num_rows > 0);
    }

    /**
     * Make an associative array of field names from a statement
     * @param {resource} mysqli statement
     * @return {array} field names array
     */
    private static function getFieldNames($statement)
    {
        $result = $statement->result_metadata();
        $fields = $result->fetch_fields();

        $fieldNames = array();
        foreach ($fields as $field) {
            $fieldNames[$field->name] = null;
        }

        return $fieldNames;
    }

    /**
     * Get an object from a query
     * @param {string} query to execute
     * @param {array} objects to use as the values (optional) 
     * @return {assoc} sinulatobject
     */
    public static function getObject($query, $objs = array())
    {
        $statement = self::query($query, $objs);

        if (!is_object($statement) || $statement->num_rows < 1) {
            return null;
        }

        $fieldNames = self::getFieldNames($statement);
        call_user_func_array(array($statement, 'bind_result'), makeRefArr($fieldNames));

        $statement->fetch();
        $statement->close();

        return $fieldNames;
    }

    /**
     * Get a list of objects from the database
     * @param {string} query
     * @return {array} objects
     */
    public static function getObjects($query, $objs = array())
    {
        $statement = self::query($query, $objs);

        if (!is_object($statement) || $statement->num_rows < 1) {
            return array();
        }

        $fieldNames = self::getFieldNames($statement);
        call_user_func_array(array($statement, 'bind_result'), makeRefArr($fieldNames));

        $results = array();
        while ($statement->fetch()) {
            $results[] = array_copy($fieldNames);
        }

        $statement->close();

        return $results;
    }

    /**
     * Get all of the data from a table
     * @param {string} table name
     * @return {array} table data
     */
    public static function getTable($tableName)
    {
        if (!self::$db)
            self::connect();

        $tableName = self::$db->escape_string($tableName);

        return self::getObjects('SELECT * FROM `' . $tableName . '`;');
    }

    /**
     * Get a field from a table based on a field having a specific value
     * @param {string} table name
     * @param {string} field name
     * @param {mixed} field value
     * @return {array} table row data
     */
    public static function getTableRow($tableName, $field, $value)
    {
        if (!self::$db)
            self::connect();

        $tableName = self::$db->escape_string($tableName);
        $field = self::$db->escape_string($field);

        return self::getObject('SELECT * FROM `' . $tableName . '` WHERE `' . $field .
            '` = ? LIMIT 1;', $value);
    }

    /**
     * Get all related rows from a table based on a field having a specific value
     * @param {string} table name
     * @param {string} field name
     * @param {mixed} field value
     * @return {array} table row data
     */
    public static function getTableRows($tableName, $field, $value, $sortField = null,
        $sortDesc = false)
    {
        if (!self::$db)
            self::connect();

        $tableName = self::$db->escape_string($tableName);
        $field = self::$db->escape_string($field);

        if ($sortField == null) {
            $sortField = $field;
        } else {
            $sortField = self::$db->escape_string($sortField);
        }

        return self::getObjects('SELECT * FROM `' . $tableName . '` WHERE `' . $field .
            '` = ? ORDER BY `' . $sortField . '` ' . ($sortDesc ? 'DESC' : 'ASC') . ';', $value);
    }
}
?>