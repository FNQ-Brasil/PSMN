<?php
/**
 * 
 * Model_State
 * @uses  
 * @author mcianci
 *
 */
class Model_State
{

    public $dbTable_State = "";
    
    function __construct() {
        $this->dbTable_State = new DbTable_State();
    }

    function getStateByUf($uf)
    {
        return $this->dbTable_State->fetchRow(array('Uf = ?' => $uf));
    }

    function getStateById($id)
    {
        return $this->dbTable_State->fetchRow(array('Id = ?' => $id));
    }

    public function getAll($where=null,$order=null,$count=null,$offset=null,$filter=null)
    {
        $dataCached = false;
        $returnAllNoFilter = (!$where and !$order and !$count and !$offset and !$filter)? true : false;
        
        if ($returnAllNoFilter) {
            $cache = Zend_Registry::get('cache_FS');
            $nameCache = 'statesAll';
            $dataCached = $cache->load($nameCache);
            if ($dataCached) {
                return $dataCached;
            };
        }
        
        $query = $this->dbTable_State->select()
                ->setIntegrityCheck(false)
                ->distinct()
                ->from(
                    array('S' => 'State'),
                    array('Id', 'Name','Uf')
        );
        if (isset($filter['CityId']) and $filter['CityId']) {
            $query->joinleft(
                array('C' => 'City'),
                'S.Id = C.StateId',
                null // array('CityId'=>'Id','CityName'=>'Name','Uf')
            );
        }
        if (isset($filter['NeighborhoodId']) and $filter['NeighborhoodId']) {
            $query->joinleft(
                array('N' => 'Neighborhood'),
                'S.Uf = N.Uf',
                null //array('NeighborhoodId'=>'Id','NeighborhoodName'=>'Name','Uf','CityId')
            );
        }
        if (isset($filter['uf']) and $filter['uf']) {
            $query->where('S.Uf in (?)', $filter['uf']);
        }
        if (isset($filter['StateId']) and $filter['StateId']) {
            $query->where('S.Id in (?)', $filter['StateId']);
        }
        if (isset($filter['CityId']) and $filter['CityId']) {
            $query->where('C.Id in (?)', $filter['CityId']);
        }
        if (isset($filter['NeighborhoodId']) and $filter['NeighborhoodId']) {
            $query->where('N.Id in (?)', $filter['NeighborhoodId']);
        }
        $query->order('Name ASC');

        $dataCached = $this->dbTable_State->fetchAll($query);
        
        if ($returnAllNoFilter) {
            $cache->save($dataCached, $nameCache);
        }
        return $dataCached;
    }

    public function getByUserLocality($userId) {
        return $this->dbTable_State->getByUserLocality($userId);
    }

    public function getByStateWithFinalists($stateId, $competitionId){
        return $this->dbTable_State->getByStateWithFinalists($stateId, $competitionId);
    }

    public function hasFinalists($stateId, $competitionId) {
        foreach($this->getAllWithFinalists($competitionId) as $state) if($state->getId() == $stateId) return true;
        return false;
    }

    public function getAllWithFinalists($competitionId) {
        return $this->dbTable_State->getAllWithFinalists($competitionId);
    }
}