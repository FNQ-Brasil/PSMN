<?php
/**
 * 
 * Controller_RoleQuestionnaire
 * @uses  
 * @author mcianci
 *
 */
class Management_RoleQuestionnaireController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('insert', array('json'))
             ->addActionContext('index', array('json'))
             ->addActionContext('edit', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        /*
        // Contextos dos actions
        $this->_helper->getHelper('ajaxContext')
            ->addActionContext('index', array('html'))
            ->initContext();
        
        */
        $this->aclModel = Zend_Registry::get('acl');
        $this->modelRoleQuestionnaire       = new Model_RoleQuestionnaire();
        $this->dbTable_RoleQuestionnaire    = new DbTable_RoleQuestionnaire();
        $this->modelQuestionnaire = new Model_Questionnaire();
    }
    public function indexAction()
    {
        $where = null; 
        $order = $this->view->orderBy = $this->_getParam('orderBy');
        $count = $this->_getParam('count', 10);
        $offset = $this->_getParam('page',null);
        $filter = $this->view->filter = $this->_getParam('filter');
        $this->view->getAllRoleQuestionnaire = $this->modelRoleQuestionnaire
            ->getAllRoleQuestionnaire($where,$order,$count,$offset,$filter);
        $this->view->getAllRoles = $this->aclModel->getAllRoles();
        $this->view->getAllQuestionnaires = $this->modelQuestionnaire->getAll();
    }
    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        //$this->view->getAllRoles = $this->aclModel->getAllRoles();
        //$this->view->getAllQuestionnaires = $this->modelQuestionnaire->getAll();
        
        $roleId = $this->_getParam('role_id',null);
        $questionnaireId = $this->_getParam('questionnaire_id',null);
        
        if ($roleId and $questionnaireId) {
            $roleRow = $this->aclModel->getRoleById($roleId, false);
            $questionnaireRow = $this->modelQuestionnaire->getQuestionnaireById($questionnaireId);
            $this->view->roleQuestionnaireRowData = array(
                'role_id'               => $roleRow->getId(),
                'role_name'             => $roleRow->getLongDescription(),
                'questionnaire_id'      => $questionnaireRow->getId(),
                'questionnaire_name'    => $questionnaireRow->getDescription(),
                'operation_beginning'   => $questionnaireRow->getOperationBeginning(),
                'operation_ending'      => $questionnaireRow->getOperationEnding()
            );
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $roleQuestionnaireData = $this->_getAllParams();
        $insert = $this->modelRoleQuestionnaire->createRoleQuestionnaire($roleQuestionnaireData['rq']);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        
        //$this->view->lastInsertId = $insert['lastInsertId'];
        $this->view->redirectUrlroleQuestionnaire = $this->view
            ->baseUrl('/management/role/index/questionnaire_id/'.$roleQuestionnaireData['rq']['questionnaire_id']);
        $this->view->itemSuccess = true;
    }

    public function editAction()
    {
        //$this->view->getAllRoles = $this->aclModel->getAllRoles();
        //$this->view->getAllQuestionnaires = $this->modelQuestionnaire->getAll();
        
        $roleQuestionnaireId = $this->_getParam('id');
        $roleQuestionnaireRow = $this->modelRoleQuestionnaire->getRoleQuestionnaireById($roleQuestionnaireId);
        
        if (!$roleQuestionnaireRow) {
            throw new Exception('Role Questionnaire Invalid');
        }
        
        $this->view->roleQuestionnaireRow = $roleQuestionnaireRow;
        
        $roleRow = $this->aclModel->getRoleById($roleQuestionnaireRow->getRoleId());
        $questionnaireRow = $this->modelQuestionnaire->getQuestionnaireById($roleQuestionnaireRow->getQuestionnaireId());
        
        $this->view->roleQuestionnaireRowData = array(
            'role_id'            => $roleQuestionnaireRow->getRoleId(),
            'role_name'          => $roleRow->getLongDescription(),
            'questionnaire_id'   => $roleQuestionnaireRow->getQuestionnaireId(),
            'questionnaire_name' => $questionnaireRow->getDescription(),
            'start_date'         => $roleQuestionnaireRow->getStartDate(),
            'end_date'           => $roleQuestionnaireRow->getEndDate(),
            'operation_beginning'=> $questionnaireRow->getOperationBeginning(),
            'operation_ending'   => $questionnaireRow->getOperationEnding()
        );
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $roleQuestionnaireRowData = $this->_getAllParams();
        $this->view->roleQuestionnaireRowData = $roleQuestionnaireRowData['rq'];
        $update = $this->modelRoleQuestionnaire->updateRoleQuestionnaire($roleQuestionnaireRow, $roleQuestionnaireRowData['rq']);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->redirectUrlroleQuestionnaire = $this->view
            ->baseUrl('/management/role/index/questionnaire_id/'.$roleQuestionnaireRow->getQuestionnaireId());
        $this->view->itemSuccess = true;
    }
    
    public function deleteAction()
    {
        $roleQuestionnaireId = $this->_getParam('id');
        $questionnaireId = $this->_getParam('questionnaire_id');
        $roleQuestionnaireRow = $this->modelRoleQuestionnaire->getRoleQuestionnaireById($roleQuestionnaireId);
        if (!$roleQuestionnaireRow) {
            throw new Exception('Invalid Question');
        }
        $delete = $this->modelRoleQuestionnaire->deleteRoleQuestionnaire($roleQuestionnaireRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            return;
        }
        $this->view->redirectUrlroleQuestionnaire = $this->view
            ->baseUrl('/management/role/index/questionnaire_id/'.$questionnaireId);
        $this->view->itemSuccess = true;
        $this->_redirect($this->view->redirectUrlroleQuestionnaire);
    }
}