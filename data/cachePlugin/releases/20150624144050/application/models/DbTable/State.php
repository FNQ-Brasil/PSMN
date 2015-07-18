<?php

class DbTable_State extends Vtx_Db_Table_Abstract
{
    protected $_name = 'State';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_dependentTables = array(
        'DbTable_City'
    );

    public function getById($Id){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('Est' => $this->_name),
                array('Name','Uf')
            )
            ->where('Est.Id = ?', $Id);
    
        return $this->fetchRow($query);
    }
}
