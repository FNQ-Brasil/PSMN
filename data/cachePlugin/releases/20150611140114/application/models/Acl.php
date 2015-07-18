<?php
/**
 * 
 * Model_Acl
 * @uses Zend_Acl
 * @author tsouza
 *
 */
class Model_Acl extends Zend_Acl
{
    private $_noAuth;
    private $_noAcl;
    public static $DEFAULT_PRIVILEGES = array(
        'index', 'insert', 'edit', 'delete'
    );
    protected $_messagesError = array(
        'roleNameExists' => 'Por favor, escolha um outro nome para este papel.',
    );

    public function __construct($populateRolesAndResource = false)
    {
        if ($populateRolesAndResource) {
            $this->_addRoles();
            $this->_addResources();
        }
    }

    public function _addRoles()
    {
        $roles = DbTable_Role::getInstance()->getAllRoles(true);
        foreach ($roles as $role) {
            $this->_addRole($role->getDescription());
        }
        
        return $this;
    }

    public function _addRole($roleName, $parents = null)
    {
        if (!$this->hasRole($roleName)) {
            $parents = !empty($parents)? explode(',', $parents) : null;
            $this->addRole(new Zend_Acl_Role($roleName), $parents);
        }
        
        return $this;
    }
    
    public function _addResources()
    {
        $resources = DbTable_RoleResourcePrivilege::getInstance()->getAllAllow();
        foreach ($resources as $resource) {
            $resourceName = $resource->getModule() . ':' . $resource->getController();
            $this->_addResource(
                $resourceName, $resource->getPrivilegeDescription(),
                $resource->getRoleDescription()
            );
        }

        return $this;
    }
    
    public function _addResource($resource, $privilege, $role)
    {
        if (!$this->has($resource)) {                   	
            $this->add(new Zend_Acl_Resource($resource));
        }

        $this->allow($role, $resource, $privilege);

        return $this;
    }
    
