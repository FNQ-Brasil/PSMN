<?php

class DbTable_Contact extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Contact';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Enterprise' => array(
          	'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        ),
        'ContactType' => array(
          	'columns' => 'ContactTypeId',
            'refTableClass' => 'ContactType',
            'refColumns' => 'Id'
        ),
        'User' => array(
          	'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        ),
        'Regional' => array(
          	'columns' => 'RegionalId',
            'refTableClass' => 'Regional',
            'refColumns' => 'Id'
        )
    );
    
}
