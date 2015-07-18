<?php
/**
 * 
 * Model_Neighborhood
 * @uses  
 * @author mcianci
 *
 */
class Model_Neighborhood
{

    public $dbTable_Neighborhood = "";

    function __construct() {
        $this->dbTable_Neighborhood = new DbTable_Neighborhood();
    }

    function getAllNeighborhoodByCityId($cityId=null,$serviceArea=null)
    {
        $filter = null;
        
        if ($cityId) {
            $filter['city_id'] = $cityId;
        }
        
        if ($serviceArea) {
            $filter[$serviceArea['indice']] = $serviceArea['value'][$serviceArea['indice']];
        }
        
        return $this->getAll($where=null,$order=null,$count=null,$offset=null,$filter);
        /*
        $where = null;
        $order = 'Name ASC';
        if ($cityId) {
            $where = array('CityId = ?' => $cityId);
        }
        return $this->getAll($where,$order);
        */}
    
    function getNeighborhoodById($id)
    {
        $where = null;
        if ($id) {
            $where = array('Id = ?' => $id);
        }
        return $this->get($where);
    }
    
    public function get($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_Neighborhood->fetchRow($where, $order, $count, $offset);
    }  
    
    public function getAll($where=null,$order=null,$count=null,$offset=null,$filter=null)
    {
        $query = $this->dbTable_Neighborhood->select()
                ->setIntegrityCheck(false)
                ->distinct()
                ->from(
                    array('N' => 'Neighborhood'),
                    array('Id', 'Name','Uf','CityId')
        );
        if (isset($filter['StateId']) and $filter['StateId']) {
            $query->joinleft(
                array('S' => 'State'),
                'N.Uf = S.Uf',
                null
            );
        }
        if (isset($filter['CityId']) or isset($filter['city_id'])) {
            $query->joinleft(
                array('C' => 'City'),
                'N.CityId = C.Id',
                null
            );
        }
        if (isset($filter['StateId']) and $filter['StateId']) {
            $query->where('S.Id in (?)', $filter['StateId']);
        }
        if (isset($filter['city_id']) and $filter['city_id']) {
            $query->where('C.Id = (?)', $filter['city_id']);
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
        
/*
        echo $query;
        die;
        */
        return $this->dbTable_Neighborhood->fetchAll($query);
        //return $this->dbTable_Neighborhood->fetchAll($where, $order, $count, $offset);
    }

}