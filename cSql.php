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
class cSql {

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
    public $lastsql = "";
    public $dbType = "";
    public $result;

    public function __construct($newDatabaseInfo) {
$this->dbType = $newDatabaseInfo['type'];
        $databasetypename = 'c' . ucfirst($this->dbType);
        include_once($databasetypename . '.php');
        $this->dbObj = new $databasetypename($newDatabaseInfo);
    }

    /**
     * @assert ($this->query) == ""
     */
    function resetQuery() {
        if ($this->debug) {
            $this->lastsql = $this->query;
        }
        unset($this->column, $this->parent_only, $this->table, $this->join_condition, $this->condition, $this->group_by, $this->having, $this->order_by, $this->limit, $this->offset_by, $this->sub_query, $this->exclude_columns);
    }

    public function create() {

        $this->query = "INSERT INTO " . $this->table;
        $this->column = is_object($this->column) ? (array) $this->column : $this->column;
        $this->query.= ( $this->column) ? " (`" . implode('`,`', array_keys($this->column)) . "`) VALUES ('" . implode("','", array_values($this->column)) . "')" : "";
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

    public function read() {
        //$this->addWhereCondition()->addHaving()->addGroupBy()->addOrderBy()->addLimit()->addOffsetBy();
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
        $this->dbObj->sql=  $this->query;
        $this->result = $this->dbObj->read();
//        $this->debug=true;exit;
        $this->resetQuery();
        return $this->result;
    }

    public function update() {
        $this->column = is_object($this->column) ? (array) $this->column : $this->column;
        $this->query = "UPDATE " . $this->parent_only . " " . $this->table . " SET " . implode(",", $this->column) . "" . $this->condition;

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
        
        foreach($condition as $columnname=>$value){
        $this->condition[]=$columnname."='".$value."'";
        }

        $this->condition = (is_array($this->condition)) ? " WHERE " . implode(' AND ', array_filter($this->condition)): "";

        return $this;
    }

    public function addGroupBy() {
        $this->group_by = ($this->group_by) ? " GROUP BY " . ((is_array($this->group_by)) ? implode(', ', array_filter($this->group_by)) : $this->group_by) : "";
        return $this;
    }

    public function addHaving() {
        $this->having = ($this->having) ? " HAVING " . ((is_array($this->having)) ? implode(' AND ', $this->having) : $this->having) : "";
        return $this;
    }

    public function addOrderBy() {

        $this->order_by = ($this->order_by) ? " ORDER BY " . ((is_array($this->order_by)) ? implode(', ', array_filter($this->order_by)) : $this->order_by) : "";
        return $this;
    }

    public function addLimit() {
        $this->limit = ($this->limit) ? " LIMIT " . $this->limit : "";
        return $this;
    }

    public function addOffsetBy() {

        $this->offset_by = ($this->offset_by) ? " OFFSET " . $this->offset_by : "";
        return $this;
    }

    function executeRead() {
        $this->dbObj->sql = $this->query;
        $this->debug = false;
        return $this->dbObj->read();
    }

    function executeWrite() {
        $this->dbObj->sql = $this->query;
        $this->debug = false;
        return $this->dbObj->write();
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
