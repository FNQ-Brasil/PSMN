<?php

class DbTable_Answer extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Answer';
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
        'DbTable_AnswerHistory'
    );

}
