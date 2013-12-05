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
    public $dbType;

    public function __construct() {
        if (!$this->dbObj) {
            if ($this->dbType != 'mongodb') {

                include 'cSql.php';
                $this->dbObj = new cSql($this->dbType);
            } else {
                include 'cNoSql.php';
                $this->dbObj = new cNoSql($this->dbType);
            }
        }
    }

}

?>
