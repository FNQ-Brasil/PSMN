<?php

class DbTable_CheckerEnterprise extends Vtx_Db_Table_Abstract
{

    protected $_name = 'CheckerEnterprise';
    protected $_id = 'Id';
    protected $_sequence = true;
    protected $_rowClass = 'DbTable_CheckerEnterpriseRow';

    protected $_referenceMap = array(
        'User' => array(
          	'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        ),
        'Enterprise' => array(
          	'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        )
    );
    
    protected $_dependentTables = array(
        'DbTable_User',
        'DbTable_Enterprise'
    );

}
