<?php
/**
 * 
 * Controller_AnnualResultData
 * @uses  
 * @author mcianci
 *
 */
class Management_AnnualResultDataController extends Vtx_Action_Abstract
{

    public function init()
    {
        $this->AnnualResultData = new Model_AnnualResultData();
        $this->AnnualResult = new Model_AnnualResult();
        $this->view->getAllAnnualResult = $this->AnnualResult->getAll();
    }

    public function indexAction()
    {
        $this->view->getAllAnnualResultData = $this->AnnualResultData->getAll();
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->annualResultRowData = $annualResultRowData = $this->_getAllParams();
        $insert = $this->AnnualResultData->createAnnualResultData($annualResultRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->annualResultDataInsertSucess = true;
        $this->_forward('index');
    }

    public function answerAction()
    {
        $this->_helper->viewRenderer->setRender('answer');
        $annualResultDataId = $this->_getParam('id');
        $annualResultDataRow = $this->AnnualResultData->getAnnualResultDataById($annualResultDataId);
        if (!$annualResultDataRow) {
            throw new Exception('Dados do resultado anual inválido, não encontrado.');
        }
        $this->view->annualResultDataRow = $annualResultDataRow;
        $this->view->annualResultDataRowData = array(
            'annual_result_id'   => $annualResultDataRow->getAnnualResultId(),
            'value'         => $annualResultDataRow->getValue(),
            'year'          => $annualResultDataRow->getYear()
        );
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $this->view->annualResultDataRowData = $annualResultDataRowData = $this->_getAllParams();
        $annualResultDataRowData['annual_result_id'] = $annualResultDataRow->getAnnualResultId();
        $annualResultDataRowData['year'] = $annualResultDataRow->getYear();
        $update = $this->AnnualResultData->updateAnnualResultData($annualResultDataRow, $annualResultDataRowData);
        
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        
        $this->view->annualResultDataAnswerSucess = true;
        $this->_forward('index');
    }

    public function editAction()
    {
        $annualResultDataId = $this->_getParam('id');
        $annualResultDataRow = $this->AnnualResultData->getAnnualResultDataById($annualResultDataId);
        if (!$annualResultDataRow) {
            throw new Exception('Dados do resultado anual inválido, não encontrado.');
        }
        $this->view->annualResultDataRow = $annualResultDataRow;
        $this->view->annualResultDataRowData = array(
            'annual_result_id'   => $annualResultDataRow->getAnnualResultId(),
            'value'         => $annualResultDataRow->getValue(),
            'year'          => $annualResultDataRow->getYear()
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->annualResultDataRowData = $annualResultDataRowData = $this->_getAllParams();
        $update = $this->AnnualResultData->updateAnnualResultData($annualResultDataRow, $annualResultDataRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->annualResultDataUpdateSucess = true;
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $annualResultDataId = $this->_getParam('id');
        $annualResultDataRow = $this->AnnualResultData->getAnnualResultDataById($annualResultDataId);
        if (!$annualResultDataRow) {
            throw new Exception('Dado do resultado anual inválido.');
        }
        $delete = $this->AnnualResultData->deleteAnnualResultData($annualResultDataRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->annualResultDataDeleteSucess = true;
        $this->_forward('index');
    }

}