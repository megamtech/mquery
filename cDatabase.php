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
Class cDatabase {

    public $dbObj;

    public function __construct($DataBaseType = "") {
        if (!$this->dbObj) {
            $this->dbType = $DataBaseType ? $DataBaseType : DataBaseType;
            $databasetypename = 'c' . ucfirst($this->dbType);
            include_once($databasetypename . '.php');
            $this->dbObj = new $databasetypename();
        }
    }

}

?>
