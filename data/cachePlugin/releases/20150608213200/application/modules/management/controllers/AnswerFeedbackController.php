<?php
/**
 * 
 * Controller_AnswerFeedback
 * @uses  
 * @author mcianci
 *
 */
class Management_AnswerFeedbackController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->Answer = new Model_Answer();
        $this->AnswerFeedback = new Model_AnswerFeedback();
        $this->view->getAllAnswer = $this->Answer->getAll();
    }

    public function indexAction()
    {
        $this->view->getAllAnswerFeedback = $this->AnswerFeedback->getAll();
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->answerFeedbackRowData = $answerFeedbackRowData = $this->_getAllParams();
        $insert = $this->AnswerFeedback->createAnswerFeedback($answerFeedbackRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->answerFeedbackInsertSucess = true;
        $this->_forward('index');
    }
    
    public function editAction()
    {
        $answerFeedbackId = $this->_getParam('id');
        $answerFeedbackRow = $this->AnswerFeedback->getAnswerFeedbackById($answerFeedbackId);
        if (!$answerFeedbackRow) {
            throw new Exception('Resposta inválida, não encontrada.');
        }
        $this->view->answerFeedbackRow = $answerFeedbackRow;
        $this->view->answerFeedbackRowData = array(
            'user_id'       => $answerFeedbackRow->getUserId(),
            'answer_id'     => $answerFeedbackRow->getAnswerId(),
            'feedback'      => $answerFeedbackRow->getFeedback(),
            'feedback_date' => $answerFeedbackRow->getFeedbackDate()
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->answerFeedbackRowData = $answerFeedbackRowData = $this->_getAllParams();
        $update = $this->AnswerFeedback->updateAnswerFeedback($answerFeedbackRow, $answerFeedbackRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->answerFeedbackUpdateSucess = true;
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $answerFeedbackId = $this->_getParam('id');
        $answerFeedbackRow = $this->AnswerFeedback->getAnswerFeedbackById($answerFeedbackId);
        if (!$answerFeedbackRow) {
            throw new Exception('Feedback de Resposta inválida');
        }
        $delete = $this->AnswerFeedback->deleteAnswerFeedback($answerFeedbackRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->answerFeedbackDeleteSucess = true;
        $this->_forward('index');
    }

}