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
include 'cModel.php';

class cNoSql implements cModel
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

    function read()
    {
        return $this->dbObj->read();
    }

    function create()
    {
        return $this->dbObj->create();
    }

    function update()
    {
        return $this->dbObj->update();
    }

    function delete()
    {
        return $this->dbObj->update();
    }

    function addWhereCondition($condition)
    {
        return $this->dbObj->addWhereCondition($condition);
    }

}
