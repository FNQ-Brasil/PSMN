<?php
/**
 * 
 * Model_EnterpriseCategoryAward
 * @uses  
 * @author mcianci
 *
 */
class Model_EnterpriseCategoryAward
{
    public $dbTable_EnterpriseCategoryAward = "";
    
    function __construct() {
        $this->dbTable_EnterpriseCategoryAward = new DbTable_EnterpriseCategoryAward();
    }
   
    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_EnterpriseCategoryAward->fetchAll($where, $order, $count, $offset);
    }
    
}