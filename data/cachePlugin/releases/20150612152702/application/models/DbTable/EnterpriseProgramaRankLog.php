<?php

class DbTable_EnterpriseProgramaRankLog extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EnterpriseProgramaRankLog';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'EnterpriseProgramaRank' => array(
            'columns' => 'EnterpriseProgramaRankId',
            'refTableClass' => 'EnterpriseProgramaRank',
            'refColumns' => 'Id'
        )
    );
}
