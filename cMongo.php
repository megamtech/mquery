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
    public $orderby;
    public $limit;
    public $offset;
//DB
    public $db;
    public $result;
    private $cursor;

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
        $this->result = $this->db->{$this->table}->insert($this->column);
    }

    public function delete() {
        $this->result = $this->db->{$this->table}->remove($this->condition);
    }

    public function read() {
        $this->cursor = $this->db->{$this->table}->find($this->condition, $this->column);
        $this->addOrderby();
        $this->addLimit();
        $this->addOffset();
        foreach ($this->cursor as $doc) {
            $this->result[] = $doc;
        }
    }

    public function update() {
        $this->result = $this->db->{$this->table}->update($this->condition, $this->column);
    }

    private function addOrderby() {
        if ($this->orderby) {
            $this->cursor->sort($this->orderby);
        }
    }

    private function addLimit() {
        if ($this->limit) {
            $this->cursor->limit($this->limit);
        }
    }

    private function addOffset() {
        if ($this->offset) {
            $this->cursor->skip($this->offset);
        }
    }

}
?>