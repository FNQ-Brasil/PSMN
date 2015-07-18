<?php

class Model_CourseType
{

    public $dbTable_CourseType = "";
    
    function __construct() {
        $this->dbTable_CourseType = new DbTable_CourseType();
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_CourseType->fetchAll($where, $order, $count, $offset);
    }

    function getCourseTypeById($id)
    {
       return $this->dbTable_CourseType->fetchRow(array('Id = ?' => $id));
    }

}