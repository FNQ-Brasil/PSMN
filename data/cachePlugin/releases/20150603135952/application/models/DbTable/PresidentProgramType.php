<?php

class DbTable_PresidentProgramType extends Vtx_Db_Table_Abstract
{
    protected $_name = 'PresidentProgramType';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'PresidentProgram' => array(
            'columns' => 'PresidentProgramId',
            'refTableClass' => 'PresidentProgram',
            'refColumns' => 'Id'
        )
    );
}
