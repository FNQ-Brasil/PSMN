<?php

class DbTable_CheckerEvaluation extends Vtx_Db_Table_Abstract
{
    protected $_name = 'CheckerEvaluation';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getCheckerEvaluations($enterpriseId, $competitionId) {
        $configDb = Zend_Registry::get('configDb');        
        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('CHE' => 'CheckerEvaluation'), null)
        ->where('CHE.CheckerEnterpriseId = ?', $enterpriseId)
            and ('CHE.CheckerEvaluationTypeId' == '2') 
            and 'CHE.Resposta' == 'F';   
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns('COUNT(*)');
        #echo '<!-- '.$query->__toString().' -->'; echo '<pre>'; echo $query; die;
        //echo $query;
        return $this->fetchRow($query);
    }
}