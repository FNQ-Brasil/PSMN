<?php
/**
 * 
 * Model_City
 * @uses  
 * @author mcianci
 *
 */
class Model_City
{

    public $dbTable_City = "";

    function __construct() {
        $this->dbTable_City = new DbTable_City();
    }
    
    function getCityById($id)
    {
        $where = null;
        if ($id) {
            $where = array('Id = ?' => $id);
        }
        return $this->dbTable_City->fetchRow($where);
    }
    
    public function getAllCityByStateId($stateId=null,$serviceArea=null)
    {
        if ($stateId) {
            $filter['state_id'] = $stateId;
        }
        
        if ($serviceArea) {
            $filter[$serviceArea['indice']] = $serviceArea['value'][$serviceArea['indice']];
        }
        
        return $this->getAll($where=null,$order=null,$count=null,$offset=null,$filter);
    }
    
    function getAllCityByUf($uf = null)
    {
        if (isset($uf) and $uf) {
            $filter['Uf'] = $uf;
        }
        return $this->getAll($where=null,$order=null,$count=null,$offset=null,$filter);
    }

    public function get($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_City->fetchRow($where, $order, $count, $offset);
    }

    public function getAll($where=null,$order=null,$count=null,$offset=null,$filter=null)
    {
        $query = $this->dbTable_City->select()
                ->setIntegrityCheck(false)
                ->distinct()
                ->from(
                    array('C' => 'City'),
                    array('Id', 'Name','Uf','StateId')
        );
        if (isset($filter['NeighborhoodId']) and $filter['NeighborhoodId']) {
            $query->joinleft(
                array('N' => 'Neighborhood'),
                'C.Id = N.CityId',
                null //array('NeighborhoodId'=>'Id','NeighborhoodName'=>'Name','Uf','CityId')
            );
        }
        if (isset($filter['state_id']) and $filter['state_id']) {
            $query->where('C.StateId = (?)', $filter['state_id']);
        }
        if (isset($filter['StateId']) and $filter['StateId']) {
            $query->where('C.StateId in (?)', $filter['StateId']);
        }
        if (isset($filter['Uf']) and $filter['Uf']) {
            $query->where('C.Uf in (?)', $filter['Uf']);
        }
        if (isset($filter['CityId']) and $filter['CityId']) {
            $query->where('C.Id in (?)', $filter['CityId']);
        }
        if (isset($filter['NeighborhoodId']) and $filter['NeighborhoodId']) {
            $query->where('N.Id in (?)', $filter['NeighborhoodId']);
        }
        if(isset($where)) {
            $query->where($where);
        }
        $query->order('Name ASC');
        return $this->dbTable_City->fetchAll($query);
        //return $this->dbTable_City->fetchAll($where, $order, $count, $offset);
    }

}