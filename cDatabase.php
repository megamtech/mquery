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
include 'cModel.php';

Class cDatabase implements cModel
{

    public $dbObj;
    public $dbType;
    public $table;
    public $condition;
    public $column;

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

    public function create()
    {

        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;

        return $this->dbObj->create();
    }

    public function update()
    {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;

        return $this->dbObj->update();
    }

    public function read()
    {
        $this->dbObj->table = $this->table;
        $this->dbObj->column = $this->column;

        return $this->dbObj->read();
    }

    public function delete()
    {
        $this->dbObj->table = $this->table;
        return $this->dbObj->delete();
    }

    public function addOrderBy($orderby)
    {
        $this->dbObj->addOrderBy($orderby);
        return $this;
    }

    public function addLimit($limit)
    {
        $this->dbObj->addOffset($offset);
    }

    public function addOffset($offset)
    {
        $this->dbObj->addOffset($offset);
    }

    public function addWhereCondition($condition = array())
    {

        $this->dbObj->addWhereCondition($condition);
        return $this;
    }

}

?>
