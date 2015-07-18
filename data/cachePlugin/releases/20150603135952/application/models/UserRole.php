<?php
/**
 * 
 * Model_UserRole
 * @uses  
 * @author mcianci
 *
 */
class Model_UserRole
{

    public $dbTable_UserRole = "";
    
    function __construct() {
        $this->dbTable_UserRole = new DbTable_UserRole();
    }

    function getUserRoleByUserId($userId)
    {
        return $this->dbTable_UserRole->fetchRow(array('UserId = ?' => $userId));
    }
    
    function getUserRoleByRoleId($roleId)
    {
        return $this->dbTable_UserRole->fetchAll(array('RoleId = ?' => $roleId));
    }

}