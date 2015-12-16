<?php

class DbTable_AnswerFeedback extends Vtx_Db_Table_Abstract
{

    protected $_name = 'AnswerFeedback';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Answer' => array(
          	'columns' => 'AnswerId',
            'refTableClass' => 'Answer',
            'refColumns' => 'Id'
        )
    );

}
