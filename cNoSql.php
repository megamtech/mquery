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
class cNoSql 
{

    public $dbType;
    public $dbObj;
    public $table;
    public $column;

    public function __construct($newDatabaseInfo)
    {
        $this->dbType = $newDatabaseInfo['type'];
        $databasetypename = 'c' . ucfirst($this->dbType);
        include_once($databasetypename . '.php');
        $this->dbObj = new $databasetypename($newDatabaseInfo);
    }

    private function __setValues()
    {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = is_array($this->column) ? $this->column : array();
    }

    function read()
    {
        $this->__setValues();
        return $this->dbObj->read();
    }

    function create()
    {
        $this->__setValues();
        return $this->dbObj->create();
    }

    function update()
    {
        $this->__setValues();
        return $this->dbObj->update();
    }

    function delete()
    {
        $this->__setValues();
        return $this->dbObj->delete();
    }

    function addWhereCondition($condition)
    {

        $this->dbObj->addWhereCondition($condition);
        return $this;
    }

    public function addOrderBy($orderby)
    {
        $this->dbObj->addOrderBy($orderby);
        return $this;
    }

    public function addLimit($limit)
    {
        $this->dbObj->addLimit($limit);
        return $this;
    }

    public function addOffset($offset)
    {
        $this->dbObj->addOffset($offset);
        return $this;
    }
     function createTable(){
         $this->dbObj->table = $this->table;
         
        return $this->dbObj->createTable();
    }

}
