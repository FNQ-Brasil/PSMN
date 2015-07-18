<?php
/**
 * 
 * Controller_Report
 * @uses  
 * @author mcianci
 *
 */
class Questionnaire_ReportController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('insert', array('json'))
            ->addActionContext('edit', array('json'))
            ->setAutoJsonSerialization(true)
            ->initContext();
        
        // Contextos dos actions
        /*
        $this->_helper->getHelper('ajaxContext')
            ->addActionContext('edit', array('json', 'html'))
            ->initContext();
        */
        
        $this->Acl = Zend_Registry::get('acl');
        $this->aclModel = Zend_Registry::get('acl');
        
        $this->auth = Zend_Auth::getInstance();
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->userLogged = Zend_Auth::getInstance()->getIdentity();
        
        $this->modelUserLocality = new Model_UserLocality();
        $this->modelEnterprise = new Model_Enterprise();
        $this->modelEnterpriseReport = new Model_EnterpriseReport();
        $this->modelAddressEnterprise = new Model_AddressEnterprise();
        $this->modelQuestionnaire = new Model_Questionnaire();
        $this->competitionId = Zend_Registry::get('configDb')->competitionId;
        $this->modelECAC = new Model_EnterpriseCategoryAwardCompetition();
        //$this->competitionId = Zend_Registry::get('config')->util->competitionId;
    }

    public function indexAction()
    {
        $enterpriseId = $this->modelUserLocality
            ->getUserLocalityByUserId($this->userAuth->getUserId())->getEnterpriseId();
        
        $where = array('EnterpriseId = ?' => $enterpriseId, 'CompetitionId = ?' => $this->competitionId);
        $hasReport = $this->modelEnterpriseReport->getEnterpriseReport($where);
        $enterpriseIdkey = $this->modelEnterprise->getEnterpriseById($enterpriseId)->getIdKey();

        
        if ( !$hasReport) {
            $this->_forward('insert', 'report', 'questionnaire', $params = array());
            return;
        }
        
        $this->_forward('edit', 'report', 'questionnaire', $params = array(
                'report_id' => $hasReport->getId(),
                'enterprise_id_key' => $enterpriseIdkey
                )
        );
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        $reportRowData = $this->_getAllParams();
        $enterpriseIdKey = $this->_getParam('enterprise_id_key',null);
        if ( $enterpriseIdKey and  $this->Acl->isAllowed(
                $this->userAuth->getRole(), 'management:report', 'index')
            ) {
            $this->view->enterpriseIdKey = $enterpriseIdKey;
            $enterpriseId = $this->modelEnterprise->getEnterpriseByIdKey($enterpriseIdKey)->getId();
            $this->view->reportRowData = $reportRowData;
        } else {
            $userId = $this->userAuth->getUserId();
            $userLocalityRow = $this->modelUserLocality->getUserLocalityByUserId($userId);
            $enterpriseId = $userLocalityRow->getEnterpriseId();
            $this->view->competitionId = $this->competitionId;
            $this->view->reportRowData = array('competition_id' => $this->competitionId);
        }

        $competitionId = isset($reportRowData['competition_id']) ? $reportRowData['competition_id']: $this->competitionId;
        $hasECAC = $this->modelECAC->hasECAC($enterpriseId,$competitionId);
        if (!$hasECAC) {
            throw new Exception('access denied');
            return;
        }

        if (isset($this->view->isViewAdmin) and $this->view->isViewAdmin) {
            $this->view->enterpriseRow = $this->modelEnterprise->getEnterpriseById($enterpriseId);
        }
        
        $this->view->addressEnterpriseRow = $this->modelAddressEnterprise->getAddressEnterpriseByEnterpriseId($enterpriseId);
        $this->view->subscriptionPeriodIsClosed = !$this->subscriptionPeriodIsOpen();
        
        if (!$this->getRequest()->isPost() or $this->view->subscriptionPeriodIsClosed) {
            return;
        }
        //die;

        $this->view->enterpriseRow = null;

        unset($this->view->competitionId);
        unset($this->view->reportRowData);

        $create = $this->modelEnterpriseReport->createReport($reportRowData,$enterpriseId);
        if (!$create['status']) {
            $this->view->itemSuccess = $create['status'];
            $this->view->messageError = $create['messageError'];
            return;
        }
        
        $this->view->itemSuccess = true;
        
         if ($this->Acl->isAllowed($this->auth->getIdentity()->getRole(), 'management:report', 'index')) {
            $this->view->loadUrlReport = $this->view
                ->baseUrl('/management/report/success/itemInsertSuccess/true/enterpriseIdKey/'.$enterpriseIdKey);
            return;
        }

        if ($this->Acl->isAllowed($this->auth->getIdentity()->getRole(), 'questionnaire:report', 'index')) {
            $this->view->loadUrlReport = $this->view->baseUrl('/questionnaire/report/success/itemInsertSuccess/true');
            return;
        }
        
    }

    public function editAction()
    {
        $UserLocality = new Model_UserLocality();
        
        $reportId = $this->_getParam('report_id');
        $enterpriseIdKey = $this->_getParam('enterprise_id_key',null);
        $reportRow = $this->modelEnterpriseReport->getEnterpriseReportById($reportId);
        $enterpriseRow = $this->modelEnterprise->getEnterpriseByIdKey($enterpriseIdKey);
        if (!$reportRow or ($reportRow->findParentEnterprise()->getIdKey() != $enterpriseRow->getIdKey())) {
            throw new Exception('Invalid Report');
            return;
        }

        if (  $this->_getParam('report_id') and  $this->Acl->isAllowed(
            $this->userLogged->getRole(), 'questionnaire:register', 'publisher')
        ) {
            $enterpriseId = $reportRow->getEnterpriseId();
        } else {
            $enterpriseId = $UserLocality->getUserLocalityByUserId($this->userLogged->getUserId())->getEnterpriseId();
        }

        $this->view->reportId = $reportId;
        $this->view->enterpriseIdKey = $enterpriseIdKey;
        $this->view->reportRow = $reportRow;
        $this->view->addressEnterpriseRow = $this->modelAddressEnterprise->getAddressEnterpriseByEnterpriseId($enterpriseId);
        if (isset($this->view->isViewAdmin) and $this->view->isViewAdmin) {
            $this->view->enterpriseRow = $reportRow->findParentEnterprise();
        }
        
        $this->view->reportRowData = array(
            'competition_id'    => $reportRow->getCompetitionId(),
            'report'            => $reportRow->getReport(),
            'title'             => $reportRow->getTitle(),
        );

        $this->view->subscriptionPeriodIsClosed = !$this->subscriptionPeriodIsOpen();

        if (!$this->getRequest()->isPost() or $this->view->subscriptionPeriodIsClosed) {
            return;
        }

        if(!$this->validateReportCompetitionEdition($reportRow))return;

        //  die;
        unset($this->view->reportId);
        unset($this->view->reportRow);
        unset($this->view->reportRowData);

        $reportRowData = $this->_getAllParams();
        
        $hasECAC = $this->modelECAC->hasECAC($enterpriseId,$reportRowData['competition_id']);
        if (!$hasECAC) {
            throw new Exception('access denied');
            return;
        }
        
        $update = $this->modelEnterpriseReport->updateReport($reportRow,$reportRowData,$enterpriseId);
        if (!$update['status']) {
            $this->view->itemSuccess = $update['status'];
            $this->view->messageError = $update['messageError'];
            return;
        }
        
        $this->view->itemSuccess = true;
        
        if ($this->Acl->isAllowed($this->auth->getIdentity()->getRole(), 'management:report', 'index')) {
            $this->view->loadUrlReport = $this->view
                ->baseUrl('/management/report/success/itemEditSuccess/true/enterpriseIdKey/'.$enterpriseIdKey);
            return;
        }
        
        /*
        if ($this->Acl->isAllowed($this->auth->getIdentity()->getRole(), 'questionnaire:register', 'publisher')) {
            $this->view->loadUrlReport = $this->view
                ->baseUrl('/management/enterprise/success/itemEditSuccess/true/social_name/'
                    .urlencode($ficha['enterprise']['social_name']));
            return;
        }
        */
        
        if ($this->Acl->isAllowed($this->auth->getIdentity()->getRole(), 'questionnaire:report', 'index')) {
            $this->view->loadUrlReport = $this->view->baseUrl('/questionnaire/report/success/itemEditSuccess/true');
            return;
        }
    }
    
    public function acompanhareportAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        $reportId = $this->_getParam('report_id');
        $enterpriseIdKey = $this->_getParam('enterprise_id_key',null);
        $reportRow = $this->modelEnterpriseReport->getEnterpriseReportById($reportId);
        $enterpriseRow = $this->modelEnterprise->getEnterpriseByIdKey($enterpriseIdKey);
        if (!$reportRow or ($reportRow->findParentEnterprise()->getIdKey() != $enterpriseRow->getIdKey())) {
            throw new Exception('Invalid Report');
            return;
        }

        $this->view->reportId = $reportId;
        $this->view->enterpriseIdKey = $enterpriseIdKey;
        $this->view->reportRow = $reportRow;
        $this->view->enterpriseRow = $reportRow->findParentEnterprise();

        $this->view->reportRowData = array(
            'competition_id'    => $reportRow->getCompetitionId(),
            'report'            => $reportRow->getReport(),
            'title'             => $reportRow->getTitle(),
        );
    }
     
    public function successAction() // success
    {
        $this->_helper->layout()->disableLayout();
        $params = $this->_getAllParams();
        
        if(isset($params['itemInsertSuccess']))
        {
            $this->view->itemInsertSuccess = $params['itemInsertSuccess'];
        }
        
        if (isset($params['itemEditSuccess'])) 
        {
            $this->view->itemEditSuccess = $params['itemEditSuccess'];
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
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

    private function validateReportCompetitionEdition($report){
        if($report->getCompetitionId() != date('Y')){
            $this->view->itemSuccess = FALSE;
            $this->view->messageError = 'Não é permitida a edição de relatos de edições anteriores';

            return FALSE;
        }
        return TRUE;
    }

    public function subscriptionPeriodIsOpen(){
        $isOpen = true;

        if(!$this->modelQuestionnaire->subscriptionPeriodIsOpenFor(null, $this->userLogged)){
            $this->view->itemSuccess = false;
            $this->view->messageError = 'Não foi possível enviar o relato: as inscrições foram encerradas.';
            $isOpen = false;
        }

        return $isOpen;
    }
}