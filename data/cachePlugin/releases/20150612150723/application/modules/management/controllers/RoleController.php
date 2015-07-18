<?php

class Management_RoleController extends Vtx_Action_Abstract
{
    /**
     * Px
     *
     * @var Model_Acl
     */
    protected $aclModel;

    /**
     * P
     *
     * @var Model_UserAuth
     */
    protected $userAuth;
    
    public function init()
    {
        $this->aclModel = Zend_Registry::get('acl');
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->modelQuestionnaire = New Model_Questionnaire;
    }

    public function indexAction()
    {
        $filter['questionnaire_id'] = $this->view->questionnaire_id = $this->_getParam('questionnaire_id');
        $this->view->roles = $this->aclModel->getAllRoles($filter);
        $this->view->getAllQuestionnaires = $this->modelQuestionnaire->getAll();
        //$this->view->roles = $this->aclModel->getAllRoleQuestionnaire
        $this->view->loggedUser = $this->userAuth;
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
     
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $this->view->roleRowData = $roleRowData = $this->_getAllParams();

        $insert = $this->aclModel->createRole($roleRowData, $this->userAuth->getRoleId());
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->roleInsertSucess = true;
        $this->_forward('index');
    }

    public function editAction()
    {
        $roleId = $this->_getParam('id');
        $roleRow = $this->aclModel->getRoleById($roleId);
        if (!$roleRow or $roleRow->getIsSystemRole()) {
            throw new Exception('Invalid role');
        }

        $this->view->roleRow = $roleRow;
        $this->view->roleRowData = array(
            'roleName' => $roleRow->getLongDescription()
        );

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $this->view->roleRowData = $roleRowData = $this->_getAllParams();

        $update = $this->aclModel->updateRole($roleRow, $roleRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->roleUpdateSucess = true;
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $roleId = $this->_getParam('id');
        $roleRow = $this->aclModel->getRoleById($roleId);
        if (!$roleRow or $roleRow->getIsSystemRole()) {
            throw new Exception('Invalid role');
        }

        $delete = $this->aclModel->deleteRole($roleRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->_forward('index');
    }
}