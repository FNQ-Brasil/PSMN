<?php

class DbTable_ApeEvaluationVerificadorComment extends Vtx_Db_Table_Abstract
{
    protected $_name = 'ApeEvaluationVerificadorComment';
    protected $_id = 'Id';
    protected $_sequence = true;


public function getApeEvaluationVerificadorCommentFind($enterpriseId,$userId) {                  

        $configDb = Zend_Registry::get('configDb');
        
        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('APEVERCOM' => 'ApeEvaluationVerificadorComment'), null)
        ->where('APEVERCOM.AppraiserEnterpriseId = ?', $enterpriseId)
		->where('APEVERCOM.UserId = ?', $userId);
		
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns(array(          
            'APEVERCOM.AppraiserEnterpriseId', 
            'APEVERCOM.UserId',
            'APEVERCOM.CriterioNumber',
			'APEVERCOM.Comment'
        ))        
        ;         
        return $this->fetchAll($query);
    }
}
