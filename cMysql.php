<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mysql_connecter
 *
 * @author gt
 */


class cMysql {

    private $connection;
    public $sql;
    public $error;

    public function __construct() {
        $this->getConnection();
    }

    public function getConnection() {
        if (!$this->connection) {
            $this->connection = new mysqli(DataBaseHost, DataBaseUser, DataBasePass, DataBaseName, DataBasePort);
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
        }
        return $this->connection;
    }

    public function read() {
        ////log_message("debug", "SQL : $this->sql");
        if ($stmt = $this->connection->prepare($this->sql)) {
            $stmt->execute();
            $stmt->store_result();
            $meta = $stmt->result_metadata();
            while ($currentColumn = $meta->fetch_field()) {
                $columnName = &$currentColumn->name;
                $columnNames[$currentColumn->name] = &$$columnName;
            }
            call_user_func_array(array($stmt, 'bind_result'), $columnNames);
            $copy = create_function('$a', 'return $a;');
            while ($stmt->fetch()) {
                $results[] = array_map($copy, $columnNames);
            }
            return $results;
        } else {
            ////log_message("error", "SQL Preparation Error : $this->sql");
            $this->error = "Please try again later : " . mysqli_error($this->connection);
            $this->error .="<br/>" . $this->sql;
            return false;
        }
    }

    public function write() {

        ////log_message("debug", "SQL : $this->sql");
        if ((count($para)) !== (count($type))) {
            throw new Exception("Number of parameters and types are not matching");
        }
        if (!preg_match('/^\s*(insert|update|delete|replace)/i', $this->sql)) {
            throw new Exception("SQL statement not supported");
        }
        if (strpos($this->sql, '?') === false) {
            $para = array();
            $type = array();
        }


        if ($stmt = $this->connection->prepare($this->sql)) {
            if ((!empty($para)) AND (!empty($type))) {
                $indicators = implode('', $type);
                array_unshift($para, $indicators);
                $parameters = array();
                // In PHP 5.3, if stmt_bind_param is used with call_user_func_array we have +to pass argument by reference.
                // Making the value to reference variables
                foreach ($para as $key => $value) {
                    $parameters[$key] = &$para[$key];
                }
                call_user_func_array(array($stmt, 'bind_param'), $parameters);
            }
            $result = $stmt->execute();
            if ($result) {
                if (preg_match('/^\s*(insert|replace)/i', $this->sql)) {
                    $stmt->store_result();
                    $result = mysqli_insert_id($this->connection);
                    $stmt->close();
                    return $result;
                } elseif (preg_match('/^\s*(delete|update)/i', $this->sql)) {
                    $result = mysqli_affected_rows($this->connection);
                    $stmt->close();
                    return $result;
                } else {
                    //log_message("debug", "SQL Preparation Error : $this->sql");
                    $this->error = "Please try again later !!!";
                    return false;
                }
            } else {
                //log_message("error", "SQL Preparation Error : $this->sql");
                $this->error = "Please try again later : " . mysqli_error($this->connection);
                return false;
            }
        } else {
            //log_message("error", "SQL Preparation Error : $this->sql");
            $this->error = "Please try again later : " . mysqli_error($this->connection);
            $this->error .="<br/>" . $this->sql;
            return false;
        }
    }

    function getNextVal($seq_name) {
        $seqdata = $this->getCurrVal($seq_name);
        //log_message("info", "getCurrVal(".$seq_name.") : $seqdata");
        $returnvalue = $seqdata[0]['seq_value'] + $seqdata[0]['increment_factor'];
        if ($seqdata[0]['table_name'] && $seqdata[0]['column_name']) {
            $this->sql = "select max(" . $seqdata[0]['column_name'] . ") as \"maxvalue\" from " . $seqdata[0]['table_name'];
            $current_data = $this->read();
            if (($current_data[0]['maxvalue'] + $seqdata[0]['increment_factor']) != $returnvalue) {
                $returnvalue = $current_data[0]['maxvalue'] + $seqdata[0]['increment_factor'];
            }
        }

        if ($seqdata[0]['pad_count'] > 0) {
            $returnseq = str_pad($returnvalue, $seqdata[0]['pad_count'], $seqdata[0]['pad_char'], $seqdata[0]['pad_type']);
        }

        $this->sql = "Update __sequence set seq_value=" . $returnvalue . " where seq_name='" . $seq_name . "'";
        $this->write();
        //log_message("info", "getNextVal -- returns: $returnseq");
        return $returnseq;
    }

