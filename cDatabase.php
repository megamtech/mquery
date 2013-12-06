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

    public function __construct($dbType) {
        echo $this->dbType=$dbType;
        if (!$this->dbObj) {

            if ($this->dbType != 'mongo') {

                include 'cSql.php';
                $this->dbObj = new cSql($this->dbType);
            } else {
                include 'cNoSql.php';
                $nosql=new cNoSql($this->dbType);
                $this->dbObj =$nosql->dbObj ;
            }
        }
    }

}

?>
