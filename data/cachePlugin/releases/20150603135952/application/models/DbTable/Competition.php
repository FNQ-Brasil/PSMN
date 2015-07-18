<?php

class DbTable_Competition extends Vtx_Db_Table_Abstract
{

    protected $_name = 'Competition';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_dependentTables = array(
    );

    public function getById($Id){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('C' => $this->_name),
                array('Id','StartDate','EndDate','Description','CreateAt','UpdatedAt')
            )
            ->where('C.Id = ?', $Id);
    
        return $this->fetchRow($query);
    }

}
