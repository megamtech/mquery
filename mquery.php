<?php

/**
 * Database
 */
define('DataBaseName', 'megam');
define('DataBasePort', '3306');
define('DataBaseHost', 'localhost');
define('DataBaseUser', 'root');
define('DataBasePass', '');
define('DataBaseType', 'mysql');


include_once('cDatabase.php');

$cDatabase = new cDatabase();
/**
 * CURD
 */
/**
 * CREATE
 */
//$cModel->table = "sample";
//$cModel->column = array("id" => '3', "user_name" => "megam", "first_name" => "megam", "last_name" => "tech");
//$cModel->create()->executeWrite();

/**
 * UPDATE
 */
//$cModel->table = "sample";
//$cModel->column = array("id" => '2', "user_name" => "megam345", "modified_by" => date("Y/m/d H:i:s"));
//$cModel->addWhereCondition("id=2")->update()->executeWrite();



/**
 * READ
 */
//$cModel->table = "sample";
//$sample = $cModel->addWhereCondition("id=1")->select()->executeRead();
//print_r($sample);

/**
 * DELETE
 */
//$cModel->table = "sample";
//$cModel->addWhereCondition("id=1")->delete()->executeWrite();
//Switch case

$data = json_decode(rawurldecode($_POST['data']));

$cDatabase->dbObj->table = $data->table;
$cDatabase->dbObj->column = $data->columns;
$result['data'] = "";
$cDatabase->dbObj->debug = $data->debug;
$cDatabase->dbType = DataBaseType;
$cDatabase->dbObj->condition = $data->condition;
$cDatabase->dbObj->groupby = $data->groupby;
$cDatabase->dbObj->having = $data->having;
$cDatabase->dbObj->orderby = $data->orderby;
$cDatabase->dbObj->limit = $data->limit;
$cDatabase->dbObj->offset = $data->offset;

switch ($data->action) {
    case 'c':
        $cDatabase->dbObj->create();
        break;
    case 'u':
        $cDatabase->dbObj->update();
        break;
    case 'r':
        $cDatabase->dbObj->read();
        break;
    case 'd':
        $cDatabase->dbObj->delete();
        break;
    default:
        $result['error'] = "Its not a Valid Action";
        break;
}
$result['data'] = $cDatabase->dbObj->result;
if ($data->debug) {
    $result['sql'] = rawurlencode($cDatabase->dbObj->lastsql);
}

echo json_encode($result);
?>
