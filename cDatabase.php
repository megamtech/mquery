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
Class cDatabase
{

    public $dbObj;
    public $dbType;

    public function __construct($newDatabaseInfo = array())
    {

        if (!$this->dbObj) {

            if ($newDatabaseInfo['type'] == '') {
                $this->dbType = DataBaseType;
                $newDatabaseInfo['type'] = DataBaseType;
                $newDatabaseInfo['host'] = DataBaseHost;
                $newDatabaseInfo['port'] = DataBasePort;
                $newDatabaseInfo['user'] = DataBaseUser;
                $newDatabaseInfo['pass'] = DataBasePass;
                $newDatabaseInfo['name'] = DataBaseName;
            }
            if ($this->dbType != 'mongo') {

                include 'cSql.php';

                $this->dbObj = new cSql($newDatabaseInfo);
            } else {
                include 'cNoSql.php';

                $this->dbObj = new cNoSql($newDatabaseInfo);
            }
            $this->dbObj->dbType = $this->dbType;
        }
    }

}

?>
