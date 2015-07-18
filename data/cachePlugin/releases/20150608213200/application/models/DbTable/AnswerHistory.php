<?php

class DbTable_AnswerHistory extends Vtx_Db_Table_Abstract
{

    protected $_name = 'AnswerHistory';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Answer' => array(
          	'columns' => 'AnswerId',
            'refTableClass' => 'Answer',
            'refColumns' => 'Id'
        ),
        'Alternative' => array(
          	'columns' => 'AlternativeId',
            'refTableClass' => 'Alternative',
            'refColumns' => 'Id'
        )
    );

}
