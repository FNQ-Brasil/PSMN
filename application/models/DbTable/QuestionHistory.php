<?php

class DbTable_QuestionHistory extends Vtx_Db_Table_Abstract
{
    protected $_name = 'QuestionHistory';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Question' => array(
          	'columns' => 'QuestionId',
            'refTableClass' => 'Question',
            'refColumns' => 'Id'
        )
    );
    



}
