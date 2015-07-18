<?php
/**
 * 
 * Model_City
 * @uses  
 * @author mcianci
 *
 */
class Model_Competition
{

    public $dbTable_Competition = "";

    function __construct() {
        $this->dbTable_Competition = new DbTable_Competition();
    }

    public function getAllCompetition()
    {
        return $this->dbTable_Competition->fetchAll($where = null, $order = 'Id DESC', $count= null, $offset= null);
    }

    public function getCurrent(){
        return $this->dbTable_Competition->fetchRow(array('Id = ?' => date('Y')));
    }

    public function getByYear($year){
        return $this->dbTable_Competition->fetchRow(array('Id = ?' => $year));
    }

}