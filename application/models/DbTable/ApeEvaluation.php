<?php

class DbTable_ApeEvaluation extends Vtx_Db_Table_Abstract
{
    protected $_name = 'ApeEvaluation';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    public function getEnterpriseScoreAppraiserAnwserAvaliatorData($enterpriseId, $competitionId) {
        $configDb = Zend_Registry::get('configDb');

        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('APEN' => 'AppraiserEnterprise'), null)
        ->where('APEN.EnterpriseId = ?', $enterpriseId)
        ->join(
            array('APEV' => 'ApeEvaluation'), 'APEN.Id = APEV.AppraiserEnterpriseId',null
        )
        ->join(
            array('AVPE' => 'AvaliacaoPerguntas'), 'APEV.AvaliacaoPerguntaId = AVPE.ID',null
        );
    
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns(array(            
            'APEN.USERID',
            'APEN.AppraiserTypeId', 
            'APEV.Resposta',
            'AVPE.Criterio',
            'AVPE.BLOCO',
            'AVPE.QuestaoLetra',
            'APEV.Linha1',
            'APEV.Linha2'          
        ))        
        ;    
        #echo '<!-- '.$query->__toString().' -->'; echo '<pre>'; echo $query; die;
        //echo $query;
        return $this->fetchRow($query);
    }
}
