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

    public function getByUserLocality($userId){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('S' => $this->_name), array('Id', 'Uf'))
            ->joinInner(array('U' => 'User'), "U.Id = $userId", null)
            ->joinInner(array('UL' => 'UserLocality'), 'UL.UserId = U.Id', null)
            ->joinInner(array('SA' => 'ServiceArea'), 'SA.RegionalId = UL.RegionalId', null)
            ->joinLeft(array('N' => 'Neighborhood'), 'N.Id = SA.NeighborhoodId', null)
            ->joinLeft(array('C' => 'City'), 'C.Id = N.CityId or C.Id = SA.CityId', null)
            ->where('S.Id = C.StateId or S.Id = SA.StateId')
            ->group('S.Uf')
            ->order('S.Uf');

        return $this->fetchAll($query);
    }

    public function getByStateWithFinalists($stateId, $competitionId){
        return $this->fetchAll($this->selectByStateWithFinalists($stateId, $competitionId));
    }

    public function getAllWithFinalists($competitionId){
        return $this->fetchAll($this->selectAllWithFinalists($competitionId)->group('S.Id'));
    }

    private function selectByStateWithFinalists($stateId, $competitionId){
        $order = array('ClassificadoOuro desc', 'ClassificadoPrata desc', 'ClassificadoBronze desc');
        return $this->selectAllWithFinalists($competitionId)->where('S.Id = ?', $stateId)->order($order);
    }

    private function selectAllWithFinalists($competitionId){
        $eprJoinCondition = "EPR.EnterpriseIdKey = E.IdKey and EPR.ProgramaId = $competitionId and
            (EPR.ClassificadoOuro = 1 or EPR.ClassificadoPrata = 1 or EPR.ClassificadoBronze = 1)";
        $eprJoinColumns = array('ClassificadoOuro', 'ClassificadoPrata', 'ClassificadoBronze');

        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('S' => $this->_name))
            ->joinInner(array('AE' => 'AddressEnterprise'), 'AE.StateId = S.Id', null)
            ->joinInner(array('E' => 'Enterprise'), 'E.Id = AE.EnterpriseId', array('FantasyName'))
            ->joinInner(array('EPR' => 'EnterpriseProgramaRank'), $eprJoinCondition, $eprJoinColumns);

        return $query;
    }
}
