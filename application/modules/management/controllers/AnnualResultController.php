<?php
/**
 * 
 * Controller_AnnualResult
 * @uses  
 * @author mcianci
 *
 */
class Management_AnnualResultController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->Questions = new Model_Question();
        $this->AnnualResult = new Model_AnnualResult();
        $this->AnnualResultData = new Model_AnnualResultData();
        $this->view->getAllQuestions = $this->Questions->getAll();
        $this->view->getAllMask = Model_AnnualResult::$MASK;
    }

    public function indexAction()
    {
        $this->view->getAllAnnualResult = $this->AnnualResult->getAll();
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->annualResultRowData = $annualResultRowData = $this->_getAllParams();
        $insert = $this->AnnualResult->createAnnualResult($annualResultRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->annualResultInsertSucess = true;
        $this->_forward('index');
    }
    
    public function editAction()
    {
        $annualResultId = $this->_getParam('id');
        $annualResultRow = $this->AnnualResult->getAnnualResultById($annualResultId);
        if (!$annualResultRow) {
            throw new Exception('Resultado anual inválido, não encontrado.');
        }
        $this->view->annualResultRow = $annualResultRow;
        $this->view->annualResultRowData = array(
            'question_id'   => $annualResultRow->getQuestionId(),
            'mask'          => $annualResultRow->getMask(),
            'value'         => $annualResultRow->getValue()
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->annualResultRowData = $annualResultRowData = $this->_getAllParams();
        $update = $this->AnnualResult->updateAnnualResult($annualResultRow, $annualResultRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->annualResultUpdateSucess = true;
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $annualResultId = $this->_getParam('id');
        $annualResultRow = $this->AnnualResult->getAnnualResultById($annualResultId);
        if (!$annualResultRow) {
            throw new Exception('Resultado anual inválido.');
        }
        $delete = $this->AnnualResult->deleteAnnualResult($annualResultRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->annualResultDeleteSucess = true;
        $this->_forward('index');
    }

}