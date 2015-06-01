<?php

class DbTable_Metier extends Vtx_Db_Table_Abstract
{

    protected $_name = 'Metier';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_dependentTables = array(
        'DbTable_Enterprise'
    );

    public function getById($Id){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('MET' => $this->_name),
                array('Description')
            )
            ->where('MET.Id = ?', $Id);
    
        return $this->fetchRow($query);
    }

}
