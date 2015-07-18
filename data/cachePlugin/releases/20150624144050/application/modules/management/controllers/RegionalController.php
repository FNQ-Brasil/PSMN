<?php

class Management_RegionalController extends Vtx_Action_Abstract
{

    public function init()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('insert', array('json'))
             ->addActionContext('edit', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        
        $this->aclModel = Zend_Registry::get('acl');
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->roleRow = $this->aclModel->getRoleById($this->userAuth->getRoleId(), false);
        $this->userLocalityModel = new Model_UserLocality();
        $this->roleAppraiserId = Zend_Registry::get('config')->acl->roleAppraiserId;
                
        $this->regionalModel = new Model_Regional();
        $this->stateModel = new Model_State();
        $this->cityModel = new Model_City();
        $this->dbTable_City = new DbTable_City();
        $this->neighborhoodModel = new Model_Neighborhood();
        $this->serviceAreaModel = new Model_ServiceArea();
    }

    public function indexAction()
    {
       
        //Listar Regional
        if ($this->_getParam('listar')=='ok') {
            
            $this->_helper->Regional->regionalModel = $this->regionalModel;
            $this->_helper->Regional->stateModel = $this->stateModel;
            $this->_helper->Regional->serviceAreaModel = $this->serviceAreaModel;
            $this->_helper->Regional->roleRow = $this->roleRow;
            $this->_helper->Regional->aclModel = $this->aclModel;
            $this->_helper->Regional->userAuth = $this->userAuth;
            $this->_helper->Regional->userLocalityModel = $this->userLocalityModel;

            $this->_helper->Regional->listarEditarRegional($this->_getAllParams(), $this->view);            
            
            $this->_helper->viewRenderer->setRender('edit');
            return;
        }
              
        $State = new Model_State();
        $this->view->getAllStates = $State->getAll();
        $listAdmin = false;
        
        $page = $this->_getParam('page');
        $count = $this->_getParam('count', 150);
        $orderBy = $this->view->orderBy = $this->_getParam('orderBy');
        $filter = $this->view->filter = $this->_getParam('filter');
        
        if ($this->roleRow->getIsSystemAdmin() == 1) {
            $this->view->getAll = $this->regionalModel->getAll(
                null, $orderBy, $count, $page, $filter
            );
            $listAdmin = true;
        }

        
        if ($this->aclModel->isAllowed($this->userAuth->getRole(), 'management:regional', 'list-address-by-regional')) {
            if (!$listAdmin) {
                $userLocality = $this->userLocalityModel->getUserLocalityByUserId($this->userAuth->getUserId());
                $regionalId = $userLocality->getRegionalId();
                $filter['regional_not'] = $regionalId;
                $filter['filterByStateRegional'] = '1';
                $this->view->getAll = $this->regionalModel->getAllRegionalByOneRegionalServiceArea(
                null,$regionalId,$filter,$orderBy,$count, $page);
            }
        }
        
        
    }


    
    public function editAction()
    {
            $this->_helper->Regional->regionalModel = $this->regionalModel;
            $this->_helper->Regional->stateModel = $this->stateModel;
            $this->_helper->Regional->serviceAreaModel = $this->serviceAreaModel;
            $this->_helper->Regional->roleRow = $this->roleRow;
            $this->_helper->Regional->aclModel = $this->aclModel;
            $this->_helper->Regional->userAuth = $this->userAuth;
            $this->_helper->Regional->userLocalityModel = $this->userLocalityModel;

            $this->_helper->Regional->listarEditarRegional($this->_getAllParams(), $this->view);  

    }

    
    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        $this->view->allStateId = array(0 => array('StateId' => 0));
        $listAdmin = false;
        
        if ($this->roleRow->getIsSystemAdmin() == 1) {
            $this->view->getAllStates = $this->stateModel->getAll();
            $this->view->serviceArea = null;
            $listAdmin = true;
        }
        
        
        if ($this->aclModel->isAllowed($this->userAuth->getRole(), 'management:regional', 'list-address-by-regional')) {
            if (!$listAdmin) {
                $userLocality = $this->userLocalityModel->getUserLocalityByUserId($this->userAuth->getUserId());
                $regionalIdUserLogged = $userLocality->getRegionalId();

                $serviceAreaData = $this->serviceAreaModel->getAllServiceAreaByRegionalId($regionalIdUserLogged);
                $filter[$serviceAreaData['indice']] = $serviceAreaData['value'][$serviceAreaData['indice']];
                $this->view->serviceArea = $serviceAreaData;
                $this->view->getAllStates = $this->stateModel->getAll(null,null,null,null,$filter);
            }
        }
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $regionalTransactionData = $this->_getAllParams();
        $this->view->regionalRowData = $regionalTransactionData;
        $regionalIdUserLogged = (isset($regionalIdUserLogged))?$regionalIdUserLogged:null;
        $insert = $this->regionalModel->createRegionalTransaction($regionalTransactionData,$regionalIdUserLogged,$this->roleRow); 
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }

        $this->view->itemSuccess = true;
        $this->view->loadUrlRegional = $this->view
            ->baseUrl('/management/regional/success/itemInsertSuccess/true/description/'
                .urlencode($regionalTransactionData['regional']['description']));
    }    
    
    public function deleteAction()
    {
        $regionalId = $this->_getParam('id');
        $regionalRow = $this->regionalModel->getRegionalById($regionalId);
        if (!$regionalRow) {
            throw new Exception('Regional inválida');
        }

        $delete = $this->regionalModel->deleteRegional($regionalRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->itemDeleteSuccess = true;
        $this->_forward('index');
    }

    public function successAction()
    {
        $this->_helper->layout()->disableLayout();
        $params = $this->_getAllParams();
        
        if(isset($params['itemInsertSuccess']))
        {
            $this->view->itemInsertSuccess = $params['itemInsertSuccess'];
            $this->view->description = $params['description'];
        }
        
        if (isset($params['itemUpdateSuccess'])) 
        {
            $this->view->itemUpdateSuccess = $params['itemUpdateSuccess'];
            $this->view->description = $params['description'];
        }
        //$this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
    }

}