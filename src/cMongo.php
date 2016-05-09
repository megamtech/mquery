
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
//include AppLoggerModule . 'cMLogger.php';


class cMongo {

    public $connection;
    //collection
    public $table;
    public $condition;
    //fields
    public $column;
    //DB
    public $db;
    public $orderby;
    public $limit;
    public $offset;
    public $group_by;
    public $result;
    private $cursor;
    public $returnType = "json";
    private $mLogger = '';

    public function __construct($newDatabaseInfo) {
        $this->getConnection($newDatabaseInfo);

    }

    public function getConnection($newDatabaseInfo) {
        if (!$this->db) {
            $dbCredencials = '';
            if ($newDatabaseInfo['user'] && $newDatabaseInfo['pass']) {
                $dbCredencials = $newDatabaseInfo['user'] . ':' . $newDatabaseInfo['pass'] . '@';
            }
            $this->connection = new MongoDB\Client('mongodb://' . $dbCredencials . $newDatabaseInfo['host'] . ':' . $newDatabaseInfo['port']);
            $this->db = $this->connection->{$newDatabaseInfo['name']};
//            $this->mLogger = new cMLogger();
        }

    }

    private function logger($functionname, $type = 'debug', $exception = null) {
        $message = array("Function" => $functionname,
            " Table " => $this->table,
            "Columns" => json_encode($this->column),
            "Condition" => json_encode($this->condition),
            "Orderby" => json_encode($this->orderby),
            "Limit" => json_encode($this->limit),
            "Offset" => json_encode($this->offset)
        );
        if ($type == 'error') {
            $exception = array("Error Message" => $exception->getMessage(),
                "Error Code" => $exception->getCode(),
                "Error File" => $exception->getFile(),
                "Error Trace" => $exception->getTraceAsString(),
            );
            $this->mLogger->error(array_merge($message, $exception));
        } else {
            $this->mLogger->debug($message);
        }

    }

    public function create() {
        try {
            $seq = $this->getNextSequence();
            if ($seq != '') {
                $this->column['_id'] = $this->getNextSequence();
            }
            $this->replaceMetaFields();
            $this->result = $this->db->{$this->table}->insertOne($this->column);

//            $this->logger(__FUNCTION__,"debug");
            $this->resetDefaults();
            return $this->result->getInsertedId();
        } catch (Exception $e) {
//            $this->logger(__FUNCTION__,"error",$e);
            return $e->getMessage();
        }

    }

    public function delete() {
        try {
            //$this->resetDefaults();
//            $this->logger(__FUNCTION__,"debug");
            $this->result = $this->db->{$this->table}->deleteMany($this->condition);
            return $this->result->getDeletedCount();
        } catch (Exception $e) {
//            $this->logger(__FUNCTION__,"error",$e);
            return $e->getMessage();
        }

    }

    public function aggregate() {

//        $adminDB = $this->connection->admin; //require admin priviledge
//
//        $mongodb_info = $adminDB->command(array('buildinfo' => true));
//        $mongodb_version = $mongodb_info['version'];
//
        $this->cursor = $this->db->{$this->table}->aggregateCursor(array('$group' => $this->group_by,
            '$sort' => $this->order_by, '$limit' => $this->limit));
        foreach ($this->cursor as $doc) {
            $this->result[] = $doc;
        }


        $this->resetDefaults();
        return $this->result;

    }

