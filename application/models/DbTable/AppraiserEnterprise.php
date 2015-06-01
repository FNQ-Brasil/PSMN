<?php

class DbTable_AppraiserEnterprise extends Vtx_Db_Table_Abstract
{

    protected $_name = 'AppraiserEnterprise';
    protected $_id = 'Id';
    protected $_sequence = true;
    protected $_rowClass = 'DbTable_AppraiserEnterpriseRow';

    protected $_referenceMap = array(
        'User' => array(
          	'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        ),
        'Enterprise' => array(
          	'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        ),
        'Questionnaire' => array(
          	'columns' => 'QuestionnaireId',
            'refTableClass' => 'Questionnaire',
            'refColumns' => 'Id'
        )
    );
    
    protected $_dependentTables = array(
        'DbTable_User',
        'DbTable_Enterprise',
        'DbTable_Questionnaire'
    );

    public function getByUserIdAndProgramaId($userId,$programaId){
        $query = $this->select()->from('AppraiserEnterprise')
            ->where('UserId = ?', $userId)
            ->where('ProgramaId = ?',$programaId);

        return $this->fetchAll($query);
    }

}
