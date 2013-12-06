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


    public function __construct($dbType) {
        $this->dbType = $dbType;
        $databasetypename = 'c' . ucfirst($this->dbType);
        include_once($databasetypename . '.php');
        $this->dbObj = new $databasetypename();
    }

}
