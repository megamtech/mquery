<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of global_db
 *
 * @author gt
 */
require_once 'cModel.php';

Class cDatabase implements cModel {

    public $dbObj;
    public $dbType;
    public $table;
    public $condition;
    public $column;

    public function __construct($newDatabaseInfo = array()) {
        if (!$this->dbObj) {

            if ($newDatabaseInfo['type'] == 'mongo') {

                require_once 'cNoSql.php';
                $this->dbObj = new cNoSql($newDatabaseInfo);
            } else {
                require_once 'cSql.php';

                $this->dbObj = new cSql($newDatabaseInfo);
            }
        }
    }

    public function create() {

        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;

        return $this->dbObj->create();
    }

    public function update() {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;

        return $this->dbObj->update();
    }

    public function count() {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;

        return $this->dbObj->count();
    }

    public function distinct() {
        $this->dbObj->table = $this->table;

        $this->dbObj->column = $this->column;

        return $this->dbObj->distinct();
    }

    public function read() {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;

        return $this->dbObj->read();
    }

    public function delete() {
        $this->dbObj->table = $this->table;
        return $this->dbObj->delete();
    }

    public function drop() {
        $this->dbObj->table = $this->table;
        return $this->dbObj->drop();
    }

    public function aggregate() {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;
        return $this->dbObj->aggregate();
    }

    function createTable() {
        $this->dbObj->table = $this->table;
        return $this->dbObj->createTable();
    }

    function createMultiple() {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;
        return $this->dbObj->createMultiple();
    }

    public function addOrderBy($orderby) {
        $this->dbObj->addOrderBy($orderby);
        return $this;
    }

    public function addLimit($limit) {
        $this->dbObj->addLimit($limit);
        return $this;
    }

    public function addOffset($offset) {
        $this->dbObj->addOffset($offset);
        return $this;
    }

    public function addGroupBy($groupby) {
        $this->dbObj->addGroupBy($groupby);
        return $this;
    }

    public function addWhereCondition($condition = array()) {
        $this->dbObj->addWhereCondition($condition);
        return $this;
    }
    public function getNextSequence($sequence_name){
        return $this->dbObj->getNextSequence($sequence_name);
         
    }

}

?>
