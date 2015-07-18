<?php

class DbTable_Resource extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Resource';
    protected $_id = 'Id';
    protected $_sequence = true;
	protected $_rowClass = 'DbTable_ResourceRow';

    /*
    protected $_referenceMap = array(
        'ParentResource' => array(
          	'columns' => 'ParentResource',
            'refTableClass' => 'ParentResource',
            'refColumns' => 'Id'
        )
    );*/
    protected $_dependentTables = array(
        'DbTable_Resource',
        'DbTable_RoleResourcePrivilege'
    );

}
