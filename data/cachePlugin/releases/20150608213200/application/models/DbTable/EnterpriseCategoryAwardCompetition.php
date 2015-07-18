<?php

class DbTable_EnterpriseCategoryAwardCompetition extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EnterpriseCategoryAwardCompetition';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Enterprise' => array(
            'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        )
    );

    public function getVerifiedECACsForEnterprise($enterpriseId){
        return $this->fetchAll(array('EnterpriseId = ?' => $enterpriseId, 'Verified = ?' => true));
    }

    public function getECACByToken($token){
        return $this->fetchAll(array('Token = ?' => $token));
    }

    public function getECACByEnterpriseIdAndYear($enterpriseId, $year){
        return $this->fetchRow(
            array('EnterpriseId = ?' => $enterpriseId, 'CompetitionId = ?' => $year)
        );
    }

    public function getECACByTokenAndYear($token, $year){
        return $this->fetchRow(
            array('Token = ?' => $token, 'CompetitionId = ?' => $year)
        );
    }

    public function updateECACVerifiedByToken($token){
        $data = array('Verified' => true);
        $where = $this->getAdapter()->quoteInto('Token = ?', $token);
        $this->update($data, $where);
    }

    public function updateECACVerifiedByEnterpriseIdAndYear($enterpriseId, $competitionId){
        $data = array('Verified' => true);
        
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('EnterpriseId = ?', $enterpriseId);
        $where[] = $this->getAdapter()->quoteInto('CompetitionId = ?', $competitionId);

        $this->update($data, $where);
    }
}