    function getCurrVal($seq_name) {
        $this->sql = "Select seq_value,increment_factor,pad_count,pad_char,pad_type,table_name,column_name from __sequence where seq_name='" . $seq_name . "'";
        return $this->read();
    }

    function getColumnDetails($table) {

        $this->sql = "select c.TABLE_SCHEMA,c.TABLE_NAME,c.COLUMN_NAME,IS_NULLABLE,c.ORDINAL_POSITION,COLUMN_DEFAULT,DATA_TYPE, CHARACTER_MAXIMUM_LENGTH,CHARACTER_OCTET_LENGTH,COLUMN_TYPE,extra,COLUMN_KEY from information_schema.COLUMNS c  where c.TABLE_NAME='" . $table . "' and c.TABLE_SCHEMA='" . DataBaseName . "' order by c.ORDINAL_POSITION;";
        $columnDetailsArray = $this->read();
        if (is_array($columnDetailsArray)) {
            foreach ($columnDetailsArray as $key => $value) {
                $this->sql = "SELECT CONSTRAINT_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME from information_schema.KEY_COLUMN_USAGE where TABLE_NAME='" . $table . "' and TABLE_SCHEMA='" . DataBaseName . "' and COLUMN_NAME='" . $value['COLUMN_NAME'] . "'";
                $columnForeignDetailsArray = $this->read();
                $columnDetails[$value['COLUMN_NAME']] = $value;
                if ($columnForeignDetailsArray[0]['REFERENCED_TABLE_NAME']) {
                    $referenceColumnDetails = $this->getColumnDetails($columnForeignDetailsArray[0]['REFERENCED_TABLE_NAME']);
                    $columnDetails[$value['COLUMN_NAME']]['REFERENCED_TABLE_NAME'] = $columnForeignDetailsArray[0]['REFERENCED_TABLE_NAME'];
                    $columnDetails[$value['COLUMN_NAME']]['REFERENCED_COLUMN_NAME'] = $columnForeignDetailsArray[0]['REFERENCED_COLUMN_NAME'];
                    $columnDetails[$value['COLUMN_NAME']]['CONSTRAINT_NAME'] = $columnForeignDetailsArray[0]['CONSTRAINT_NAME'];
                    $columnDetails[$value['COLUMN_NAME']]['referencetabledetails'] = $referenceColumnDetails;
                }
                $columnDetails[$value['COLUMN_NAME']]["AI"]=$value['extra']=='auto_increment'?true:false;
                
                
            }

            //log_message("info", "getColumnDetails -- " . $table . ": " . json_encode($columnDetails));
            return $columnDetails;
        }
    }

    function getChildTables($table) {
        $this->sql = "select table_name,column_name,REFERENCED_COLUMN_NAME from information_schema.KEY_COLUMN_USAGE where REFERENCED_TABLE_NAME='" . $table . "' and `TABLE_SCHEMA`='" . DataBaseName . "'";
        $ChildTableDetailsArray = $this->read();

        if (is_array($ChildTableDetailsArray)) {
            foreach ($ChildTableDetailsArray as $key => $value) {
                $childTableDetails[$value['table_name']] = $value['column_name'];
            }
        }

        return $childTableDetails;
    }

    function getTableDetails($columns="",$condition="") {
       $this->sql = "SELECT TABLE_NAME,TABLE_TYPE,TABLE_ROWS,AUTO_INCREMENT,CREATE_TIME $columns from information_schema.TABLES where  TABLE_SCHEMA='" . DataBaseName . "' $condition order by TABLE_NAME;";
        return $this->read();
    }

    function getForeignKeyDetails($table) {
        $this->sql = "select column_name,REFERENCED_COLUMN_NAME,REFERENCED_TABLE_NAME from information_schema.KEY_COLUMN_USAGE where table_name='" . $table . "' and `TABLE_SCHEMA`='" . DataBaseName . "'";
        $ForeignKeyDetailsArray = $this->read();
        if (is_array($ForeignKeyDetailsArray)) {
            foreach ($ForeignKeyDetailsArray as $key => $value) {
                $ForeignKeyDetailsArray['columns'][] = $value['column_name'];
            }
        }

        return $ForeignKeyDetailsArray;
    }
}

?>
