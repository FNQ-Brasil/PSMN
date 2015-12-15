<?php

class DbTable_Responsability extends Vtx_Db_Table_Abstract
{

    protected $_name = 'Responsability';

    protected $_id = 'Id';

    protected $_sequence = true;

    protected $_referenceMap = array(
        'Enterprise' => array(
            'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        ),
        'User' => array(
            'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        ),
        'ResponsabilityType' => array(
            'columns' => 'ResponsabilityTypeId',
            'refTableClass' => 'ResponsabilityType',
            'refColumns' => 'Id'
        ),
        'Regional' => array(
            'columns' => 'RegionalId',
            'refTableClass' => 'Regional',
            'refColumns' => 'Id'
        )
    );
    
}
