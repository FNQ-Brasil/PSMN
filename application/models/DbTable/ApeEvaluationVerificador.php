<?php

class DbTable_ApeEvaluationVerificador extends Vtx_Db_Table_Abstract
{
    protected $_name = 'ApeEvaluationVerificador';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    public function getEnterpriseScoreAppraiserAnwserVerificadorData($enterpriseId, $competitionId) {
        $configDb = Zend_Registry::get('configDb');
        
        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('APEN' => 'AppraiserEnterprise'), null)
        ->where('APEV.AppraiserEnterpriseId = ?', $enterpriseId)
        ->join(
            array('APEV' => 'ApeEvaluationVerificador'), 'APEN.Id = APEV.AppraiserEnterpriseId',null
        )
        ->join(
            array('AVPE' => 'AvaliacaoPerguntas'), 'APEV.AvaliacaoPerguntaId = AVPE.ID',null
        );
    
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns(array(            
            'APEN.USERID',
            'APEN.AppraiserTypeId',
            'APEV.AppraiserEnterpriseId',
            'APEV.AvaliacaoPerguntaId',
            'APEV.Resposta',
            'APEV.PontosFinal',
            'AVPE.Criterio',
            'AVPE.BLOCO',
            'AVPE.QuestaoLetra'         
        ))        
        ;    
        return $this->fetchRow($query);
    }

    
}