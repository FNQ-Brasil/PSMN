<?php

class DbTable_UserRole extends Vtx_Db_Table_Abstract
{
    protected $_name = 'User_Role';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'User' => array(
          	'columns' => 'UserId',
            'refTableClass' => 'User',
            'refColumns' => 'Id'
        ),
        'Role' => array(
          	'columns' => 'RoleId',
            'refTableClass' => 'Role',
            'refColumns' => 'Id'
        )
    );
    

}
