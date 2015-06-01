<?php
/**
 * 
 * Model_PresidentProgram
 * @uses  
 * @author mcianci
 *
 */
class Model_PresidentProgram
{

    public $dbTable_PresidentProgram = "";
    
    function __construct() {
        $this->dbTable_PresidentProgram = new DbTable_PresidentProgram();
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_PresidentProgram->fetchAll($where, $order, $count, $offset);
    }
 

    function getPresidentProgramById($Id)
    {
        return $this->dbTable_PresidentProgram->fetchRow(array('Id = ?' => $Id));
    }
    
    function getPresidentProgramByPresidentId($presidentId)
    {
        return $this->dbTable_PresidentProgram->fetchRow(array('PresidentId = ?' => $presidentId));
    }    

    function getAllPresidentProgramByPresidentId($presidentId)
    {
        return $this->dbTable_PresidentProgram->fetchAll(array('PresidentId = ?' => $presidentId));
    }
    
    public function createPresidentProgramByPresidentId($registerPresidentProgramData,$presidentId) 
    {
        $modelPresidentProgramType = new Model_PresidentProgramType();
        foreach ($registerPresidentProgramData as $ElementName => $value) {
            $presidentProgramTypeId = $modelPresidentProgramType->getPresidentProgramTypeIdByElementName($ElementName);
            $presidentProgramRow = DbTable_PresidentProgram::getInstance()->createRow()
                ->setPresidentId($presidentId)
                ->setPresidentProgramTypeId($presidentProgramTypeId)
                ->setCompetitionId($value);
            $presidentProgramRow->save();
        }
        return array(
            'status' => true
        );
    }
    
    public function deleteAllPresidentProgramByPresidentId($PresidentId) 
    {   
        DbTable_PresidentProgram::getInstance()->delete(array('PresidentId = ?' => $PresidentId));
        return array(
            'status' => true
        );
    }
    
    
}