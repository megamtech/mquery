<?php

/**
 * Database
 */
define('DataBaseName', 'sample');
define('DataBasePort', '3306');
define('DataBaseHost', 'localhost');
define('DataBaseUser', 'root');
define('DataBasePass', '');
define('DataBaseType', 'mysql');


include_once('cModel.php');

$cModel = new cModel();
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

$data = json_decode($_POST['data']);

$cModel->table = $data->table;
$cModel->column = $data->columns;
$result['data'] = "";
switch ($data->action) {
    case 'c':
        $result['data'] = $cModel->create()->executeWrite();


        break;
    case 'u':
        $result['data'] = $cModel->addWhereCondition($data['condition'])->update()->executeWrite();


        break;
    case 'r':
        $cModel->debug = true;
        $result['data'] = $cModel->addWhereCondition($data->condition)->addGroupBy($data->groupby)->addHaving($data->having)->addOrderBy($data->orderby)->addLimit($data->limit)->addOffsetBy($data->offset)->select()->executeRead();

        break;
    case 'd':
        $result['data'] = $cModel->addWhereCondition($data['condition'])->addHaving($data['having'])->delete()->executeWrite();


        break;

    default:
        $result['error'] = "Its not a Valid Action";

        break;
}
echo json_encode($result);
?>
