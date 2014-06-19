<?php

//http://logging.apache.org/log4php/quickstart.html
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('html_errors', On);
//ini_set('expose_php',Off);
//ini_set('output_buffering',40960);
//ini_set('max_execution_time',40);
ini_set('default_charset', 'utf-8');
//ini_set('session.save_path','../../tech/sessions');
date_default_timezone_set('UTC');
/**
 * Database
 */
define('DataBaseName', 'testdb');
define('DataBasePort', '3306');
//Use 127.0.0.1 localhost is very slow in windows for mysqli
define('DataBaseHost', '127.0.0.1');
define('DataBaseUser', 'root');
define('DataBasePass', '');
define('DataBaseType', 'mysql');

include './src/cDatabase.php';
$newDatabaseInfo['type'] = DataBaseType;
$newDatabaseInfo['host'] = DataBaseHost;
$newDatabaseInfo['port'] = DataBasePort;
$newDatabaseInfo['user'] = DataBaseUser;
$newDatabaseInfo['pass'] = DataBasePass;
$newDatabaseInfo['name'] = DataBaseName;
$DbObj = new cDatabase($newDatabaseInfo);
$table = "users";

$DbObj->table = $table;
$results = $DbObj->delete();

echo "=========Cleaning Table - Cleaned - " . count($results) . "=============<br>";

echo "=========Creating - records=============<br>";
for ($i = 1; $i <= 10; $i++) {
    $DbObj->column = array("id" => $i, "user_name" => "TestData" . $i, "mail_id" => "testemail" . $i . "@megamtech.com");
    $DbObj->table = $table;
    $results[] = $DbObj->create();
}


if (implode(',', $results) == "1,2,3,4,5,6,7,8,9,10") {
    echo "Test Success";
} else {
    echo "Test Failure";
}
echo '<br>';
//echo "=========Create Output ends =============<br>";
//echo "=========Updating Record with id 5 =============<br>";
$DbObj->column = array("user_name" => "TestData20", "mail_id" => "testemail20@megamtech.com");
$DbObj->table = $table;
$DbObj->addWhereCondition(array("id" => 5))->update();
//TODO
$DbObj->column = array("user_name", "mail_id");
$DbObj->table = $table;
$results = $DbObj->addWhereCondition(array("id" => 5))->addOrderBy(array("id" => "desc"))->read();
print_r($results);

if ($results[0]['user_name'] == "TestData20" && $results[0]['mail_id'] == "testemail20@meg") {
    echo "Test Success";
} else {
    echo "Test Failure";
}
echo '<br>';
echo "=========Update Output ends =============<br>";
echo "=========Reading data=============<br>";


$DbObj->table = $table;
$results = $DbObj->addOrderBy(array("user_name" => "asc"))->read();
print_r($results);
exit;

if ($results[4]['user_name'] == "TestData20" && $results[4]['mail_id'] == "testemail20@meg" && count($results) == 10) {
    echo "Test Success";
} else {
    echo "Test Failure";
}
echo '<br>';
echo "=========Read Output ends =============<br>";

echo "=========Deleting data=============<br>";
$DbObj->table = $table;

$results = $DbObj->addWhereCondition(array("id" => 8))->delete();
$DbObj->table = $table;
$results = $DbObj->addOrderBy(array("id" => "asc"))->read();


if (count($results) == 9) {
    echo "Test Success";
} else {
    echo "Test Failure";
}
echo "=========Delete Output ends =============<br>";














































































































































































