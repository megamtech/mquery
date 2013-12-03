<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cModal
 *
 * @author gt
 */
include_once('cDatabase.php');

class cModel extends cDatabase {

    public $column; //what columns to be queried or set or deleted
    public $parent_only; //whether to restrict the query to only one table
    public $table;
    public $join_condition;
    public $condition;
    public $group_by;
    public $having;
    public $order_by;
    public $limit;
    public $offset_by;
    public $sub_query;
    public $query;
    public $exclude_columns = "";
    public $returning;
    public $debug = false;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @assert ($this->query) == ""
     */
    function resetQuery() {
        if ($this->debug) {
            echo $this->query;
        }
        unset($this->column, $this->parent_only, $this->table, $this->join_condition, $this->condition, $this->group_by, $this->having, $this->order_by, $this->limit, $this->offset_by, $this->sub_query, $this->exclude_columns);
    }

    public function create() {
        $foreignkeycolumns = $this->dbObj->getForeignKeyDetails($this->table);
        if (@is_array($foreignkeycolumns['columns'])) {
            foreach ($this->column as $columnname => $value) {
                if (array_key_exists($this->column[$columnname], $foreignkeycolumns['columns'])) {
                    $this->column[$columnname] = $this->column[$columnname] ? $this->column[$columnname] : "NULL";
                }
            }
        }
        $this->query = "INSERT INTO " . $this->table;
        $columnNames = '';

        foreach ($this->column as $columnName => $columnValue) {

            if ($columnValue != '' || $columnValue !== NULL) {
                $columnNames[$columnName] = $columnValue;
            }
        }
        $this->query.= ( $columnNames) ? " (`" . implode('`,`', array_keys($columnNames)) . "`) VALUES ('" . implode("','", array_values($columnNames)) . "')" : "";
        $this->query.= ( $this->returning) ? " RETURNING " . $this->returning : "";
        $this->query.= ( $this->sub_query) ? $this->sub_query : "";
        $this->query.=";";
        $this->resetQuery();
        return $this;
    }

    public function createmultiple() {

        $this->query = "INSERT INTO " . $this->table;
        foreach ($this->column as $key => $data) {
            $this->query.= " VALUES ('" . implode("','", array_values($data)) . "'),";
        }
        $this->query = rtrim($this->query, ",");
        $this->query.= ( $this->returning) ? " RETURNING " . $this->returning : "";
        $this->query.= ( $this->sub_query) ? $this->sub_query : "";
        $this->query.=";";
        $this->resetQuery();
        return $this;
    }

    public function select() {
        $this->query = "SELECT ";
        if (is_array($this->column) || is_object($this->column)) {
            $columns = array();
            if (is_object($this->column)) {

                foreach ($this->column as $key => $value) {
                    $columns[] = $key;
                }
                $this->column = $columns;
            }
            $this->column = implode(",", $this->column);
        }
        $this->query.= ( $this->column) ? $this->column : " * ";
        $this->query.=" FROM " . $this->parent_only . " " . $this->table . " ";
        $this->query.= ( $this->join_condition) ? " " . ((is_array($this->join_condition)) ? implode(' ', $this->join_condition) : $this->join_condition) : "";
        $this->query.=$this->condition . $this->group_by . $this->having . $this->order_by . $this->limit . $this->offset_by;
        $this->resetQuery();
        return $this;
    }

    public function update() {
        $columnNames = '';
        foreach ($this->column as $columnName => $columnValue) {

            if ($columnValue != '' || $columnValue !== NULL) {
                $columnNames[] = $columnName . " = '" . $columnValue . "'";
            }
        }
        $this->query = "UPDATE " . $this->parent_only . " " . $this->table . " SET " . implode(",", $columnNames) . "" . $this->condition;

        $this->resetQuery();
        return $this;
    }

    public function alter() {

        foreach ($this->column as $columnName => $columnValue) {

            if ($columnValue != '' || $columnValue !== NULL) {
                $columnNames[] = $columnName . " = '" . $columnValue . "'";
            }
        }
        $this->query = "ALTER TABLE " . $this->table . " SET " . implode(",", $columnNames) . "" . $this->condition;

        $this->resetQuery();
        return $this;
    }

    public function delete() {

        $this->query = "DELETE FROM " . $this->parent_only . " " . $this->table . $this->condition;
        $this->resetQuery();
        return $this;
    }

    public function addWhereCondition($condition) {

        $this->condition = ($condition) ? " WHERE " . ((is_array($condition)) ? implode(' AND ', array_filter($condition)) : $condition) : "";

        return $this;
    }

    public function addGroupBy($group_by) {
        $this->group_by = ($group_by) ? " GROUP BY " . ((is_array($group_by)) ? implode(', ', array_filter($group_by)) : $group_by) : "";
        return $this;
    }

    public function addHaving($having) {
        $this->having = ($having) ? " HAVING " . ((is_array($having)) ? implode(' AND ', $having) : $having) : "";
        return $this;
    }

    public function addOrderBy($order_by) {

        $this->order_by = ($order_by) ? " ORDER BY " . ((is_array($order_by)) ? implode(', ', array_filter($order_by)) : $order_by) : "";
        return $this;
    }

    public function addLimit($limit) {
        $this->limit = ($limit) ? " LIMIT " . $limit : "";
        return $this;
    }

    public function addOffsetBy($offset_by) {

        $this->offset_by = ($offset_by) ? " OFFSET " . $offset_by : "";
        return $this;
    }

    function executeRead() {
        $this->dbObj->sql = $this->query;
        $this->debug = false;
        $result = $this->dbObj->read();
        if ($result === false) {
            $_SESSION['lastaction']['error'] = $this->dbObj->error;
            //log_message('error', $this->dbObj->error, FALSE);
            if (AppEnvironment == 'Development') {
                echo $_SESSION['lastaction']['error'];
                exit;
            }
        }
        return $this->dbObj->read();
    }

    function executeWrite() {
        $this->dbObj->sql = $this->query;
        $this->debug = false;
        $result = $this->dbObj->write();
        if ($result === false) {
            $_SESSION['lastaction']['error'] = $this->dbObj->error;
            //log_message('error', $this->dbObj->error, FALSE);
            if (AppEnvironment == 'Development') {
                echo $_SESSION['lastaction']['error'];
                exit;
            }
        }
        return $result;
    }

    function getColumnDetails($table) {
        return $this->dbObj->getColumnDetails($table);
    }

    function getTableDetails($columns = "", $condition = "") {
        return $this->dbObj->getTableDetails($columns, $condition);
    }

    function getNextVal($seq_name) {
        return $this->dbObj->getNextVal($seq_name);
    }

    function getChildTables($table) {
        return $this->dbObj->getChildTables($table);
    }

}

?>
