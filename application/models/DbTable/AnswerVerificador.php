<?php

class DbTable_AnswerVerificador extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AnswerVerificador';
    protected $_id = 'Id';
    protected $_sequence = true;
    protected $_rowClass = 'DbTable_AnswerRow';

    protected $_referenceMap = array(
            'Alternative' => array(
            'columns' => 'AlternativeId',
            'refTableClass' => 'Alternative',
            'refColumns' => 'Id'
        ),
            'User' => array(
            'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        )
    );
    
    protected $_dependentTables = array(
        'DbTable_AnswerHistoryVerificador'
    );

	   public function getAllScore()
    {
        $configDb = Zend_Registry::get('configDb');
                
        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('Ans' => 'AnswerVerificador'),null)

        ->joinLeft(
            array('Alt' => 'Alternative'), 'Ans.AlternativeId = Alt.Id',null
        );

        $query->reset(Zend_Db_Select::COLUMNS)

        ->columns(array(          
            'Ans.AlternativeId', 
            'Ans.UserId',
            'Ans.EnterpriseId',
            'Alt.QuestionId',
            'Alt.ScoreLevel'
        ));        

        return $this->fetchAll($query);
    }
	
    public function getAllScoreByEnterpriseId($enterpriseId)
    {
        $configDb = Zend_Registry::get('configDb');
                
        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('Ans' => 'AnswerVerificador'),null)

        ->joinLeft(
            array('Alt' => 'Alternative'), 'Ans.AlternativeId = Alt.Id',null
        )
		->where('Ans.EnterpriseId = ?', $enterpriseId);

        $query->reset(Zend_Db_Select::COLUMNS)

        ->columns(array(          
            'Ans.UserId',
            'Ans.EnterpriseId',
            'AVG(`Alt`.`ScoreLevel`) as ScoreLevel'
        ));        

        return $this->fetchAll($query);
    }
}
