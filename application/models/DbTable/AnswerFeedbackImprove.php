<?php

class DbTable_AnswerFeedbackImprove extends Vtx_Db_Table_Abstract
{

    protected $_name = 'AnswerFeedbackImprove';
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
