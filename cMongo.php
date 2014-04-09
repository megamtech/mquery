<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//Connect MongoDB



class cMongo
{

    public $connection;
    //collection
    public $table;
    //fields
    public $column;
    //DB
    public $db;
    public $result;

    public function __construct($newDatabaseInfo)
    {
        $this->getConnection($newDatabaseInfo);
    }

    public function getConnection($newDatabaseInfo)
    {
        if (!$this->db) {
            $this->connection = new MongoClient('mongodb://' . $newDatabaseInfo['host'] . ':' . $newDatabaseInfo['port']);
            $this->db = $this->connection->{$newDatabaseInfo['name']};
        }
    }

    public function create()
    {
        $this->db->{$this->table}->insert($this->column);
    }

    public function delete()
    {
        $this->db->{$this->table}->remove($this->condition);
    }

    public function read()
    {
        $this->db->{$this->table}->find($this->condition, $this->column)->sort($this->orderby)->limit($this->limit)->skip($this->offset);
    }

    public function update()
    {
        $this->db->{$this->table}->update($this->condition, array('$set' => $this->column), array('multiple' => true));
        $this->result = "";
    }

    public function addWhereCondition($condition)
    {

        return $this;
    }

}

?>
