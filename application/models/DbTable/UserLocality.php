<?php

class DbTable_UserLocality extends Vtx_Db_Table_Abstract
{
    protected $_name = 'UserLocality';
    protected $_id = 'Id';
    protected $_sequence = true;

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
        ),
        'Regional' => array(
          	'columns' => 'RegionalId',
            'refTableClass' => 'Regional',
            'refColumns' => 'Id'
        )
    );
}