    /**
     * Cria novo recurso com permissao para os privlegios padrões para o usuário superAdmin
     * @param type $resourceName
     * @param type $adicionalPrivilege
     * @return type
     */
    public function createResource($module, $controller, $adicionalPrivilege = null)
    {
        /* Módulo default qualquer papel pode acessar */
        if ($module == 'default') {
            return;
        }
        
        $privileges = self::$DEFAULT_PRIVILEGES;
        if ($adicionalPrivilege and !in_array($adicionalPrivilege, $privileges)) {
            $privileges[] = $adicionalPrivilege;
        }
        $resourceName = $module . ':' . $controller;

        DbTable_Resource::getInstance()->getAdapter()->beginTransaction();

        try {
            $resource = DbTable_Resource::getInstance()->createRow()
                ->setModule($module)
                ->setDescription($controller)
                ->setLongDescription($resourceName);
            $resource->save();

            $roleSupremeAdminId = Zend_Registry::get('config')->acl->roleSupremeAdminId;
            foreach ($privileges as $privilege) {
                $roleResourcePrivilege = DbTable_RoleResourcePrivilege::getInstance()->createRow()
                    ->setRoleId($roleSupremeAdminId)
                    ->setResourceId($resource->getId())
                    ->setPrivilege($privilege);
                $roleResourcePrivilege->save();
            }

            self::clearAclCache();
            
            DbTable_Resource::getInstance()->getAdapter()->commit();
            return $resource;

        } catch (Exception $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public function createRole($data, $roleParentId)
    {
        DbTable_Resource::getInstance()->getAdapter()->beginTransaction();

        try {
            $data['longDescription'] = $data['roleName'];
            $data = $this->_filterInputRole($data)->getUnescaped();
            
            $verifyName = DbTable_Role::getInstance()->fetchRow(array(
                'Description = ?' => $data['roleName']
            ));
            
            if ($verifyName) {
                throw new Vtx_UserException($this->_messagesError['roleNameExists']);
            }

            $roleRow = DbTable_Role::getInstance()->createRow()
                ->setDescription($data['roleName'])
                ->setLongDescription($data['longDescription'])
                ->setParentRole($roleParentId);
            $roleRow->save();

            self::clearAclCache();
            
            DbTable_Resource::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }  
    
    public function updateRole(DbTable_RoleRow $roleRow, $data)
    {   
        DbTable_Resource::getInstance()->getAdapter()->beginTransaction();

        try {
            $data['longDescription'] = $data['roleName'];
            $data = $this->_filterInputRole($data)->getUnescaped();
            
            $verifyName = DbTable_Role::getInstance()->fetchRow(array(
                'Description = ?' => $data['roleName'],
                'Id <> ?' => $roleRow->getId()
            ));
            
            if ($verifyName) {
                throw new Vtx_UserException($this->_messagesError['roleNameExists']);
            }

            $roleRow->setDescription($data['roleName'])
                ->setLongDescription($data['longDescription']);
            $roleRow->save();

            self::clearAclCache();
            
            DbTable_Resource::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public function deleteRole(DbTable_RoleRow $roleRow)
    {   
        DbTable_Resource::getInstance()->getAdapter()->beginTransaction();

        try {
            /* Deleta todos os privilégios do papel */
            $whereDelete = array('RoleId = ?' => $roleRow->getId());
            DbTable_RoleResourcePrivilege::getInstance()->delete($whereDelete);
            
            $roleRow->delete();

            self::clearAclCache();
            
            DbTable_Resource::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    protected function _filterInputRole($params)
    {      
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'roleName' => array(
                    'Alpha',
                    array('StringToLower', 'encoding' => 'UTF-8'),
                    new Vtx_Filter_Transliterate() //custom filter
                )
            ),
            array( //validates
                'longDescription' => array('NotEmpty'),
                'roleName' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['roleNameExists']
                )
            ),
            $params,
            array('presence' => 'required')
        );

        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }

        return $input;
    }
    
    
    public static function clearAclCache()
    {
        $cache = Zend_Registry::get('cache_acl');   
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        $cache->clean(Zend_Cache::CLEANING_MODE_OLD); 

        $cacheSite = new Vtx_Cache_MPE_SiteCache();
        $acl = $cacheSite->fazCacheAcl();
        Zend_Registry::getInstance()->set('acl', $acl);
        return;
    }
    
    public function getAllResources()
    {
        $resources = DbTable_Resource::getInstance()->fetchAll(null, 'LongDescription');
        return $resources;
    }
    
    /**
     * 
     * @param type $roleId
     * @return type
     */
    public function getRoleById($roleId, $verifyPermissionListSystemAdmin = true)
    {
        $loggedUser = Zend_Auth::getInstance()->getIdentity();

        $where = array('Id = ?' => $roleId);
        if ($verifyPermissionListSystemAdmin and !$loggedUser->isSupremeAdmin()) {
            $where['IsSystemAdmin = ?'] = '0';
        }
        return DbTable_Role::getInstance()->fetchRow($where);
    }
    
    public function getAllRoles($filter = null)
    {
        $loggedUser = Zend_Auth::getInstance()->getIdentity();
        return DbTable_Role::getInstance()->getAllRoles(
            $loggedUser->isSupremeAdmin()? true : false, $filter
        );
    }

    public function getAppraiserRoles(){
        return DbTable_Role::getInstance()->getAppraiserRoles();
    }

    /**
     * 
     * @param type $userLogged
     * @param DbTable_RoleRow $roleRow
     * @param type $allowPrivileges
     */
    public function updateRolePrivileges(
        $userLogged, DbTable_RoleRow $roleRow, $allowPrivileges = array() 
    ) {
        
        DbTable_Resource::getInstance()->getAdapter()->beginTransaction();

        try {
            /* Deleta todos os privilégios do papel */
            $whereDelete = array('RoleId = ?' => $roleRow->getId());
            DbTable_RoleResourcePrivilege::getInstance()->delete($whereDelete);

            foreach ($allowPrivileges as $allowPrivilege) {
                list($resourceId, $privilege) = explode(':', $allowPrivilege, 2);

                /* o usuário logado tem este privilégio? */
                $resource = DbTable_Resource::getInstance()->find($resourceId)->current();
                $allowEditPrivileges = $this->isAllowed(
                    $userLogged->getRole(), $resource->getResourceName(), $privilege
                );

                if (!$allowEditPrivileges) {
                    throw new Exception('Sem permissão para editar privilegio');
                }

                $roleResourcePrivilege = DbTable_RoleResourcePrivilege::getInstance()->createRow()
                    ->setRoleId($roleRow->getId())
                    ->setResourceId($resourceId)
                    ->setPrivilege($privilege);
                $roleResourcePrivilege->save();
            }

            self::clearAclCache();
            
            DbTable_Resource::getInstance()->getAdapter()->commit();
            return $this;

        } catch (Exception $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public function setUserRole($userId,$roleId)
    {
        $verifyUserRole = DbTable_UserRole::getInstance()->fetchRow(array(
            'UserId = ?' => $userId,
            'RoleId = ?' => $roleId
        ));
        if ($verifyUserRole) {
            return array(
                'status' => false,
                'messageError' => 'Regra já existe.'
                    );
        }
        $userRoleData = DbTable_UserRole::getInstance()->createRow()
            ->setUserId($userId)
            ->setRoleId($roleId);
        $userRoleData->save();
        return array(
            'status' => true
        );
    }
    
    public function updateUserRole($userRoleRow,$roleId)
    {
        $userRoleRow->setRoleId($roleId);
        $userRoleRow->save();
        return array(
            'status' => true
        );
    }  

}