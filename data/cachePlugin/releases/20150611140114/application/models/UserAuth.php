<?php

class Model_UserAuth
{
    protected $userId;
    protected $username;
    protected $firstName;
    protected $surname;

    protected $role;
    protected $roleId;
    protected $roleLongDescription;
    protected $user;

    /**
     * @var DbTable_UserRow
     */
    protected $userRow;
    
    public function __call($method, $args)
    {
        if(!preg_match('~^(set|get)([A-Z_])(.*)$~',$method,$matches)){return;}$property=strtolower(
        $matches[2]).$matches[3];if(!property_exists($this,$property)){throw new Exception("$property n/exists"
        );}if($matches[1]=='set'){$this->$property=$args[0];return $this;}return $this->$property;
    }

    public function isSupremeAdmin()
    {
        return (Zend_Registry::get('config')->acl->roleSupremeAdminId != $this->getRoleId())? false: true;
    }

    public function getUserRow()
    {
        if (!$this->getUser()) {
            $this->setUser(
                DbTable_User::getInstance()->getUserById($this->getUserId())
            );
        }
        return $this->getUser();
    }
    
    public function getEnterpriseRow() 
    {
        $modelUserLocality = new Model_UserLocality();
        $modelEnterprise = new Model_Enterprise();
        //$userRow = $this->getUserRow();
        $userLocalityRow = $modelUserLocality->getUserLocalityByUserId($this->getUserId());
     
        return ($userLocalityRow and $userLocalityRow->getEnterpriseId()) ?
            $modelEnterprise->getEnterpriseById($userLocalityRow->getEnterpriseId())
            : null;
    }
    
    public function getEnterpriseStatus() 
    {
        $enterpriseRow = $this->getEnterpriseRow();
        return ($enterpriseRow)? $enterpriseRow->getCurrentStatus():true;
    }
    
}