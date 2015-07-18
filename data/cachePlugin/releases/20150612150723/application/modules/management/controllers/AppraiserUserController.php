<?php

class Management_AppraiserUserController extends Vtx_Action_Abstract {
    protected $AppraiserUser;

    public function init()
    {
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        if(!$this->userAuth) return;

        $this->aclModel = Zend_Registry::get('acl');
        $this->roleRow = $this->aclModel->getRoleById($this->userAuth->getRoleId(), false);

        if ($this->userAuth->getUserId() != 33525 and !$this->roleRow->getIsSystemAdmin()){
            throw new Exception('access denied');
        }

        $this->_helper->contextSwitch()
            ->addActionContext('change-status', 'json')
            ->setAutoJsonSerialization(true)
            ->initContext();

        $this->roleAppraiserId = Zend_Registry::get('config')->acl->roleAppraiserId;

        $this->userModel = new Model_User();
        $this->AppraiserUser = new Model_AppraiserUser();
        $this->regionalModel = new Model_Regional();
        $this->dbTable_Regional = new DbTable_Regional();
        $this->userLocalityModel = new Model_UserLocality();
        $this->stateModel = new Model_State();
    }

    public function indexAction() {
        $this->view->roles = $this->aclModel->getAppraiserRoles();
        $this->view->states = $this->stateModel->getAll();
        $this->view->appraiserStatuses = $this->AppraiserUser->getStatuses();

        $regionalId = null;
        $page = $this->_getParam('page',1);
        $limit = $this->_getParam('count',10);
        $filter = $this->_getParam('filter',null);
        $this->view->filter = $filter;

        $this->view->appraisers = $this->AppraiserUser->getAllBy($filter['name'], $filter['login'], $filter['cpf'],
            $filter['role_id'], $filter['uf_id'], $filter['status'], $regionalId, $limit, $page);
    }

    public function changeStatusAction(){
        $userId = $this->_getParam('user_id');
        $status = $this->_getParam('status');
        $responsibleId = $this->userAuth->getUserId();

        try {
            $this->AppraiserUser->changeStatus($userId, $status, $responsibleId);
            $status = 0;
            $message = 'O avaliador foi atualizado com sucesso.';
        } catch(Exception $e) {
            $status = -1;
            $message = 'Não foi possível atualizar o avaliador: '.$e->getMessage();
        }

        $this->_helper->json(array('status' => $status, 'message' => $message));
    }
}
