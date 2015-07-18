<?php

class DbTable_GroupEnterprise extends Vtx_Db_Table_Abstract
{
    protected $_name = 'GroupEnterprise';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
    );

    protected $_dependentTables = array(
    );


    public function getById($Id){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('GrpEnt' => $this->_name),
                array('GroupId','EnterpriseId')
            )
            ->where('GrpEnt.Id = ?', $Id);
        return $this->fetchRow($query);
    }

}
