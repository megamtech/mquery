<?php

/**
 * This Class is for MongoDB related operations like connect , create,update,read and remove/delete
 *
 * In this class we have the functions @todo Long Desc
 *
 * @example <br/>
 * $obj=new Obj(); //This is automatically called from cDatabase,Singleton obj so it will connect if the connection is not available <br>
 * $obj->read();//Will by default perform sort,limit,offset if the attribs are present<br>
 * $obj->update();<br>
 * $obj->create();<br>
 * $obj->remove();<br>
 *
 *
 */
class cMongo {

    public $connection;
    //collection
    public $table;
    private $condition;
    //fields
    public $column;
    //DB
    public $db;
    public $orderby;
    public $limit;
    public $offset;
    public $result;
    private $cursor;

    /**
     *
     * @var Mixed
     */
    public function __construct($newDatabaseInfo) {
        $this->getConnection($newDatabaseInfo);
    }

    public function getConnection($newDatabaseInfo) {
        if (!$this->db) {
            if ($newDatabaseInfo['user'] && $newDatabaseInfo['pass']) {
                $dbCredencials = $newDatabaseInfo['user'] . ':' . $newDatabaseInfo['pass'] . '@';
            }
            $this->connection = new MongoClient('mongodb://' . $dbCredencials . $newDatabaseInfo['host'] . ':' . $newDatabaseInfo['port'] . '/' . $newDatabaseInfo['name']);
            $this->db = $this->connection->{$newDatabaseInfo['name']};
        }
    }

    public function create() {

        $this->column['_id'] = $this->getNextSequence();
        $this->db->{$this->table}->insert($this->column);
        return $this->column['_id'];
    }

    public function delete() {
        return $this->db->{$this->table}->remove($this->condition);
    }

    public function read() {
        $this->column = is_array($this->column) ? $this->column : array();
        $this->cursor = $this->db->{$this->table}->find($this->condition, $this->column);

        if ($this->orderby) {
            $this->cursor = $this->cursor->sort($this->orderby);
        }
        if ($this->limit) {
            $this->cursor = $this->cursor->limit($this->limit);
        }
        if ($this->offset) {
            $this->cursor = $this->cursor->skip($this->offset);
        }
        foreach ($this->cursor as $doc) {
            $this->result[] = $doc;
        }

        return $this->result;
    }

    public function update() {
        return $this->db->{$this->table}->update($this->condition, array('$set' => $this->column), array('multiple' => true));
    }

    public function addWhereCondition($condition) {
        $this->condition = is_array($condition) ? $condition : array();
        return $this;
    }

    private function getNextSequence() {

        $result = $this->db->__sequences->findAndModify(array("name" => "$this->table"), array('$inc' => array("seq" => 1)));
        return $result['seq'];
    }

}

?>
