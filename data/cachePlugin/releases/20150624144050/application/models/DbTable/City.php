<?php

class DbTable_City extends Vtx_Db_Table_Abstract
{
    protected $_name = 'City';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'State' => array(
            'columns' => 'StateId',
            'refTableClass' => 'State',
            'refColumns' => 'Id'
        )
    );

    protected $_dependentTables = array(
        'DbTable_Address',
        'DbTable_ServiceArea'
    );


    public function getById($Id){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('Cit' => $this->_name),
                array('Name','Uf','StateId')
            )
            ->where('Cit.Id = ?', $Id);
        //echo $query; die;
        return $this->fetchRow($query);
    }

}
