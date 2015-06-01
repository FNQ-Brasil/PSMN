<?php

class DbTable_RoleQuestionnaire extends Vtx_Db_Table_Abstract
{
    protected $_name = 'RoleQuestionnaire';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Role' => array(
          	'columns' => 'RoleId',
            'refTableClass' => 'Role',
            'refColumns' => 'Id'
        ),
        'Questionnaire' => array(
          	'columns' => 'QuestionnaireId',
            'refTableClass' => 'Questionnaire',
            'refColumns' => 'Id'
        )
    );

    protected $_dependentTables = array(
        'DbTable_Role',
        'DbTable_Questionnaire'
    );
      
}