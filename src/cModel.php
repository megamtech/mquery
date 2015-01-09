<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Admin
 */
include 'properties.php';
interface cModel {

    public function create();

    public function update();

    public function read();

    public function delete();

    public function addOrderBy($orderby);

    public function addLimit($limit);

    public function addOffset($offset);

    public function addWhereCondition($condition);
    
    public function addGroupBy($groupby);
}
