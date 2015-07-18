<?php

class DbTable_RoleResourcePrivilege extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Role_Resource_Privilege';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Role' => array(
          	'columns' => 'RoleId',
            'refTableClass' => 'Role',
            'refColumns' => 'Id'
        ),
        'Resource' => array(
          	'columns' => 'ResourceId',
            'refTableClass' => 'Resource',
            'refColumns' => 'Id'
        )
    );
    
    public function getAllAllow()
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('rrp' => $this->_name),
                array('PrivilegeDescription' => 'Privilege')
            )
            ->joinLeft(
                array('re' => 'Resource'), 're.Id = rrp.ResourceId',
                array('Module', 'Controller' => 'Description')
            )
            ->joinLeft(
                array('r' => 'Role'), 'r.Id = rrp.RoleId',
                array('RoleDescription' => 'Description')
            )
            ->order('r.Description')
            ->order('re.Module')
            ->order('re.Description');

        return $this->fetchAll($query);
    }
}