<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cNoSql
 *
 * @author Admin
 */
class cNoSql {

    public $dbType;
    public $dbObj;
    public $table;
    public $column;

    public function __construct($newDatabaseInfo) {
        $this->dbType = $newDatabaseInfo['type'];
        $databasetypename = 'c' . ucfirst($this->dbType);
        include_once($databasetypename . '.php');
        $this->dbObj = new $databasetypename($newDatabaseInfo);
    }

    function read() {
        $this->dbObj->table = $this->table;
        $this->dbObj->join_condition = $this->join_condition;
        $this->column = $this->column;

        return $this->dbObj->read();
    }

    function create() {
        return $this->dbObj->create();
    }

    function update() {
        return $this->dbObj->update();
    }

    function delete() {
        return $this->dbObj->update();
    }

    function addWhereCondition($condition) {
        $this->dbObj->addWhereCondition($condition);
        return $this;
    }

    public function addOrderBy($orderby) {

    }

    public function addLimit($limit) {

    }

    public function addOffset($offset) {

    }

}
