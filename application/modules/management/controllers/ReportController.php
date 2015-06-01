<?php
/**
 * Controller_Report
 * @uses  
 *
 */
class Management_ReportController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('insert', array('json'))
            ->addActionContext('edit', array('json'))
            ->setAutoJsonSerialization(true)
            ->initContext();

        $this->Acl = Zend_Registry::get('acl');
        $this->aclModel = Zend_Registry::get('acl');
        
        $this->auth = Zend_Auth::getInstance();
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        
        $this->modelUserLocality = new Model_UserLocality();
        $this->modelEnterpriseReport = new Model_EnterpriseReport();
        $this->competitionId = Zend_Registry::get('config')->util->competitionId;
    }

    public function indexAction()
    {
        $this->view->enterpriseIdKey = $enterpriseIdKey = $this->_getParam('enterprise-id-key');
        $programaId = $this->_getParam('programa_id', null);
        $cp = $this->modelEnterpriseReport->getCurrentEnterpriseReportByEnterpriseIdKey($enterpriseIdKey,$programaId);
        $this->_helper->_layout->setLayout('new-qstn');
         $this->view->isViewAdmin = true;
         
        if ($cp) {

            $reportId = $this->modelEnterpriseReport
                ->getEnterpriseReportByEnterpriseIdKey($enterpriseIdKey,$programaId)->getId();
            $paramsArray = array(
                'enterprise_id_key' => $enterpriseIdKey,
                'report_id'=> $reportId
            );
            $this->_forward('edit', 'report', 'questionnaire',$paramsArray);
            return;
        }

        $paramsArray = array(
            'enterprise_id_key' => $enterpriseIdKey,
            'competition_id'=> $programaId // $this->competitionId
        );
        $this->_forward('insert', 'report', 'questionnaire', $paramsArray);
        
    }

    public function insertAction()
    {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $params = $this->_getAllParams();

        $paramsArray = array(
            'enterprise_id_key' => $params['enterprise_id_key'],
            'competition_id'=> $params['competition_id']
        );
        
        $this->_forward('insert', 'report', 'questionnaire', $paramsArray);
    }

    public function editAction()
    {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $paramsArray = array();
        $this->_forward('edit', 'report', 'questionnaire', $paramsArray);
    }
    
    public function acompanhareportAction()
    {
        $this->view->enterpriseIdKey = $enterpriseIdKey = $this->_getParam('enterprise-id-key', $this->_getParam('enterprise_id_key'));
        $programaId = $this->_getParam('programa_id', null);
        $cp = $this->modelEnterpriseReport->getCurrentEnterpriseReportByEnterpriseIdKey($enterpriseIdKey,$programaId);
        $this->_helper->_layout->setLayout('new-qstn');
        $this->view->isViewAdmin = true;
         
        if (!$cp) {
            throw new Exception('Dados invalidos');
        }
        $reportId = $this->modelEnterpriseReport
            ->getEnterpriseReportByEnterpriseIdKey($enterpriseIdKey,$programaId)->getId();
        $paramsArray = array(
            'enterprise_id_key' => $enterpriseIdKey,
            'report_id'=> $reportId
        );
        $this->_forward('acompanhareport', 'report', 'questionnaire', $paramsArray);
    }

    public function viewAction()
    {
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
        $this->view->enterpriseIdKey = isset($params['enterpriseIdKey'])?
            $params['enterpriseIdKey'] : (isset($params['enterprise_id_key'])?
            $params['enterprise_id_key'] : '');

        if (!$this->getRequest()->isPost()) {
            return;
        }
    }


    

    public function deleteAction()
    {
        $this->_helper->viewRenderer->setRender('index');
        $reportId = $this->_getParam('id');
        $reportRow = $this->Report->getReportById($reportId);
        if (!$reportRow) {
            throw new Exception('invalid report.');
        }
        $delete = $this->Report->deleteReport($reportRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
    }
}
