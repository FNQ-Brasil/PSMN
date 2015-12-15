<?php

class Management_PrivilegeController extends Vtx_Action_Abstract
{
    public function indexAction()
    {
        $this->_redirect($this->view->baseUrl('management/role'));
    }

    public function editAction()
    {
        $auth = Zend_Auth::getInstance()->getIdentity();
        /* @var $acl Model_Acl */ 
        $acl = Zend_Registry::get('acl');

        $roleRow = $acl->getRoleById($this->_getParam('role'));

        if (!$roleRow) {
            throw new Exception('Invalid role');
        }

        $this->view->resources = $acl->getAllResources();
        $this->view->acl = $acl; //Zend_Registry::get('acl');
        $this->view->roleRow = $roleRow;
        $this->view->auth = $auth;

        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $allowPrivileges = $this->_getParam('allowPrivileges', array());
        $updatePrivileges = $acl->updateRolePrivileges($auth, $roleRow, $allowPrivileges);
        //$this->view->acl = Zend_Registry::get('acl');
        //$this->view->messageSuccess = true;
        $this->_forward('index', 'role');
    }
}