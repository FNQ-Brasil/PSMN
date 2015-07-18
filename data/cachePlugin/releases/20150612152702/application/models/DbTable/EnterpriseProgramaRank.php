<?php

class DbTable_EnterpriseProgramaRank extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EnterpriseProgramaRank';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Enterprise' => array(
            'columns' => 'IdKey',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        )
    );
}
