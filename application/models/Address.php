<?php
/**
 * 
 * Model_Address
 * @uses  
 * @author mcianci
 *
 */
class Model_Address
{

    public $dbTable_Address = "";

    function __construct() {
        $this->dbTable_Address = new DbTable_Address();
    }
    
    function getAddressById($id)
    {
        $where = null;
        if ($id) {
            $where = array('Id = ?' => $id);
        }
        return $this->dbTable_Address->fetchRow($where);
    }

    public function get($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_Address->fetchRow($where, $order, $count, $offset);
    }

    public function getAddressByFilter($filter)
    {
        $query = $this->dbTable_Address->select()
                ->setIntegrityCheck(false)
                ->distinct()
                ->from(
                    array('A' => 'Address'),
                    array(
                        'Id', 
                        'Uf',
                        'CityId',
                        'NameAloneLog',
                        'NameFullLog',
                        'NeighborhoodId',
                        'StreetType',
                        'Cep',
                        'UfCode',
                        'StreetCompletion'
                        )
        );
        if (isset($filter['uf']) and $filter['uf']) {
            $query->where('A.Uf = (?)', $filter['uf']);
        }
        if (isset($filter['city_id']) and $filter['city_id']) {
            $query->where('A.CityId in (?)', $filter['city_id']);
        }
        if (isset($filter['cep']) and $filter['cep']) {
            $query->where('A.cep in (?)', $filter['cep']);
        }
        return $this->dbTable_Address->fetchRow($query);
    }

}