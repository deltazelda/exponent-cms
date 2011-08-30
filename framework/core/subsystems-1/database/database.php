<?php
/**
 *  This file is part of Exponent
 *  Exponent is free software; you can redistribute
 *  it and/or modify it under the terms of the GNU
 *  General Public License as published by the Free
 *  Software Foundation; either version 2 of the
 *  License, or (at your option) any later version.
 *
 * The file that holds the database class
 *
 * @link http://www.gnu.org/licenses/gpl.txt GPL http://www.gnu.org/licenses/gpl.txt
 * @package Exponent-CMS
 */
/**
 * This is the class database
 *
 * This is the generic implementation of the database class.
 * @copyright 2004-2011 OIC Group, Inc.
 * @author Written and Designed by James Hunt
 * @version 2.0.0
 * @subpackage Database = database
 * @package Subsystems
 */
abstract class database {

    /**
     *
     * @var string $connection Database connection string
     */
    var $connection = null;
    var $havedb = false;
    /**
     *
     * @var string $prefix Database prefix
     */
    var $prefix = "";

    /**
     * Make a connection to the Database Server
     *
     * Takes the supplied credentials (username / password) and tries to
     * connect to the server and select the given database.  All the rules
     * governing database connect also govern this method.
     *
     * This must be called before any other methods of database are invoked.
     *
     * @param string $username The username to connect to the server as.
     * @param string $password The password for $username
     * @param string $hostname The hostname of the database server.  If
     *   localhost is specified, a local socket connection will be attempted.
     * @param string $database The name of the database to use.  Multi-database
     *   sites are still not yet supported.
     * @param bool $new Whether or not to force the PHP connection function to establish
     *   a distinctly new connection handle to the server.
     */

	function connect ($username, $password, $hostname, $database, $new=false) {
	}

    /**
     * Create a new Table
     *
     * Creates a new database table, according to the passed data definition.
     *
     * This function abides by the Exponent Data Definition Language, and interprets
     * its general structure for databases.
     *
     * @param string $tablename The name of the table to create
     * @param array $datadef The data definition to create, expressed in
     *   the Exponent Data Definition Language.
     * @param array $info Information about the table itself.
     * @return array
	 */
	function createTable($tablename,$datadef,$info) {
    }

    /**
     * This is an internal function for use only within the database class
     * @internal Internal
     * @param  $name
     * @param  $def
     * @return bool|string
     */
    function fieldSQL($name, $def) {
        $sql = "`$name`";
        if (!isset($def[DB_FIELD_TYPE])) {
            return false;
        }
        $type = $def[DB_FIELD_TYPE];
        if ($type == DB_DEF_ID) {
            $sql .= " INT(11)";
        } else if ($type == DB_DEF_BOOLEAN) {
            $sql .= " TINYINT(1)";
        } else if ($type == DB_DEF_TIMESTAMP) {
            $sql .= " INT(14)";
        } else if ($type == DB_DEF_INTEGER) {
            $sql .= " INT(8)";
        } else if ($type == DB_DEF_STRING) {
            if (isset($def[DB_FIELD_LEN]) && is_int($def[DB_FIELD_LEN])) {
                $len = $def[DB_FIELD_LEN];
                if ($len < 256)
                    $sql .= " VARCHAR($len)";
                else if ($len < 65536)
                    $sql .= " TEXT";
                else if ($len < 16777216)
                    $sql .= " MEDIUMTEXT";
                else
                    $sql .= "LONGTEXT";
            } else {
                return false; // must specify a field length as integer.  //FIXME need to have a default
            }
        } else if ($type == DB_DEF_DECIMAL) {
            $sql .= " DOUBLE";
        } else {
            return false; // must specify known FIELD_TYPE
        }
        $sql .= " NOT NULL";
        if (isset($def[DB_DEFAULT]))
            $sql .= " DEFAULT '" . $def[DB_DEFAULT] . "'";

        if (isset($def[DB_INCREMENT]) && $def[DB_INCREMENT])
            $sql .= " AUTO_INCREMENT";
        return $sql;
    }

