<?php
/**
 * 
 * Model_PresidentProgramType
 * @uses  
 * @author mcianci
 *
 */
class Model_PresidentProgramType
{

    public $dbTable_PresidentProgramType = "";
    
    function __construct() {
        $this->dbTable_PresidentProgramType = new DbTable_PresidentProgramType();
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_PresidentProgramType->fetchAll($where, $order, $count, $offset);
    }
 

    function getPresidentProgramTypeById($Id)
    {
        return $this->dbTable_PresidentProgramType->fetchRow(array('Id = ?' => $Id));
    }
    
    function getPresidentProgramTypeIdByElementName($ElementName)
    {
        $PresidentProgramTypeRow = $this->dbTable_PresidentProgramType
            ->fetchRow(array('ElementName = ?' => $ElementName));
        return $PresidentProgramTypeRow->getId(); 
    }    

}