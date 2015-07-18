<?php

class DbTable_Role extends Vtx_Db_Table_Abstract
{
    //protected $_adapter = 'db2';
    protected $_name = 'Role';
    protected $_id = 'Id';
    protected $_sequence = true;
	protected $_rowClass = 'DbTable_RoleRow';

    protected $_dependentTables = array(
        'DbTable_RoleResourcePrivilege',
        'DbTable_UserRole'
    );
    
    public function getAllRoles($permitShowRoleSystemAdmin = false, $filter = null)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('r' => $this->_name),
                array('Id', 'Description', 'LongDescription', 'ParentRole', 'IsSystemRole')
            )
            ->joinLeft(
                array('r2' => 'Role'), 'r2.Id = r.ParentRole',
                array('ParentRoleDescription' => 'Description')
            );
     
        $queryJoinLeftRQ = 'RQ.RoleId = r.Id and `RQ`.`QuestionnaireId` is null';
        if (isset($filter['questionnaire_id']) and $filter['questionnaire_id']) {
            $queryJoinLeftRQ = 'RQ.RoleId = r.Id and `RQ`.`QuestionnaireId` = ' . $filter['questionnaire_id'];
        }

        $query->joinLeft(
            array('RQ' => 'RoleQuestionnaire'),
            $queryJoinLeftRQ,
             array('RoleQuestionnaireId' => 'Id', 'QuestionnaireId', 'StartDate','EndDate')
        ); 
        
        if (!$permitShowRoleSystemAdmin) {
            $query->where('r.IsSystemAdmin = ?', '0');
        }

        $query->order('r.Description');
        //Zend_Debug::dump($query->__toString());
        
        return $this->fetchAll($query);
    }

    public function getAppraiserRoles() {
        $rolesDescription = array('avaliador','verificador');

        $query = $this->select()
            ->from(
                array('r' => $this->_name),
                array('Id', 'Description', 'LongDescription', 'ParentRole', 'IsSystemRole')
            )->where('LongDescription in (?)', $rolesDescription);

        return $this->fetchAll($query);
    }
    
    public function getRoleByLongDescription($longDescription, $permitShowRoleSystemAdmin = false)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('r' => $this->_name),
                array('Id', 'Description', 'LongDescription', 'ParentRole', 'IsSystemRole')
            )
            ->where('r.LongDescription = ?', $longDescription)
            ->joinLeft(
                array('r2' => 'Role'), 'r2.Id = r.ParentRole',
                array('ParentRoleDescription' => 'Description')
            )
            ->order('r.Description');
        
        if (!$permitShowRoleSystemAdmin) {
            $query->where('r.IsSystemAdmin = ?', '0');
        }

        return $this->fetchRow($query);
    }
    
}