<?php

class DbTable_EnterpriseReport extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EnterpriseReport';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Enterprise' => array(
            'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        )
    );
}
