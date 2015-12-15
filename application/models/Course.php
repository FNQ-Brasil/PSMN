<?php

class Model_Course
{

    public $dbTable_Course = "";
    
    function __construct() {
        $this->dbTable_Course = new DbTable_Course();
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_Course->fetchAll($where, $order, $count, $offset);
    }
}