<?php
/**
 * 
 * Controller_Group
 * @uses  
 * @author mcianci
 *
 */
class Management_GroupController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('set-group-to-enterprise', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        
        // Contextos dos actions
        $this->_helper->getHelper('ajaxContext')
            ->addActionContext('edit', array('json', 'html'))
            ->initContext();
        
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->aclModel = Zend_Registry::get('acl');
        
        $this->modelEnterprise = new Model_Enterprise();
        $this->modelGroup = new Model_Group();
        $this->modelGroupEnterprise = new Model_GroupEnterprise();
        $this->modelRegional = new Model_Regional();
        $this->modelUserLocality = new Model_UserLocality();
        $this->autoavaliacaoId = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;
        $this->dbTable_Regional = new DbTable_Regional();
    }

    public function indexAction()
    {
        $page = $this->_getParam('page');
        $count = $this->_getParam('count');
        $orderBy = $this->view->orderBy = $this->_getParam('orderBy');
        $filter = $this->_getParam('filter');
        $this->view->filter = $filter;
        $this->view->getAllGroups = $this->modelGroup->getAllGroups(
                null,$orderBy, $count, $page,$filter);
        
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->groupRowData = $groupRowData = $this->_getAllParams();
        $insert = $this->modelGroup->createGroup($groupRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->itemInsertSuccess = true;
        $this->_forward('index');
    }

    public function editAction()
    {
        $listAdmin = false;
        $auth = Zend_Auth::getInstance();
        $userLoggedRow  = $this->userAuth;
        $userLocality   = $this->modelUserLocality->getUserLocalityByUserId($userLoggedRow->getUserId());
        $roleRow = $this->aclModel->getRoleById($userLoggedRow->getRoleId(), false);
        
        $groupId = $this->_getParam('id');
        
        $page = $this->_getParam('page',1);
        $count = $this->_getParam('count',10);
        $orderBy = $this->view->orderBy = $this->_getParam('orderBy');
        $filter = $this->_getParam('filter');
        
        $groupRow = $this->modelGroup->getGroupById($groupId);
        if (!$groupRow) {
            throw new Exception('Invalid Group');
            return;
        }

        $this->view->groupRow = $groupRow;
        $this->view->groupRowData = array(
            'name'         => $groupRow->getName(),
            'description'   => $groupRow->getDescription(),
        );
        $this->view->getAllGroupEnterprise = $this->modelGroupEnterprise
                ->getAllGroupEnterpriseByGroupId($groupId,$count,$page,$orderBy,$filter);

        // Listagem dos Avaliadores pelo admin 
        if ($roleRow->getIsSystemAdmin() == 1) { // eh admin 
            // lista Empresas
            $getAllEnterprise = $this->modelEnterprise->getAllWithRa($this->autoavaliacaoId, $count, $page,$filter);
            $this->view->getAllEnterpriseByRegionalServiceArea = $getAllEnterprise;
            $listAdmin = true;
        }
         
        // Listagem dos Avaliadores pelo gestor 
        if ($this->aclModel->isAllowed($auth->getIdentity()->getRole(), 'management:appraiser', 'list-appraiser-by-regional-service-area')) 
        {
            if (!$listAdmin) {
                $userLocality = $this->modelUserLocality->getUserLocalityByUserId($userLoggedRow->getUserId());
                $regionalIdUserLogged = $userLocality->getRegionalId();
                
                // Listar Empresas por RegionalSA
                $getAllEnterpriseByRegionalServiceArea = $this->modelEnterprise
                    ->getAllEnterpriseByRegionalServiceArea($regionalIdUserLogged, $count, $page, $filter);
                $this->view->getAllEnterpriseByRegionalServiceArea = $getAllEnterpriseByRegionalServiceArea;
                //--
            }
        }
        
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $this->view->groupRowData = $groupRowData = $this->_getAllParams();

        $update = $this->modelGroup->updateGroup($groupRow,$groupRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        
        $this->view->itemUpdateSuccess = true;
        $this->_forward('index');
    }
    
    public function connectAction()
    {
        $State = new Model_State();
        $City = new Model_City();
        $Neighborhood = new Model_Neighborhood();
        
        $listAdmin = false;
        $auth = Zend_Auth::getInstance();
        $userLoggedRow  = $this->userAuth;
        $userLocality   = $this->modelUserLocality->getUserLocalityByUserId($userLoggedRow->getUserId());
        $roleRow = $this->aclModel->getRoleById($userLoggedRow->getRoleId(), false);
        
        $groupId = $this->_getParam('id');
        $groupRow = $this->modelGroup->getGroupById($groupId);
        if (!$groupRow) {
            throw new Exception('Invalid Group');
            return;
        }
        
        $page = $this->_getParam('page',1);
        $count = $this->_getParam('count',10);
        $orderBy = $this->view->orderBy = $this->_getParam('orderBy');
        $filter = $this->_getParam('filter',null);
        $this->view->filter = $filter;
        $this->view->getAllStates = $State->getAll();
        if (isset($filter['state_id']) and !empty($filter['state_id'])) {
            $this->view->getAllCities = $City->getAllCityByStateId($filter['state_id']);
        }
        if (isset($filter['city_id']) and !empty($filter['city_id'])) {
            $this->view->getAllNeighborhoods = $Neighborhood->getAllNeighborhoodByCityId($filter['city_id']);
        }
        /*
        if(isset($filter['state_id'])) {
            die($filter['state_id']);
        }
         */
        $this->view->groupRow = $groupRow;

        // Listagem dos Avaliadores pelo admin 
        if ($roleRow->getIsSystemAdmin() == 1) { // eh admin 
            // lista Empresas
            $getAllEnterprise = $this->modelEnterprise->getAllWithRa($this->autoavaliacaoId, $count, $page,$filter);
            $this->view->getAllEnterpriseByRegionalServiceArea = $getAllEnterprise;
            $this->view->getAllRegional = $this->modelRegional->getAll();
            $listAdmin = true;
        }
         
        // Listagem dos Avaliadores pelo gestor 
        if ($this->aclModel->isAllowed($auth->getIdentity()->getRole(), 'management:appraiser', 'list-appraiser-by-regional-service-area')) 
        {
            if (!$listAdmin) {
                $userLocality = $this->modelUserLocality->getUserLocalityByUserId($userLoggedRow->getUserId());
                $regionalIdUserLogged = $userLocality->getRegionalId();
                
                $this->view->getAllRegional = $this->dbTable_Regional->getAllRegionalByOneRegionalServiceArea(null,$regionalIdUserLogged,'all',$filter);
                
                // Listar Empresas por RegionalSA
                $getAllEnterpriseByRegionalServiceArea = $this->modelEnterprise
                    ->getAllEnterpriseByRegionalServiceArea($regionalIdUserLogged, $count, $page, $filter);
                $this->view->getAllEnterpriseByRegionalServiceArea = $getAllEnterpriseByRegionalServiceArea;
                //--
            }
        }
        
        if (!$this->getRequest()->isPost()) {
            return;
        }

    }
    
    public function setGroupToEnterpriseAction()
    {
        $enterpriseId = $this->_getParam('enterpriseId');
        $groupId = $this->_getParam('groupId');
        $checked = $this->_getParam('checked');
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        $objGroupEnt = $this->modelGroupEnterprise->setGroupToEnterprise($groupId, $enterpriseId, $checked);
        $this->view->itemSuccess = true;
    }

    public function deleteAction()
    {
        $groupId = $this->_getParam('id');
        $groupRow = $this->modelGroup->getGroupById($groupId);
        if (!$groupRow) {
            throw new Exception('Invalid Group');
        }

        $delete = $this->modelGroup->deleteGroup($groupRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            $this->_forward('index');
            return;
        }
        $this->view->itemDeleteSuccess = true;
        $this->_forward('index');
    }

}