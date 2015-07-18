<?php

class DbTable_Group extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Group';
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
                array('Grp' => $this->_name),
                array('Description','Title')
            )
            ->where('Grp.Id = ?', $Id);
        //echo $query; die;
        return $this->fetchRow($query);
    }

}
