<?php

class DbTable_Execution extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Execution';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'User' => array(
          	'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        ),
        'Questionnaire' => array(
          	'columns' => 'QuestionnaireId',
            'refTableClass' => 'Questionnaire',
            'refColumns' => 'Id'
        )
    );
    
    protected $_dependentTables = array(
        'DbTable_Answer'
    );

    public function getPendingExecutionsByProgramaId($programaId){
        return $this->fetchAll($this->selectByProgramaId($programaId)->where('Progress < ?', 100));
    }

    private function selectByProgramaId($programaId){
        return $this->select()->where('ProgramaId = ?', $programaId)->order('Id ASC');
    }
}
