<?php
/**
 * 
 * Controller_Answer
 * @uses  
 * @author mcianci
 *
 */
class Management_AnswerController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->Answer = new Model_Answer();
        $this->Alternative = new Model_Alternative();
        $this->Execution = new Model_Execution();
        $this->view->getAllAlternative = $this->Alternative->getAll();
        $this->view->getAllExecution = $this->Execution->getAll();
    }

    public function indexAction()
    {
        $this->view->getAllAnswer = $this->Answer->getAll();
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->answerRowData = $answerRowData = $this->_getAllParams();
        $answerRowData['end_time'] = date('Y-m-d H:i:s');
        $insert = $this->Answer->createAnswer($answerRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->answerInsertSucess = true;
        $this->_forward('index');
    }
    
    public function editAction()
    {
        $answerId = $this->_getParam('id');
        $answerRow = $this->Answer->getAnswerById($answerId);
        if (!$answerRow) {
            throw new Exception('Resposta inválida, não encontrada.');
        }
        $this->view->answerRow = $answerRow;
        $this->view->answerRowData = array(
            'alternative_id'    => $answerRow->getAlternativeId(),
            'answer_value'      => $answerRow->getAnswerValue(),
            'start_time'        => $answerRow->getStartTime(),
            'end_time'          => $answerRow->getEndTime(),
            'answer_date'       => $answerRow->getAnswerDate(),
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->answerRowData = $answerRowData = $this->_getAllParams();
        $answerRowData['end_time'] = date('Y-m-d H:i:s');
        $update = $this->Answer->updateAnswer($answerRow, $answerRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->answerUpdateSucess = true;
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $answerId = $this->_getParam('id');
        $answerRow = $this->Answer->getAnswerById($answerId);
        if (!$answerRow) {
            throw new Exception('Resposta inválida');
        }
        $delete = $this->Answer->deleteAnswer($answerRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->answerDeleteSucess = true;
        $this->_forward('index');
    }

}