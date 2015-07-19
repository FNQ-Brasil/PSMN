<?php
/**
 * Controller_Appraiser
 * @uses  
 *
 */
class Management_AppraiserController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('insert', array('json'))
             ->addActionContext('delete', array('json'))
             ->addActionContext('set-appraiser-to-enterprise', array('json'))
             ->addActionContext('set-checker-to-enterprise', array('json'))
             ->addActionContext('devolver-avaliador', array('json'))
            ->addActionContext('devolver-verificador', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        
        // Contextos dos actions
        $this->_helper->getHelper('ajaxContext')
            ->addActionContext('edit', array('json', 'html'))
            ->initContext();
        
        $this->programId = Zend_Registry::get('configDb')->competitionId;
        $this->Enterprise = new Model_Enterprise();
        $this->DbTable_Enterprise = new DbTable_Enterprise();
        $this->programId = Zend_Registry::get('configDb')->competitionId;
        $this->dbTable_User = new DbTable_User();
        $this->modelUser = new Model_User();
        $this->serviceAreaModel = new Model_ServiceArea();
        $this->aclModel = Zend_Registry::get('acl');
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->modelUserLocality = new Model_UserLocality();
        $this->modelRegional = new Model_Regional();
        $this->dbTable_Regional = new DbTable_Regional();
        $this->roleAppraiserId = Zend_Registry::get('config')->acl->roleAppraiserId;
        $this->autoavaliacaoId = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;
        $this->modelServiceArea = new Model_ServiceArea();
        $this->Appraiser = new Model_Appraiser(); 
    }

    public function indexAction()
    {
        $Acl = Zend_Registry::get('acl'); 
        $auth = Zend_Auth::getInstance();

        $userLoggedRow = $this->userAuth;
        $roleRow = $this->aclModel->getRoleById($userLoggedRow->getRoleId(), false);

        $this->view->page = $page = $this->_getParam('page', 1);
        $count = $this->_getParam('count', 10);
        $filter = $this->_getParam('filter', null);
        $this->view->filter = $filter;

        $format = $this->_getParam('format');
        $this->view->orderBy = $orderBy = $this->_getParam('orderBy', null);
        //$this->view->programaTipo = Zend_Registry::get('programaTipo');

        $filter['incluir_join_pontuacao'] = '1';
        $filter['appraiser_id'] = $auth->getIdentity()->getUserId();
        // List Coop by Avaliador
        $this->view->getAllEnterprise = $this->Enterprise->getAllByColAE(
            null, null, $this->autoavaliacaoId, $count, $page,
            $filter, $orderBy, null, 'all', 'lista-avaliador'
        );
        
                        
        $this->view->getAllEnterpriseNacional = $this->Enterprise->getAllByColAE(
            null, null, $this->autoavaliacaoId, $count, $page,
            $filter, $orderBy, null, 'all', 'lista-avaliador-nacional'
        );
        
        
        
    }
    
    /**
     * Listagem do verificadores logadops
     */
    public function checkerAction()
    {
        $auth = Zend_Auth::getInstance();

        $this->view->page = $page = $this->_getParam('page', 1);
        $count = $this->_getParam('count', 10);
        $filter = $this->_getParam('filter', null);
        $this->view->filter = $filter;

        $format = $this->_getParam('format');
        $this->view->orderBy = $orderBy = $this->_getParam('orderBy', null);
        //$this->view->programaTipo = Zend_Registry::get('programaTipo');

        $filter['incluir_join_pontuacao'] = '1';
        $filter['checker_id'] = $auth->getIdentity()->getUserId();
        // List Coop by Verificador
        $this->view->getAllEnterprise = $this->Enterprise->getAllByColAE(
            null, null, $this->autoavaliacaoId, $count, $page,
            $filter, $orderBy, null, 'all', 'checker-list'
        );  
        
     }

    public function editAction()
    {
        $Acl = Zend_Registry::get('acl'); 
        $auth = Zend_Auth::getInstance();
        
        $State = new Model_State();
        $City = new Model_City();
        $Neighborhood = new Model_Neighborhood();
        
        $Appraiser = new Model_Appraiser();
    	$appraiserId = $this->_getParam('id');
        $appraiserRow = $this->modelUser->getUserById($appraiserId);
        if (!$appraiserRow) {
            throw new Exception('invalid appraiser.');
        }
        $this->view->appraiserRow = $appraiserRow;
        
        $listAdmin = false;
        
        $userLoggedRow  = $this->userAuth;
        $userLocality   = $this->modelUserLocality->getUserLocalityByUserId($userLoggedRow->getUserId());
        
        $roleRow = $this->aclModel->getRoleById($userLoggedRow->getRoleId(), false);
        
        $page = $this->_getParam('page', 1);
        $count = $this->_getParam('count',10);
        
        $this->view->page = $page;
        $this->view->itensPage = $count;
        
        $filter = $this->_getParam('filter', null);
        $this->view->filter = $filter;
        $this->view->getAllStates = $State->getAll();
        if (isset($filter['state_id']) and !empty($filter['state_id'])) {
            $this->view->getAllCities = $City->getAllCityByStateId($filter['state_id']);
        }
        if (isset($filter['city_id']) and !empty($filter['city_id'])) {
            $this->view->getAllNeighborhoods = $Neighborhood->getAllNeighborhoodByCityId($filter['city_id']);
        }
        
        // Listagem dos Avaliadores pelo admin 
        if ($roleRow->getIsSystemAdmin() == 1) { // eh admin 
            // lista Empresas
            $getAllEnterprise = $this->Enterprise->getAllWithRa($this->autoavaliacaoId, $count, $page,$filter);
            $this->view->getAllEnterpriseByRegionalServiceArea = $getAllEnterprise;
            //--
            $getAllRegional = $this->modelRegional->getAll();
            $this->view->getAllRegional = $getAllRegional;
            $listAdmin = true;
        }
         
        // Listagem dos Avaliadores pelo gestor 
        if ($Acl->isAllowed($auth->getIdentity()->getRole(), 'management:appraiser', 'list-appraiser-by-regional-service-area')) {
            if (!$listAdmin) {
                $userLocality = $this->modelUserLocality->getUserLocalityByUserId($userLoggedRow->getUserId());
                
                //$regionalId = ($filter['regional_id'])?$filter['regional_id']:$userLocality->getRegionalId();
                $regionalIdUserLogged = $userLocality->getRegionalId();
                
                $this->view->getAllRegional = $this->dbTable_Regional->getAllRegionalByOneRegionalServiceArea($this->roleAppraiserId,$regionalIdUserLogged,'all',$filter);
                
                // Listar Empresas por RegionalSA
                $getAllEnterpriseByRegionalServiceArea = $this->Enterprise
                    ->getAllEnterpriseByRegionalServiceArea($regionalIdUserLogged, $count, $page, $filter);
                $this->view->getAllEnterpriseByRegionalServiceArea = $getAllEnterpriseByRegionalServiceArea;
                //--
            }
        }
        
        if ($filter) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }

    }

    public function setAppraiserToEnterpriseAction()
    {
        $data['enterprise_id']= $this->Enterprise->getEnterpriseByIdKey($this->_getParam('idKey'))->getId();
        $data['appraiser_id']= $this->_getParam('appraiserId');
        $data['tipo']= $this->_getParam('tipo');
        $data['programa_id'] = Zend_Registry::get('configDb')->competitionId;
        $data['etapa'] = $this->_getParam('etapa', 'estadual');

        if ($data['etapa'] == 'nacional') {
            /* @TODO só pode gestor nacional */
            unset($data['desclassificar']);
            unset($data['justificativa']);
            switch ($data['tipo']) {
                case 1:
                    $data['tipo'] = 4;
                    break;
                case 2:
                    $data['tipo'] = 5;
                    break;
                case 3:
                    $data['tipo'] = 6;
                    break;
            }
        } else {
            if (!in_array($data['tipo'], array(1, 2, 3))) {
                throw new Exception('erro');
            }
        }

        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        
        $AppraiserEnterprise = new Model_Appraiser();
        $objAppraiser = $AppraiserEnterprise->setAppraiserToEnterprise($data);
        
        $this->view->itemSuccess = true;
    }
    
    public function setCheckerToEnterpriseAction()
    {
        $data['enterprise_id']= $this->Enterprise->getEnterpriseByIdKey($this->_getParam('idKey'))->getId();
        $data['checker_id']= $this->_getParam('appraiserId');
        $data['tipo']= $this->_getParam('tipo');
        $data['programa_id']= Zend_Registry::get('configDb')->competitionId;
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        
        $AppraiserModel = new Model_Appraiser();
        $objAppraiser = $AppraiserModel->setCheckerToEnterprise($data);
        
         $this->view->itemSuccess = true;
         
    }
    
    public function deleteAction()
    {
        $this->_helper->viewRenderer->setRender('index');
        $userId = $this->_getParam('id');
        $userRow = $this->modelUserLocality->getUserLocalityByUserId($userId);
        if (!$userRow) {
            throw new Exception('Invalid User.');
        }
        $delete = $this->modelUserLocality->deleteEnterpriseByAppraiser($userRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
    }
 
    public function internalReportAction()
    {
        
        $enterpriseKey = $this->_getParam('enterprise-id-key');
        $competitionId = $this->_getParam('competition-id',null);
        $enterpriseRow = $this->Enterprise->getEnterpriseByIdKey($enterpriseKey);
        $commentQuestions = $this->Appraiser->getQuestions();
        $evaluationQuestions = DbTable_QuestionChecker::getInstance()->fetchAll('QuestionTypeId = 7', 'Designation');
        
        $this->evaluationRow = $this->Appraiser->getCheckerEvaluation(
            $enterpriseRow->getId(), (($competitionId)?$competitionId:$this->programId)
        );

        $modelReport = new Model_EnterpriseReport;
        $View = array(
            'report' => $modelReport->getEnterpriseReportByEnterpriseIdKey($enterpriseKey,$competitionId),
            'enterprise' => $enterpriseRow,
            'president' => $enterpriseRow->getPresidentRow(),
            'questoes' => $commentQuestions,
            'questionsAvaliacao' => $evaluationQuestions,
            'evaluationRow' => $this->evaluationRow,
            'respostas' => $this->evaluationRow? $this->evaluationRow->getAnswers() : null,
            'commentAnswers' => $this->evaluationRow? $this->evaluationRow->getCommentAnswers() : null,
            'scores' => $this->Appraiser->getEnterpriseScoreAppraisersData($enterpriseRow->getId(),$competitionId)
        );
        $this->view->assign($View);
    }
    
    public function devolverAvaliadorAction()
    {
        $enterpriseKey = $this->_getParam('key');
        $enterpriseRow = $this->Enterprise->getEnterpriseByIdKey($enterpriseKey);
        $userIdAppraiser = $this->_getParam('appraiser');
        $etapa = $this->_getParam('etapa', 'estadual');
        
        $evaluationRow = $this->Appraiser->isPermit(
            $enterpriseRow->getId(), $userIdAppraiser, $this->programId, $etapa
        );
        if (!$evaluationRow or $evaluationRow->getStatus() != 'C') {
            throw new Exception('Não autorizado');
        }
        $evaluationRow->setStatus('I')->setDevolutiva('Devolvida')
            ->setDevolutiva($this->_getParam('motivo', ''))->save();
        $this->view->itemSuccess = true;
    }
    
    public function devolverVerificadorAction()
    {
        $enterpriseKey = $this->_getParam('key');
        $enterpriseRow = $this->Enterprise->getEnterpriseByIdKey($enterpriseKey);
        $userIdChecker = $this->_getParam('checker');
        
        $evaluationRow = $this->Appraiser->isCheckerPermit(
             $enterpriseRow->getId(), $userIdChecker, $this->programId
        );
        if (!$evaluationRow or $evaluationRow->getStatus() != 'C') {
            throw new Exception('Não autorizado');
        }
        $evaluationRow->setStatus('I')->setDevolutiva('Devolvida')
            ->setDevolutiva($this->_getParam('motivo', ''))->save();
        $this->view->itemSuccess = true;
    }
}
