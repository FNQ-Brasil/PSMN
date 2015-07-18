<?php
/**
 * 
 * Model_ApeEvaluationVerificador
 * @uses  
 *
 */
class Model_ApeEvaluationVerificador
{

    public $DbApeEvaluationVerificador = "";
    
    public function __construct()
    {
        $this->DbAppraiser = DbTable_AppraiserEnterprise::getInstance();
        $this->DbChecker = DbTable_CheckerEnterprise::getInstance();
        $this->DbEnterprise = DbTable_Enterprise::getInstance();
        $this->DbApeEvaluation = DbTable_ApeEvaluation::getInstance();
        $this->DbCheckerEvaluation = DbTable_CheckerEvaluation::getInstance();
        $this->DbApeEvaluationVerificador = DbTable_ApeEvaluationVerificador::getInstance();         
    }
    
    public function getTable()
    {
        return $this->DbApeEvaluationVerificador;
    }
    
function getEnterpriseScoreAppraiserAnwserVerificadorData($enterpriseId, $competitionId = null)
    {
        if (!$competitionId) {
            $competitionId = Zend_Registry::get('configDb')->competitionId;
        }
        return $this->DbApeEvaluationVerificador->getEnterpriseScoreAppraiserAnwserVerificadorData($enterpriseId, $competitionId);
    }

  
}