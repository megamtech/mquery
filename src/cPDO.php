<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cPDO
 *
 * @author Admin
 */
class cPDO {

    public $connection;
    //collection
    public $table;
    public $join_condition;
    public $condition;
    //fields
    public $column;
    public $orderby;
    public $limit;
    public $offset;
    public $result;
    private $cursor;
    public $sql;

    public function __construct($newDatabaseInfo) {
        $this->getConnection($newDatabaseInfo);

    }

    public function getConnection($newDatabaseInfo) {

        if (!$this->connection) {
            switch ($newDatabaseInfo['type']) {
                case 'mysql':
                    //MySQL
                    $this->connection = new PDO('mysql:host=' . $newDatabaseInfo['host'] . ';port=' . $newDatabaseInfo['port'] . ';dbname=' . $newDatabaseInfo['name'] . '',
                            $newDatabaseInfo['user'], $newDatabaseInfo['pass']);

                    break;
                case 'pgsql':
                    //PostgreSQL
                    $this->connection = new PDO('pgsql:host=' . $newDatabaseInfo['host'] . ' port=' . $newDatabaseInfo['port'] . ' dbname=' . $newDatabaseInfo['name'],
                            $newDatabaseInfo['user'], $newDatabaseInfo['pass']);
                    break;
                case 'oracle':
                    //Oracle
                    $this->connection = new PDO('oci:dbname=//' . $newDatabaseInfo['host'] . ':' . $newDatabaseInfo['port'] . '/' . $newDatabaseInfo['name'],
                            $newDatabaseInfo['user'], $newDatabaseInfo['pass']);
                    break;
                case 'sybase':
                    //Sybase
                    $this->connection = new PDO('sybase:host=' . $newDatabaseInfo['host'] . ';dbname=' . $newDatabaseInfo['name'],
                            $newDatabaseInfo['user'], $newDatabaseInfo['pass']);
                    break;
                case 'mssql':
                    //Microsoft SQL Server
                    $this->connection = new PDO('mssql:host=' . $newDatabaseInfo['host'] . ';dbname=' . $newDatabaseInfo['name'],
                            $newDatabaseInfo['user'], $newDatabaseInfo['pass']);
                    break;
                case 'dblib':
                    //DB(Lib) /Lite
                    $this->connection = new PDO('dblib:host=' . $newDatabaseInfo['host'] . ';dbname=' . $newDatabaseInfo['name'],
                            $newDatabaseInfo['user'], $newDatabaseInfo['pass']);
                    break;
                case 'mdb':
                    //MS Access
                    $this->connection = new PDO('odbc:Driver={Microsoft Access Driver (*.mdb)};DBQ=' . $newDatabaseInfo['name'] . '.mdb;Uid=' . $newDatabaseInfo['user']);
                    break;
                case 'sqlite':
                    //SQLite
                    $this->connection = new PDO('sqlite:' . $newDatabaseInfo['name'] . '.db');
                    break;
                case 'sqlite2':
                    //SQLite 2
                    $this->connection = new PDO('sqlite2:' . $newDatabaseInfo['name'] . '.db');
                    break;
            }
            return $this->connection;
        }

    }

    function create() {
        try {
            //$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->beginTransaction();
            $this->connection->exec($this->sql);
            //TODO Fix for all db's
            $lastid = $this->connection->lastInsertId();
            $this->connection->commit();
            return $lastid;
        } catch (Exception $ex) {
            $this->connection->rollBack();
            return "Failed: " . $ex->getMessage();
        }

    }

    function read() {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION);
            $this->result = $this->connection->prepare($this->sql);
            $this->result->execute();
            //TODO Fix for all db's
            $result = $this->result->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $ex) {
            $this->connection->rollBack();
            return "Failed: " . $ex->getMessage();
        }

    }

    function update() {
        try {
            //$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->beginTransaction();
            $updatedrows = $this->connection->exec($this->sql);
            $this->connection->commit();
            return $updatedrows;
        } catch (Exception $ex) {
            $this->connection->rollBack();
            return "Failed: " . $ex->getMessage();
        }

    }

    function delete() {
        try {
            //$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->beginTransaction();
            $updatedrows = $this->connection->exec($this->sql);
            $this->connection->commit();
            return $deletedrows;
        } catch (Exception $ex) {
            $this->connection->rollBack();
            return "Failed: " . $ex->getMessage();
        }

    }

}
