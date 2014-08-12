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
    public $condition = array();
    //fields
    public $column;
    //DB
    public $db;
    public $orderby;
    public $limit;
    public $offset;
    public $result;
    private $cursor;
    public $returnType = "json";

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
        try {
            $id = $this->getNextSequence();
            if ($id != ''||$id!=null){
                $this->column['_id'] = $id;
            }
            $this->db->{$this->table}->insert($this->column);
            $this->result = $this->column;
            $this->resetDefaults();
            return $this->result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function delete() {
        try {
            $this->result = $this->db->{$this->table}->remove($this->condition);
            $this->resetDefaults();
            return $this->result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function read() {
        try {
            $this->result = array();
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

            $this->resetDefaults();
            return $this->result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function update() {
        try {
//Todo Fix  the $set problem for multiple columns now it works as replace not update a specific column.
            $this->result = $this->db->{$this->table}->update($this->condition, array('$set' => $this->column), array("multiple" => True));
            $this->resetDefaults();
            return $this->result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function addWhereCondition($condition = array()) {

        if (is_array($condition[$or])) {

            $newcondition = array('$or' => array());
            $index = 0;
            foreach ($condition['$or'] as $key => $value) {
                $newcondition['$or'][$index][$key] = $this->createConditionBasedonType($value);
            }
            $condition = $newcondition;
        }

        $this->condition = $condition;
        return $this;
    }

    function createConditionBasedonType($value) {
        if (is_array($value)) {
            switch (key($value)) {
                case 'like':
                    $value = new MongoRegex('/' . current($value) . '/i');
                    break;
            }
        }
        return $value;
    }

    private function getNextSequence() {

        $result = $this->db->__sequences->findAndModify(array("name" => "$this->table"), array('$inc' => array("seq" => 1)));
        return $result['seq'];
    }

    function addOrderBy($orderby) {
        if (is_array($orderby)) {
            foreach ($orderby as $column => $order) {
                $this->orderby[$column] = ($order != 'asc') ? -1 : 1;
            }
        }
        return $this;
    }

    public function addLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function addOffset($offset) {

        $this->offset_by = $offset;
        return $this;
    }

    private function resetDefaults() {
        unset($this->table, $this->offset, $this->orderby);
        $this->condition = array();
        $this->column = array();
    }

    function createTable() {
//        echo $this->table;
        return $this->db->createCollection($this->table);
    }

}

?>
