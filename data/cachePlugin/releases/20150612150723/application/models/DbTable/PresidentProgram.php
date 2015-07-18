<?php

class DbTable_PresidentProgram extends Vtx_Db_Table_Abstract
{
    protected $_name = 'PresidentProgram';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'President' => array(
            'columns' => 'PresidentId',
            'refTableClass' => 'President',
            'refColumns' => 'Id'
        )
    );
}
