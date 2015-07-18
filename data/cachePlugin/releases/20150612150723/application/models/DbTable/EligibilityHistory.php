<?php

class DbTable_EligibilityHistory extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EligibilityHistory';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_referenceMap = array(
        'Enterprise' => array(
          	'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        ),
        'Questionnarie' => array(
          	'columns' => 'QuestionnaireId',
            'refTableClass' => 'Questionnaire',
            'refColumns' => 'Id'
        ),
        'User' => array(
          	'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        )
    );

    public function getEligibilityHistory($enterpriseId, $questionnaireId, $isPremio = false)
    {
        $queryEligibility = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ELH' => 'EligibilityHistory'),
                array('Eligibility')
            )
            ->where('ELH.EnterpriseId = ?', $enterpriseId)
            ->where('ELH.QuestionnaireId = ?', $questionnaireId);
        if($isPremio) {
            $queryEligibility->where('ELH.PremioFlag = 1');
        }     
        $queryEligibility->order('EligibilityDate DESC')
            ->limit(1);
        
        $objResult = $this->fetchRow($queryEligibility);
        return $objResult;
    }
    
    public function setEligibilityHistory($enterpriseId, $questionnaireId, $userId = null, $eligibility, $isPremio = false)
    {
        $newEligibility = $this->createRow()
            ->setEnterpriseId($enterpriseId)
            ->setQuestionnaireId($questionnaireId)
            ->setUserId($userId)
            ->setEligibilityDate(new Zend_Db_Expr('NOW()'))
            ->setEligibility($eligibility);
        if($isPremio) {
            $newEligibility->setPremioFlag(1);
        }
        $newEligibility->save();
        return true;
    }
}