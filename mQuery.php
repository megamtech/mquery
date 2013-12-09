<?php

/**
 * @author G.M.Sundar <gmsundar@megamtech.com>
 * @copyright (c) 2013, Megam Technologies LLP
 * @
 */
define('DataBaseName', 'mauthenticate');
define('DataBasePort', '27017');
define('DataBaseHost', '192.168.1.200');
define('DataBaseUser', '');
define('DataBasePass', '');
define('DataBaseType', 'mongo');


include_once('cDatabase.php');
$cDatabase = new cDatabase(DataBaseType);

$data = json_decode(rawurldecode($_POST['data']));

$cDatabase->dbObj->table = $data->table;
$cDatabase->dbObj->column = $data->columns;
$result['data'] = "";
$cDatabase->dbObj->debug = $data->debug;
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
