
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

    /**
     * Used to hold db connection object
     * @var Object 
     */
    private $connection;

    /**
     * Used to give collection name for operation
     * @var string 
     */
    public $table;

    /**
     * Used to give condition array(associative)
     * @var array 
     */
    public $condition;

    /**
     * Used to give column names and values array(associative)
     * @var array 
     */
    public $column;

    /**
     * Used to specify store Database name to connect
     * @var string 
     */
    public $db;
    public $orderby;
    public $limit;
    public $offset;
    public $group_by;
    public $result;
    private $cursor;
    public $returnType = "json";

    /**
     * Singleton class will call getconnection method to get object
     * @param array $newDatabaseInfo 
     * 
     */
    public function __construct($newDatabaseInfo) {
        $this->getConnection($newDatabaseInfo);

    }

    /**
     * 
     * @param array $newDatabaseInfo
     */
    public function getConnection($newDatabaseInfo) {
        if (!$this->db) {
            if ($newDatabaseInfo['user'] && $newDatabaseInfo['pass']) {
                $dbCredencials = $newDatabaseInfo['user'] . ':' . $newDatabaseInfo['pass'] . '@';
            }
            $this->connection = new MongoClient('mongodb://' . $dbCredencials . $newDatabaseInfo['host'] . ':' . $newDatabaseInfo['port'] . '/' . $newDatabaseInfo['name']);
            $this->db = $this->connection->{$newDatabaseInfo['name']};
        }

    }
