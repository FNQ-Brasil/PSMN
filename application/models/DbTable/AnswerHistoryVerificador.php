<?php

class DbTable_AnswerHistoryVerificador extends Vtx_Db_Table_Abstract
{

    protected $_name = 'AnswerHistoryVerificador';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'AnswerVerificador' => array(
            'columns' => 'AnswerId',
            'refTableClass' => 'AnswerVerificador',
            'refColumns' => 'Id'
        ),
        'Alternative' => array(
            'columns' => 'AlternativeId',
            'refTableClass' => 'Alternative',
            'refColumns' => 'Id'
        )
    );

}
