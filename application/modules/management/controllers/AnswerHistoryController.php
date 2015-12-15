<?php
/**
 * 
 * Controller_AnswerHistory
 * @uses  
 * @author mcianci
 *
 */
class Management_AnswerHistoryController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->Answer = new Model_Answer();
        $this->AnswerHistory = new Model_AnswerHistory();
    }

    public function indexAction()
    {
        $this->view->getAllAnswerHistory = $this->AnswerHistory->getAll();
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        $answerId = $this->_getParam('answer_id');
        $answerRow = $this->Answer->getAnswerById($answerId);
        if (!$answerRow) {
            throw new Exception('Resposta inválida, não encontrada.');
        }
        $this->view->answerRow = $answerRow;
        $this->view->answerHistoryRowData = array(
            'user_id'           => array(''),
            'answer_id'         => $answerRow->getId(),
            'alternative_id'    => $answerRow->getAlternativeId(),
            'answer_value'      => $answerRow->getAnswerValue(),
            'start_time'        => $answerRow->getStartTime(),
            'end_time'          => $answerRow->getEndTime(),
            'log_date'          => $answerRow->getAnswerDate(),
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->answerHistoryRowData = $answerHistoryRowData = $this->_getAllParams();
        $this->view->answerHistoryRowData = array(
            'user_id'           => $answerHistoryRowData['user_id'],
            'answer_id'         => $answerRow->getId(),
            'alternative_id'    => $answerRow->getAlternativeId(),
            'answer_value'      => $answerRow->getAnswerValue(),
            'start_time'        => $answerRow->getStartTime(),
            'end_time'          => $answerRow->getEndTime(),
            'log_date'          => $answerRow->getAnswerDate(),
        );
        $insert = $this->AnswerHistory->createAnswerHistory($answerHistoryRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->answerHistoryInsertSucess = true;
        $this->_forward('index');
    }
    
    public function deleteAction()
    {
        $answerHistoryId = $this->_getParam('id');
        $answerHistoryRow = $this->AnswerHistory->getAnswerHistoryById($answerHistoryId);
        if (!$answerHistoryRow) {
            throw new Exception('Histórico de Resposta inválida');
        }
        $delete = $this->AnswerHistory->deleteAnswerHistory($answerHistoryRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->answerHistoryDeleteSucess = true;
        $this->_forward('index');
    }

}