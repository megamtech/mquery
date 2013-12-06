<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//Connect MongoDB

include 'cModel.php';

class cMongo implements cModel {

    public $connection;
    //collection
    public $table;
    //fields
    public $column;
    //DB
    public $db;
    public $result;

    public function __construct() {
        $this->getConnection();
    }

    public function getConnection() {
        if (!$this->db) {
            $this->connection = new MongoClient('mongodb://' . DataBaseHost . ':' . DataBasePort);
            $this->db = $this->connection->{DataBaseName};
        }
    }

    public function create() {
        $this->db->{$this->table}->insert($this->column);
    }

    public function delete() {
        $this->db->{$this->table}->remove($this->condition, $justone);
    }

    public function read() {
        $this->db->{$this->table}->find($this->condition, array('$set'=>$this->column),array('multiple'=>true))->sort($this->orderby)->limit($this->limit)->skip($this->offset);
    }

    public function update() {
        $this->db->{$this->table}->update($this->condition, $this->column);
        $this->result = "";
    }

}

?>