    public function drop() {
        try {
            //$this->resetDefaults();
            return $this->db->{$this->table}->drop();
        } catch (Exception $e) {
//        $this->logger(__FUNCTION__,"error",$e);
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

                $this->cursor = $this->db->{$this->table}->group($this->group_by[0],
                        $this->group_by[1], $this->group_by[2], $options);
                $this->result = $this->cursor['retval'];
            } else {
                if (!is_array($this->condition)) {
                    $this->condition = array();
                }
                if (is_array($this->column)) {
                    $findOptions['projection'] = $this->column;
                }

                if ($this->orderby) {

                    $findOptions['sort'] = $this->orderby;
                }
                if ($this->limit) {

                    $findOptions['limit'] = $this->limit;
                }
                if ($this->offset) {
                    $findOptions['skip'] = $this->offset;
                }
                if ($this->columns) {
                    
                }
                $this->cursor = $this->db->{$this->table}->find($this->condition,
                        $findOptions);

                foreach ($this->cursor as $doc) {
                    $this->result[] = $doc;
                }
            }
//            $this->logger(__FUNCTION__,"debug");
            $this->resetDefaults();
            return $this->result;
        } catch (Exception $e) {
//            $this->logger(__FUNCTION__,"error",$e);
            return $e->getMessage();
        }

    }

    public function update() {
        try {
            $this->replaceMetaFields();
            $this->result = $this->db->{$this->table}->updateMany($this->condition,
                    array('$set' => $this->column)
            );

//            $this->logger(__FUNCTION__,"debug");
            $this->resetDefaults();
            return $this->result->getModifiedCount();
        } catch (Exception $e) {
//            $this->logger(__FUNCTION__,"error",$e);
            return $e->getMessage();
        }

    }

    public function count() {
        try {
            if ($this->limit) {

                $findOptions['limit'] = $this->limit;
            }
            if ($this->offset) {
                $findOptions['skip'] = $this->offset;
            }
//            $this->logger(__FUNCTION__,"debug");
            return $this->db->{$this->table}->count($this->condition,
                            $findOptions);
        } catch (Exception $e) {
//            $this->logger(__FUNCTION__,"error",$e);
            return $e->getMessage();
        }

    }

    public function distinct() {
        try {
//            $this->logger(__FUNCTION__,"debug");
            //For distinct we can use only one column @ this time
            return $this->db->{$this->table}->distinct($this->column[0],
                            $this->condition);
        } catch (Exception $e) {
//            $this->logger(__FUNCTION__,"error",$e);
            return $e->getMessage();
        }

    }

    public function createTable() {
//        $this->logger(__FUNCTION__,"debug");
        return $this->db->createCollection($this->table);

    }

    public function createMultiple() {
//        $this->logger(__FUNCTION__,"debug");
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
                            $tempcondition['$or'][] = $this->createFilterCondition($columnname,
                                    $values['type'], $values['values'],
                                    $values['dbtype']);
                        } elseif ($values['optype'] == '&AND') {
                            $tempcondition['$and'][] = $this->createFilterCondition($columnname,
                                    $values['type'], $values['values'],
                                    $values['dbtype']);
                        } else {

                            $tempcondition[$columnname] = $values;
                        }
                    } else {
                        //Converting to mongo id if it is a string
                        if ($columnname == '_id' && is_object($values) == false) {
                            $values = $this->changeDataType($columnname, $values);
                        }
                        $tempcondition[$columnname] = $values;
                    }
                } else {
//TODO  Yet to implement Array of conditions

                    if ($columnname == '&ORARRAY') {
                        foreach ($values as $column => $value) {
                            if (is_array($value)) {
                                $tempcondition['$or'][] = $this->createFilterCondition($column,
                                        $value['type'], $value['values'],
                                        $value['dbtype']);
                            } else {
                                $tempcondition['$or'][] = array($column => $value);
                            }
                        }
                    } elseif ($columnname == '&ANDARRAY') {

                        if (is_array($value)) {
                            $tempcondition['$and'][] = $this->createFilterCondition($column,
                                    $value['type'], $value['values'],
                                    $value['dbtype']);
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
                $return[$column]['$not']['$lt'] = $this->changeDataType($dbtype,
                        $value);
                break;
            case '&gt':
                $return[$column]['$gt'] = $this->changeDataType($dbtype, $value);
                break;
            case '&!gt':
                $return[$column]['$not']['$gt'] = $this->changeDataType($dbtype,
                        $value);
                break;
            case '&starts':
                $return[$column]['$regex'] = new MongoRegex("/^" . $this->changeDataType($dbtype,
                                $value) . "/i");
                break;
            case '&!starts':
                $return[$column]['$not']['$regex'] = new MongoRegex("/^" . $this->changeDataType($dbtype,
                                $value) . "/i");

                break;
            case '&ends':
                $return[$column]['$regex'] = new MongoRegex("/" . $this->changeDataType($dbtype,
                                $value) . "$/i");
                break;
            case '&!ends':
                $return[$column]['$not']['$regex'] = new MongoRegex("/" . $this->changeDataType($dbtype,
                                $value) . "$/i");
                break;
            case '&contains':
                $return[$column]['$regex'] = new MongoRegex('/^' . $this->changeDataType($dbtype,
                                $value) . '/i');
                break;
            case '&!contains':
                $return[$column]['$not']['$regex'] = new MongoRegex("/" . $this->changeDataType($dbtype,
                                $value) . "/i");

                break;
            case '&between':
                $return[$column]['$lt'] = $this->changeDataType($dbtype,
                        $value[0]);
                $return[$column]['$gt'] = $this->changeDataType($dbtype,
                        $value[1]);
            case '&!between':

                $return[$column]['$not']['$lt'] = $this->changeDataType($dbtype,
                        $value[0]);
                $return[$column]['$not']['$gt'] = $this->changeDataType($dbtype,
                        $value[1]);
                break;
            case '&empty':
                $return[$column] = null;
                break;
            case '&!empty':
                $return[$column]['$ne'] = null;

                break;
            case '&in':
                foreach ($value as $individual_value) {
                    $return[$column]['$in'][] = $this->changeDataType($dbtype,
                            $individual_value);
                }
                break;
            case '&!in':
                foreach ($value as $individual_value) {
                    $return[$column]['$nin'] = $this->changeDataType($dbtype,
                            $individual_value);
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
            case '_id':

                $result = new MongoId($value);
                break;
            default:
                $result = (string) "$value";
                break;
        }

        return $result;

    }

    private function replaceMetaFields() {

        foreach ($this->column as $column_name => $column_value) {
            if ($column_value == '&current_time&') {
                $column_value = (microtime(true) * 1000);
                $this->column[$column_name] = new \MongoDB\BSON\UTCDateTime($column_value);
            }
        }

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

    public function getNextSequence($sequence_name = '') {
        $sequence_name = ($sequence_name == '') ? $this->table : $sequence_name;
        $result = $this->db->__sequences->findAndModify(array("name" => "$sequence_name"),
                array('$inc' => array("seq" => 1)));
        return $result['seq_prefix'] . $result['seq'] . $result['seq_suffix'];

    }

    private function resetDefaults() {
        unset($this->table, $this->condition, $this->column, $this->offset,
                $this->orderby);

    }

}

?>
