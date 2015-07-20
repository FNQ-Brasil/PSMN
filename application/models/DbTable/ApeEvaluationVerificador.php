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
    
    public function verificaResposta($enterpriseId, $perguntaId,$competitionId) {
       
        $configDb = Zend_Registry::get('configDb');
        
        $query = $this->select()
            ->setIntegrityCheck(false)    
            ->from(
                array('CHEKEV' => 'checkerevaluation')
            )
            
            ->join(array('CHEKente'=>'checkerenterprise'), 'CHEKEV.CheckerEnterpriseId = CHEKente.ID',NULL)
            ->join(array('APEEVA'=>'apeevaluationverificador'), 'CHEKEV.CheckerEnterpriseId = APEEVA.AppraiserEnterpriseId',NULL )
            ->where('CHEKente.EnterpriseId = ?', $enterpriseId)
            ->where('APEEVA.AvaliacaoPerguntaId = ?', $perguntaId);
        
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns(array(            
            'CHEKEV.CheckerEnterpriseId',
            'CHEKente.EnterpriseId',
            'APEEVA.AvaliacaoPerguntaId',
            'APEEVA.Resposta'
        ))        
        ;
        
        
        $objResult = $this->fetchRow($query)->toArray();
        $resposta = array();
        $resposta[$objResult['AvaliacaoPerguntaId']]=$objResult['Resposta'];
	
        return $resposta;
    }
    
    
    
    public function verificaRespostaCriterio($enterpriseId, $perguntaId,$competitionId) {
       
        $configDb = Zend_Registry::get('configDb');
        
        $query = $this->select()
            ->setIntegrityCheck(false)    
            ->from(
                array('CHEKEV' => 'checkerevaluation')
            )
            
            ->join(array('CHEKente'=>'checkerenterprise'), 'CHEKEV.CheckerEnterpriseId = CHEKente.ID',NULL)
            ->where('CHEKente.EnterpriseId = ?', $enterpriseId)
            ->where('CHEKEV.QuestionCheckerId = ?', $perguntaId);
        
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns(array(            
            'CHEKEV.QuestionCheckerId',
            'CHEKEV.Resposta'
         ))        
        ;
        
        
        $objResult = getRole($this->fetchRow($query));
        $resposta = array();
        $resposta[$objResult['QuestionCheckerId']]=$objResult['Resposta'];
	
        return $resposta;
        
    }
    
    public function getRole($data)
    {
        return array($data);
    }

    public function getEnterpriseCheckerEnterprisePontosFortes($IdEntrepriseNacional, $CompetitionId)
    {
        $configDb = Zend_Registry::get('configDb');
                
                $query = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('CHE' => 'CheckerEnterprise'),null)
                ->where('CHE.EnterpriseId = ?' , $IdEntrepriseNacional)
                ->where('CHE.ProgramaId = ?', $CompetitionId);
        
                $query->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('CHE.QtdePontosFortes'));
                
                return $this->fetchRow($query);
    }
}