    /**
     * Switch field values between two entries in a  Table
     *
     * Switches values between two table entries for things like swapping rank, etc...
     * @param  $table
     * @param  $field
     * @param  $a
     * @param  $b
     * @param null $additional_where
     * @return bool
     */
    function switchValues($table, $field, $a, $b, $additional_where = null) {
        if ($additional_where == null) {
            $additional_where = '1';
        }
        $object_a = $this->selectObject($table, "$field='$a' AND $additional_where");
        $object_b = $this->selectObject($table, "$field='$b' AND $additional_where");

        if ($object_a && $object_b) {
            $tmp = $object_a->$field;
            $object_a->$field = $object_b->$field;
            $object_b->$field = $tmp;

            $this->updateObject($object_a, $table);
            $this->updateObject($object_b, $table);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks to see if the connection for this database object is valid.
     * @return bool True if the connection can be used to execute SQL queries.
     */
    function isValid() {
        return ($this->connection != null && $this->havedb);
    }

    /**
     * Test the privileges of the user account for the connection.
     * Tests run include:
     * <ul>
     * <li>CREATE TABLE</li>
     * <li>INSERT</li>
     * <li>SELECT</li>
     * <li>UPDATE</li>
     * <li>DELETE</li>
     * <li>ALTER TABLE</li>
     * <li>DROP TABLE</li>
     * </ul>
     * These tests must be performed in order, for logical reasons.  Execution
     * terminates when the first test fails, and the status flag array is returned then.
     * Returns an array of status flags.  Key is the test name.  Value is a boolean,
     * true if the test succeeded, and false if it failed.
     * @return array
     */
    function testPrivileges() {

        $status = array();

        $tablename = "___testertable" . uniqid("");
        $dd = array(
            "id" => array(
                DB_FIELD_TYPE => DB_DEF_ID,
                DB_PRIMARY => true,
                DB_INCREMENT => true),
            "name" => array(
                DB_FIELD_TYPE => DB_DEF_STRING,
                DB_FIELD_LEN => 100)
        );

        $this->createTable($tablename, $dd, array());
        if (!$this->tableExists($tablename)) {
            $status["CREATE TABLE"] = false;
            return $status;
        } else
            $status["CREATE TABLE"] = true;

        $o = null;
        $o->name = "Testing Name";
        $insert_id = $this->insertObject($o, $tablename);
        if ($insert_id == 0) {
            $status["INSERT"] = false;
            return $status;
        } else
            $status["INSERT"] = true;

        $o = $this->selectObject($tablename, "id=" . $insert_id);
        if ($o == null || $o->name != "Testing Name") {
            $status["SELECT"] = false;
            return $status;
        } else
            $status["SELECT"] = true;

        $o->name = "Testing 2";
        if (!$this->updateObject($o, $tablename)) {
            $status["UPDATE"] = false;
            return $status;
        } else
            $status["UPDATE"] = true;

        $this->delete($tablename, "id=" . $insert_id);
        $o = $this->selectObject($tablename, "id=" . $insert_id);
        if ($o != null) {
            $status["DELETE"] = false;
            return $status;
        } else
            $status["DELETE"] = true;

        $dd["thirdcol"] = array(
            DB_FIELD_TYPE => DB_DEF_TIMESTAMP);

        $this->alterTable($tablename, $dd, array());
        $o = null;
        $o->name = "Alter Test";
        $o->thirdcol = "Third Column";
        if (!$this->insertObject($o, $tablename)) {
            $status["ALTER TABLE"] = false;
            return $status;
        } else
            $status["ALTER TABLE"] = true;

        $this->dropTable($tablename);
        if ($this->tableExists($tablename)) {
            $status["DROP TABLE"] = false;
            return $status;
        } else
            $status["DROP TABLE"] = true;

        foreach ($this->getTables() as $t) {
            if (substr($t, 0, 14 + strlen($this->prefix)) == $this->prefix . "___testertable")
                $this->dropTable($t);
        }

        return $status;
    }

    /**
     * Alter an existing table
     *
     * Alters the structure of an existing database table to conform to the passed
     * data definition.
     *
     * This function abides by the Exponent Data Definition Language, and interprets
     * its general structure for databases.
     *
     * @param string $tablename The name of the table to alter
     * @param array $newdatadef The new data definition for the table.
     *   This is expressed in the Exponent Data Definition Language
     * @param array $info Information about the table itself.
     * @param bool $aggressive Whether or not to aggressively update the table definition.
     *   An aggressive update will drop columns in the table that are not in the Exponent definition.
     * @return array
     */
    function alterTable($tablename, $newdatadef, $info, $aggressive = false) {
    }

    /**
     * Drop a table from the database
     *
     * Removes an existing table from the database. Returns true if the table was dropped, false if there
     * was an error returned by the database server.
     *
     * @param string $table The name of the table to drop.
     * @return bool
     */
    function dropTable($table) {
    }

    /**
     * Run raw SQL.  Returns true if the query succeeded, and false
     *   if an error was returned from the database server.
     *
     * <div style="color:red">If you can help it, do not use this function.  It presents Database Portability Issues.</div>
     *
     * Runs a straight SQL query on the database.  This is not a
     * very portable way of dealing with the database, and is only
     * provided as a last resort.
     *
     * @param string $sql The SQL query to run
     * @return mixed
     */
    function sql($sql) {
    }

	/**
	 * Toggle a boolean value in a Table Entry
	 *
	 * @param  $table
	 * @param  $col
	 * @param null $where
	 * @return void
	 */
    function toggle($table, $col, $where=null) {
        $obj = $this->selectObject($table, $where);
        $obj->$col = ($obj->$col == 0) ? 1 : 0;
        $this->updateObject($obj, $table);
    }

	/**
	 * Update a column in all records in a table
	 *
	 * @param  $table
	 * @param  $col
	 * @param $val
	 * @param int|null $where
	 * @return void
	 */
    function columnUpdate($table, $col, $val, $where=1) {         
    }

	/**
	 * @param  $object
	 * @param  $table
	 * @param  $col
	 * @param int|null $where
	 * @return bool
	 */
    function setUniqueFlag($object, $table, $col, $where=1) {
        if (isset($object->id)) {
            $this->sql("UPDATE " . DB_TABLE_PREFIX . "_" . $table . " SET " . $col . "=0 WHERE " . $where);
            $this->sql("UPDATE " . DB_TABLE_PREFIX . "_" . $table . " SET " . $col . "=1 WHERE id=" . $object->id);
            return true;
        }
        return false;
    }

    /**
     * Select a series of objects
     *
     * Selects a set of objects from the database.  Because of the way
     * Exponent handles objects and database tables, this is akin to
     * SELECTing a set of records from a database table.  Returns an
     * array of objects, in any random order.
     *
     * @param string $table The name of the table/object to look at
     * @param string $where Criteria used to narrow the result set.  If this
     *   is specified as null, then no criteria is applied, and all objects are
     *   returned
     * @param null $orderby
     * @return array
     */
    function selectObjects($table, $where = null, $orderby = null) {
    }

	/**
	 * @param  $terms
	 * @param null $where
	 * @return array
	 */
    function selectSearch($terms, $where = null) {
    }

	/**
	 * @param null $colsA
	 * @param null $colsB
	 * @param  $tableA
	 * @param  $tableB
	 * @param  $keyA
	 * @param null $keyB
	 * @param null $where
	 * @param null $orderby
	 * @return array'
	 */
    function selectAndJoinObjects($colsA=null, $colsB=null, $tableA, $tableB, $keyA, $keyB=null, $where = null, $orderby = null) {
    }

	/**
	 * @param  $sql
	 * @return null|void
	 */
    function selectObjectBySql($sql) {
    }

	/**
	 * @param  $sql
	 * @return array
	 */
    function selectObjectsBySql($sql) {
    }

	/**
	 * @param  $table
	 * @param  $col
	 * @param null $where
	 * @param null $orderby
	 * @param bool $distinct
	 * @return array
	 */
    function selectColumn($table, $col, $where = null, $orderby = null, $distinct=false) {
    }

	/**
	 * @param  $table
	 * @param  $col
	 * @param null $where
	 * @return int
	 */
    function selectSum($table, $col, $where = null) {
    }

	/**
	 * @param  $table
	 * @param  $col
	 * @param null $where
	 * @param null $orderby
	 * @return array
	 */
    function selectDropdown($table, $col, $where = null, $orderby = null) {
    }

	/**
	 * @param  $table
	 * @param  $col
	 * @param null $where
	 * @return null
	 */
    function selectValue($table, $col, $where=null) {
    }

	/**
	 * @param  $sql
	 * @return null
	 */
    function selectValueBySql($sql) {
    }

    /**
     * This function takes an array of indexes and returns an array with the objects associated with each id
     * @param  $table
     * @param array $array
     * @param null $orderby
     * @return array
     */
    function selectObjectsInArray($table, $array=array(), $orderby=null) {
        $where = 'id IN ' . implode(",", $array);
        $res = $this->selectObjects($table, $where, $orderby);
        return $res;
    }

    /**
     * Select a series of objects, and return by ID
     *
     * Selects a set of objects from the database.  Because of the way
     * Exponent handles objects and database tables, this is akin to
     * SELECTing a set of records from a database table. Returns an
     * array of objects, in any random order.  The indices of the array
     * are the IDs of the objects.
     *
     * @param string $table The name of the table/object to look at
     * @param string $where Criteria used to narrow the result set.  If this
     *   is specified as null, then no criteria is applied, and all objects are
     *   returned
     * @param null $orderby
     * @return array
     */
    function selectObjectsIndexedArray($table, $where = null, $orderby = null) {
    }

    /**
     * Count Objects matching a given criteria
     *
     * @param string $table The name of the table to count objects in.
     * @param string $where Criteria for counting.
     * @return int
     */
    function countObjects($table, $where = null) {
    }

    /**
     * Count Objects matching a given criteria using raw sql
     *
     * @param string $sql The sql query to be run
     * @return int
     */
    function countObjectsBySql($sql) {
    }

    /**
     * Count Objects matching a given criteria using raw sql
     *
     * @param string $sql The sql query to be run
     * @return int|void
     */
    function queryRows($sql) {
    }

    /**
     * Select a single object.
     *
     * Selects an objects from the database.  Because of the way
     * Exponent handles objects and database tables, this is akin to
     * SELECTing a single record from a database table. Returns the
     * first record/object found (in the case of multiple-result queries,
     * there is no way to determine which of the set will be returned).
     * If no record(s) match the query, null is returned.
     *
     * @param string $table The name of the table/object to look at
     * @param string $where Criteria used to narrow the result set.
     * @return null|void
     */
    function selectObject($table, $where) {
    }

	/**
	 * @param $table
	 * @param string $lockType
	 * @return mixed
	 */
	function lockTable($table,$lockType="WRITE") {
    }

	/**
	 * @return mixed
	 */
	function unlockTables() {
    }
    
	/**
     * Insert an Object into some table in the Database
     *
     * This method will return the ID assigned to the new record by database.  Note that
     * object attributes starting with an underscore ('_') will be ignored and NOT inserted
     * into the table as a field value.
     *
     * @param Object $object The object to insert.
     * @param string $table The logical table name to insert into.  This does not include the table prefix, which
     *    is automagically prepended for you.
     * @return int|void
     */
    function insertObject($object, $table) {
    }

    /**
     * Delete one or more objects from the given table.
     *
     * @param string $table The name of the table to delete from.
     * @param string $where Criteria for determining which record(s) to delete.
     * @return mixed
     */
    function delete($table, $where = null) {
    }

    /**
     * Update one or more objects in the database.
     *
     * This function will only update the attributes of the resulting record(s)
     * that are also member attributes of the $object object.
     *
     * @param object $object An object specifying the fields and values for updating.
     *    In most cases, this will be the altered object originally returned from one of
     *    the select* methods.
     * @param string $table The table to update in.
     * @param string $where Optional criteria used to narrow the result set.
     * @param string $identifier
     * @param bool $is_revisioned
     * @return bool|int|void
     */
    function updateObject($object, $table, $where=null, $identifier='id', $is_revisioned=false) {
    }

	/**
	 * Find the maximum value of a field.  This is similar to a standard
	 * SELECT MAX(field) ... query.
	 *
	 * @param string $table The name of the table to select from.
	 * @param string $attribute The attribute name to find a maximum value for.
	 * @param string $groupfields A comma-separated list of fields (or a single field) name, used
	 *    for a GROUP BY clause.  This can also be passed as an array of fields.
	 * @param string $where Optional criteria for narrowing the result set.
	 * @return mixed
	 */
    function max($table, $attribute, $groupfields = null, $where = null) {
    }

	/**
	 * Find the minimum value of a field.  This is similar to a standard
	 * SELECT MIN(field) ... query.
	 *
	 * @internal Internal
	 * @param string $table The name of the table to select from.
	 * @param string $attribute The attribute name to find a minimum value for.
	 * @param string $groupfields A comma-separated list of fields (or a single field) name, used
	 *    for a GROUP BY clause.  This can also be passed as an array of fields.
	 * @param $where Optional criteria for narrowing the result set.
	 * @return null
	 */
    function min($table, $attribute, $groupfields = null, $where = null) {
    }

    /**
     * Increment a numeric table field in a table.
     *
     * @param string $table The name of the table to increment in.
     * @param string $field The field to increment.
     * @param integer $step The step value.  Usually 1.  This can be negative, to
     *    decrement, but the decrement() method is prefered, for readability.
     * @param string $where Optional criteria to determine which records to update.
     * @return mixed
     */
    function increment($table, $field, $step, $where = null) {
    }

    /**
     * Decrement a numeric table field in a table.
     *
     * @param string $table The name of the table to decrement in.
     * @param string $field The field to decrement.
     * @param integer $step The step value.  Usually 1.  This can be negative, to
     *    increment, but the increment() method is prefered, for readability.
     * @param string $where Optional criteria to determine which records to update.
     */

    function decrement($table, $field, $step, $where = null) {
        $this->increment($table, $field, -1 * $step, $where);
    }

    /**
     * Check to see if the named table exists in the database.
     * Returns true if the table exists, and false if it doesn't.
     *
     * @param string $table Name of the table to look for.
     * @return bool
     */
    function tableExists($table) {
    }

    /**
     * Get a list of all tables in the database.  Optionally, only the tables
     * in the corrent logcial database (tables with the same prefix) can
     * be retrieved.
     *
     * @param bool $prefixed_only Whether to return only the tables
     *    for the logical database, or all tables in the physical database.
     * @return array
     */
    function getTables($prefixed_only=true) {
    }

    /**
     * Runs whatever table optimization routines the database engine supports.
     *
     * @param string $table The name of the table to optimize.
     * @return bool
     */
    function optimize($table) {
    }

    /**
     * Retrieve table information for a named table.
     * Returns an object, with the following attributes:
     * <ul>
     * <li><b>rows</b> -- The number of rows in the table.</li>
     * <li><b>average_row_length</b> -- The average storage size of a row in the table.</li>
     * <li><b>data_total</b> -- How much total disk space is used by the table.</li>
     * <li><b>data_overhead</b> -- How much storage space in the table is unused (for compacting purposes)</li>
     * </ul>
     * @param  $table
     * @return null
     */
    function tableInfo($table) {
    }

    /**
     * Check whether or not a table in the database is empty (0 rows).
     * Returns tue of the specified table has no rows, and false if otherwise.
     *
     * @param string $table Name of the table to check.
     * @return bool
     */
    function tableIsEmpty($table) {
        return ($this->countObjects($table) == 0);
    }

    /**
     * Returns table information for all tables in the database.
     * This function effectively calls tableInfo() on each table found.
     * @return array
     */
    function databaseInfo() {
    }

    /**
     * This is an internal function for use only within the database database class
     * @internal Internal
     * @param  $status
     * @return null
     */
    function translateTableStatus($status) {
        $data = null;
        $data->rows = $status->Rows;
        $data->average_row_lenth = $status->Avg_row_length;
        $data->data_overhead = $status->Data_free;
        $data->data_total = $status->Data_length;

        return $data;
    }

	/**
	 * @param  $table
	 * @return array
	 */
    function describeTable($table) {
    }

    /**
     * Build a data definition from a pre-existing table.  This is used
     * to intelligently alter tables that have already been installed.
     *
     * @param string $table The name of the table to get a data definition for.
     * @return array|null
     */
    function getDataDefinition($table) {
    }

    /**
     * This is an internal function for use only within the database class
     * @internal Internal
     * @param  $fieldObj
     * @return int
     */
    function getDDFieldType($fieldObj) {
        $type = strtolower($fieldObj->Type);

        if ($type == "int(11)")
            return DB_DEF_ID;
        if ($type == "int(8)")
            return DB_DEF_INTEGER;
        elseif ($type == "tinyint(1)")
            return DB_DEF_BOOLEAN;
        elseif ($type == "int(14)")
            return DB_DEF_TIMESTAMP;
        //else if (substr($type,5) == "double") return DB_DEF_DECIMAL;
        elseif ($type == "double")
            return DB_DEF_DECIMAL;
        // Strings
        elseif ($type == "text" || $type == "mediumtext" || $type == "longtext" || strpos($type, "varchar(") !== false) {
            return DB_DEF_STRING;
        }
    }

    /**
     * This is an internal function for use only within the database class
     * @internal Internal
     * @param  $fieldObj
     * @return int|mixed
     */
    function getDDStringLen($fieldObj) {
        $type = strtolower($fieldObj->Type);
        if ($type == "text")
            return 65535;
        else if ($type == "mediumtext")
            return 16777215;
        else if ($type == "longtext")
            return 16777216;
        else if (strpos($type, "varchar(") !== false) {
            return str_replace(array("varchar(", ")"), "", $type) + 0;
        }
    }

    /**
     * Returns an error message from the database server.  This is intended to be
     * used by the implementers of the database wrapper, so that certain
     * cryptic error messages can be reworded.
     * @return string
     */
    function error() {
    }

    /**
     * Checks whether the database connection has experienced an error.
     * @return bool
     */
    function inError() {
    }

	/**
	 * Unescape a string based on the database connection
	 * @param $string
	 * @return string
	 */
	function escapeString($string) {
	}

	/**
	 * Create a SQL "limit" phrase
	 *
	 * @param  $num
	 * @param  $offset
	 * @return string
	 */
    function limit($num, $offset) {
        return ' LIMIT ' . $offset . ',' . $num . ' ';
    }

    /**
     * Select an array of arrays
     *
     * Selects a set of arrays from the database.  Because of the way
     * Exponent handles objects and database tables, this is akin to
     * SELECTing a set of records from a database table.  Returns an
     * array of arrays, in any random order.
     *
     * @param string $table The name of the table/object to look at
     * @param string $where Criteria used to narrow the result set.  If this
     *   is specified as null, then no criteria is applied, and all objects are
     *   returned
     * @param string $orderby
     * @return array
     */
    function selectArrays($table, $where = null, $orderby = null) {
    }

    /**
     * Select an array of arrays
     *
     * Selects a set of arrays from the database.  Because of the way
     * Exponent handles objects and database tables, this is akin to
     * SELECTing a set of records from a database table.  Returns an
     * array of arrays, in any random order.
     *
     * @param string $sql The name of the table/object to look at
     * @return array
     */
    function selectArraysBySql($sql) {        
    }

    /**
     * Select a record from the database as an array
     *
     * Selects a set of arrays from the database.  Because of the way
     * Exponent handles objects and database tables, this is akin to
     * SELECTing a set of records from a database table.  Returns an
     * array of arrays, in any random order.
     *
     * @param string $table The name of the table/object to look at
     * @param string $where Criteria used to narrow the result set.  If this
     *   is specified as null, then no criteria is applied, and all objects are
     *   returned
     * @param null $orderby
     * @param bool $is_revisioned
     * @return array|void
     */
    function selectArray($table, $where = null, $orderby = null, $is_revisioned=false) {
    }

	/**
	 * Select a records from the database
	 * @param string $table The name of the table/object to look at
	 * @param string $where Criteria used to narrow the result set.  If this
	 *   is specified as null, then no criteria is applied, and all objects are
	 *   returned
	 * @param  $classname
	 * @param bool $get_assoc
	 * @param bool $get_attached
	 * @param array $except
	 * @param bool $cascade_except

	 * @return array
	 */
    function selectExpObjects($table, $where=null, $classname, $get_assoc=true, $get_attached=true, $except=array(), $cascade_except=false) {
    }

    /**
     * @param string $sql The sql statement to run on the model/classname
     * @param string $classname Can be $this->baseclassname
     * Returns an array of fields
     * @param bool $get_assoc
     * @param bool $get_attached
     * @return array
     */
    function selectExpObjectsBySql($sql, $classname, $get_assoc=true, $get_attached=true) {
    }

	/**
	 * @param  $table
	 * @return array
	 */
    function selectNestedTree($table) {
        $sql = 'SELECT node.*, (COUNT(parent.sef_url) - 1) AS depth
			FROM `' . $this->prefix . $table . '` AS node,
			`' . $this->prefix . $table . '` AS parent
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			GROUP BY node.sef_url
			ORDER BY node.lft';
        return $this->selectObjectsBySql($sql);
    }
	
	function selectFormattedNestedTree($table) {
		$sql = "SELECT CONCAT( REPEAT( '&nbsp;&nbsp;&nbsp;', (COUNT(parent.title) -1) ), node.title) AS title, node.id 
				FROM " .$this->prefix . $table. " as node, " .$this->prefix . $table. " as parent 
				WHERE node.lft BETWEEN parent.lft and parent.rgt 
				GROUP BY node.title, node.id 
				ORDER BY node.lft";
				
		return $this->selectObjectsBySql($sql);
	}

	/**
	 * @param  $table
	 * @param  $start
	 * @param  $width
	 * @return void
	 */
    function adjustNestedTreeFrom($table, $start, $width) {
        $table = $this->prefix . $table;
        $this->sql('UPDATE `' . $table . '` SET rgt = rgt + ' . $width . ' WHERE rgt >=' . $start);
        $this->sql('UPDATE `' . $table . '` SET lft = lft + ' . $width . ' WHERE lft >=' . $start);
        //eDebug('UPDATE `'.$table.'` SET rgt = rgt + '.$width.' WHERE rgt >='.$start);
        //eDebug('UPDATE `'.$table.'` SET lft = lft + '.$width.' WHERE lft >='.$start);
    }

	/**
	 * @param  $table
	 * @param  $lft
	 * @param  $rgt
	 * @param  $width
	 * @return void
	 */
    function adjustNestedTreeBetween($table, $lft, $rgt, $width) {
        $table = $this->prefix . $table;
        $this->sql('UPDATE `' . $table . '` SET rgt = rgt + ' . $width . ' WHERE rgt BETWEEN ' . $lft . ' AND ' . $rgt);
        $this->sql('UPDATE `' . $table . '` SET lft = lft + ' . $width . ' WHERE lft BETWEEN ' . $lft . ' AND ' . $rgt);
        //eDebug('UPDATE `'.$table.'` SET rgt = rgt + '.$width.' WHERE rgt BETWEEN '.$lft.' AND '.$rgt);
        //eDebug('UPDATE `'.$table.'` SET lft = lft + '.$width.' WHERE lft BETWEEN '.$lft.' AND '.$rgt);
    }

	/**
	 * @param  $table
	 * @param null $node
	 * @return array
	 */
    function selectNestedBranch($table, $node=null) {
        if (empty($node))
            return array();

        $where = is_numeric($node) ? 'id=' . $node : 'title="' . $node . '"';
        global $db;
        $sql = 'SELECT node.*, 
                (COUNT(parent.title) - (sub_tree.depth + 1)) AS depth 
                FROM `' . $this->prefix . $table . '` AS node,
                `' . $this->prefix . $table . '` AS parent,
                `' . $this->prefix . $table . '` AS sub_parent,
                        (       SELECT node.*, (COUNT(parent.title) - 1) AS depth 
                                FROM `' . $this->prefix . $table . '` AS node,
                                `' . $this->prefix . $table . '` AS parent
                                WHERE node.lft BETWEEN parent.lft 
                                AND parent.rgt AND node.' . $where . '
                                GROUP BY node.title 
                                ORDER BY node.lft )
                AS sub_tree 
                WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt 
                AND sub_parent.title = sub_tree.title 
                GROUP BY node.title 
                ORDER BY node.lft;';

        return $this->selectObjectsBySql($sql);
    }

	/**
	 * @param  $table
	 * @param  $lft
	 * @param  $rgt
	 * @return void
	 */
    function deleteNestedNode($table, $lft, $rgt) {
        $table = $this->prefix . $table;

        $width = ($rgt - $lft) + 1;
        $this->sql('DELETE FROM `' . $table . '` WHERE lft BETWEEN ' . $lft . ' AND ' . $rgt);
        $this->sql('UPDATE `' . $table . '` SET rgt = rgt - ' . $width . ' WHERE rgt > ' . $rgt);
        $this->sql('UPDATE `' . $table . '` SET lft = lft - ' . $width . ' WHERE lft > ' . $rgt);
    }

	/**
	 * @param  $table
	 * @param null $node
	 * @return array
	 */
    function selectPathToNestedNode($table, $node=null) {
        if (empty($node))
            return array();

        $where = is_numeric($node) ? 'id=' . $node : 'title="' . $node . '"';
        $sql = 'SELECT parent.*
			FROM `' . $this->prefix . $table . '` AS node,
			`' . $this->prefix . $table . '` AS parent
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			AND node.' . $where . '
			ORDER BY parent.lft;';
        return $this->selectObjectsBySql($sql);
    }

	/**
	 * @param  $table
	 * @param null $node
	 * @return array
	 */
    function selectNestedNodeParent($table, $node=null) {
        if (empty($node))
            return array();

        $where = is_numeric($node) ? 'id=' . $node : 'title="' . $node . '"';
        $sql = 'SELECT parent.*
			FROM `' . $this->prefix . $table . '` AS node,
			`' . $this->prefix . $table . '` AS parent
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			AND node.' . $where . '
			ORDER BY parent.lft DESC
			LIMIT 1, 1;';
        $parent_array = $this->selectObjectsBySql($sql);
        return $parent_array[0];
    }

	/**
	 * @param  $table
	 * @param null $node
	 * @return array
	 */
    function selectNestedNodeChildren($table, $node=null) {
        if (empty($node))
            return array();

        $where = is_numeric($node) ? 'node.id=' . $node : 'node.title="' . $node . '"';
        $sql = '
			SELECT node.*, (COUNT(parent.title) - (sub_tree.depth + 1)) AS depth
			FROM ' . $this->prefix . $table . ' AS node,
				' . $this->prefix . $table . ' AS parent,
				' . $this->prefix . $table . ' AS sub_parent,
				(
					SELECT node.*, (COUNT(parent.title) - 1) AS depth
					FROM ' . $this->prefix . $table . ' AS node,
					' . $this->prefix . $table . ' AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND ' . $where . '
					GROUP BY node.title
					ORDER BY node.lft
				)AS sub_tree
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
				AND sub_parent.title = sub_tree.title
			GROUP BY node.title
			HAVING depth = 1
			ORDER BY node.lft;';
		$children = $this->selectObjectsBySql($sql);
        return $children;
	}
	
	/**
	 * This function returns all the text columns in the given table
	 * @param $table
	 * @return array
	 */
	function getTextColumns($table) {
	}

}

?>
