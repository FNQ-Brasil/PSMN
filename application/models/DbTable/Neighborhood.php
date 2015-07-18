<?php

class DbTable_Neighborhood extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Neighborhood';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'City' => array(
            'columns' => 'CityId',
            'refTableClass' => 'City',
            'refColumns' => 'Id'
        )
    );

    protected $_dependentTables = array(
        'DbTable_AddressEnterprise'
    );


    public function getById($Id){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('Neighborhood' => $this->_name),
                array('Uf','CityId','Name')
            )
            ->where('Neighborhood.Id = ?', $Id);
    
        return $this->fetchRow($query);
    }

}
