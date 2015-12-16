<?php
class DbTable_QuestionnaireExecution extends Vtx_Db_Table_Abstract
{
    protected $_name = 'QuestionnaireExecution';
    protected $_primary = 'Id';

    protected $_referenceMap = array(
	     'Cooperative' => array(
             'columns' => array('CooperativeId'),
             'refTableClass' => 'Cooperative',
             'refColumns' => array('Id')
         ),
         'User' => array(
             'columns' => array('UserId'),
             'refTableClass' => 'User',
             'refColumns' => array('Id')
         ),
         'Questionnaire' => array(
             'columns' => array('QuestionnaireId'),
             'refTableClass' => 'Questionnaire',
             'refColumns' => array('Id')
         )
    );
    
}