/**
 *  Used to create record based on the table,column passed 
 * @return int|string
 */
    public function create() {
        try {
            $seq = $this->getNextSequence();
            if ($seq != '') {
                $this->column['_id'] = $this->getNextSequence();
            }
            /**
             * j=>true Journaling is enabled so we will get response 
             * @link http://php.net/manual/en/mongocollection.insert.php description
             */

            $this->db->{$this->table}->insert($this->column, array('j' => TRUE));
            $this->result = $this->column['_id'];
            $this->resetDefaults();
            return $this->result;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function delete() {
        try {
            //$this->resetDefaults();
            return $this->db->{$this->table}->remove($this->condition);
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function drop() {
        try {
            //$this->resetDefaults();
            return $this->db->{$this->table}->drop();
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function read() {
        try {

            $this->result = array();

            if ($this->group_by) {

                if (count($this->condition) > 0) {
                    $options['condition'] = $this->condition;
                }
//$this->group_by[0] == Columns to be grouped
//$this->group_by[1] == Initial values to be returned
//$this->group_by[2] == Javascript function to reduce the array

                $this->cursor = $this->db->{$this->table}->group($this->group_by[0], $this->group_by[1], $this->group_by[2], $options);
                $this->result = $this->cursor['retval'];
            } else {
                if ($this->column == "") {
                    $this->column = array();
                }
                if (!is_array($this->condition)) $this->condition = array();
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
            }


            $this->resetDefaults();
            return $this->result;
        } catch (Exception $e) {
            //echo $e->getTrace();
            return $e->getMessage();
        }

    }

    public function update() {
        try {
            return $this->db->{$this->table}->update($this->condition, array('$set' => $this->column), array('multiple' => true));

            $this->resetDefaults();
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function count() {
        try {
            return $this->db->{$this->table}->count($this->condition, $this->limit, $this->offset);
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function createTable() {
        return $this->db->createCollection($this->table);

    }

    public function createMultiple() {


        $this->result = $this->db->{$this->table}->batchInsert($this->column);
        foreach ($this->result as $value) {
            $result[] = $value['id'];
        }
        return $result;

    }

    /**
     *
     * @param type $condition= array(
     *
     * columnname,&ANDARRAY,&ORARRAY=>Array(
     * 'values'=>array(1,2,n),string| ~~~Mandatory ~~~default string
     * * 'type'=>'&lt',&gt,&starts,&ends,&contains,&eq,&between,&!eq,&!like,&notbetween,&!contains,&!startswith,&!endwith,&empty,&!empty,&in,&!in| ~~~ Optional ~~~ default &eq
     * ,dbtype=>|string,int,number,date~~Optional ~~~ default string
     * 'optype'=>'&AND',&OR | ~~~ Optional ~~~ Default AND
     * ))
     * @return \cMongo
     */
    public function addWhereCondition($condition) {
        $tempcondition = array();

        if (is_array($condition)) {

            foreach ($condition as $columnname => $values) {
                if (($columnname != '&ANDARRAY' && $columnname != '&ORARRAY')) {

                    if (is_array($values)) {
                        if ($values['optype'] == '&OR') {
                            $tempcondition['$or'][] = $this->createFilterCondition($columnname, $values['type'], $values['values'], $values['dbtype']);
                        } elseif ($values['optype'] == '&AND') {
                            $tempcondition['$and'][] = $this->createFilterCondition($columnname, $values['type'], $values['values'], $values['dbtype']);
                        } else {

                            $tempcondition[$columnname] = $values;
                        }
                    } else {

                        $tempcondition[$columnname] = $values;
                    }
                } else {
//TODO  Yet to implement Array of conditions

                    if ($columnname == '&ORARRAY') {
                        foreach ($values as $column => $value) {
                            if (is_array($value)) {
                                $tempcondition['$or'][] = $this->createFilterCondition($column, $value['type'], $value['values'], $value['dbtype']);
                            } else {
                                $tempcondition['$or'][] = array($column => $value);
                            }
                        }
                    } elseif ($columnname == '&ANDARRAY') {

                        if (is_array($value)) {
                            $tempcondition['$and'][] = $this->createFilterCondition($column, $value['type'], $value['values'], $value['dbtype']);
                        } else {
                            $tempcondition['$and'][] = array($column => $value);
                        }
                    }
                }
            }
        }

        $this->condition = $tempcondition;
        return $this;

    }

    function addOrderBy($orderby) {
        if (is_array($orderby)) {
            foreach ($orderby as $column => $order) {
                $this->orderby[$column] = ($order != 'asc') ? -1 : 1;
            }
        }
        return $this;

    }

    private function createFilterCondition($column, $type, $value, $dbtype) {
        $return = array();
        switch ($type) {
            case '&lt':
                $return[$column]['$lt'] = $this->changeDataType($dbtype, $value);
                break;
            case '&!lt':
                $return[$column]['$not']['$lt'] = $this->changeDataType($dbtype, $value);
                break;
            case '&gt':
                $return[$column]['$gt'] = $this->changeDataType($dbtype, $value);
                break;
            case '&!gt':
                $return[$column]['$not']['$gt'] = $this->changeDataType($dbtype, $value);
                break;
            case '&starts':
                $return[$column]['$regex'] = new MongoRegex("/^" . $this->changeDataType($dbtype, $value) . "/i");
                break;
            case '&!starts':
                $return[$column]['$not']['$regex'] = new MongoRegex("/^" . $this->changeDataType($dbtype, $value) . "/i");

                break;
            case '&ends':
                $return[$column]['$regex'] = new MongoRegex("/" . $this->changeDataType($dbtype, $value) . "$/i");
                break;
            case '&!ends':
                $return[$column]['$not']['$regex'] = new MongoRegex("/" . $this->changeDataType($dbtype, $value) . "$/i");
                break;
            case '&contains':
                $return[$column]['$regex'] = new MongoRegex('/^' . $this->changeDataType($dbtype, $value) . '/i');
                break;
            case '&!contains':
                $return[$column]['$not']['$regex'] = new MongoRegex("/" . $this->changeDataType($dbtype, $value) . "/i");

                break;
            case '&between':
                $return[$column]['$lt'] = $this->changeDataType($dbtype, $value[0]);
                $return[$column]['$gt'] = $this->changeDataType($dbtype, $value[1]);
            case '&!between':

                $return[$column]['$not']['$lt'] = $this->changeDataType($dbtype, $value[0]);
                $return[$column]['$not']['$gt'] = $this->changeDataType($dbtype, $value[1]);
                break;
            case '&empty':
                $return[$column] = null;
                break;
            case '&!empty':
                $return[$column]['$ne'] = null;

                break;
            case '&in':
                foreach ($value as $individual_value) {
                    $return[$column]['$in'][] = $this->changeDataType($dbtype, $individual_value);
                }
                break;
            case '&!in':
                foreach ($value as $individual_value) {
                    $return[$column]['$nin'] = $this->changeDataType($dbtype, $individual_value);
                }
                break;
            case '&!eq':
                $return[$column]['$ne'] = $this->changeDataType($dbtype, $value);

                break;

            default:
                $return[$column] = $this->changeDataType($dbtype, $value);
                break;
        }
        return $return;

    }

    private function changeDataType($type, $value) {
        switch ($type) {
            case 'date':
                $result = new MongoDate($value);

                break;
            case 'int':

                $result = (int) $value;
                break;
            case 'number':

                $result = (double) $value;
                break;

            default:
                $result = (string) "$value";
                break;
        }
        return $result;

    }

    public function addLimit($limit) {
        $this->limit = $limit;
        return $this;

    }

    public function addOffset($offset) {
        $this->offset = $offset;
        return $this;

    }

    public function addGroupBy($groupby) {
        $this->group_by = $groupby;
        return $this;

    }

    private function getNextSequence() {



        $result = $this->db->__sequences->findAndModify(array("name" => "$this->table"), array('$inc' => array("seq" => 1)));
        return $result['seq'];

    }

    private function resetDefaults() {
        unset(
                $this->table, $this->condition, $this->column, $this->offset, $this->orderby);

    }

}

?>
