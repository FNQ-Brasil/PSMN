<?php
/**
 * 
 * Controller_QuestionTip
 * @uses  
 * @author mcianci
 *
 */
class Management_QuestionTipController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->Question = new Model_Question();
        $this->QuestionTip = new Model_QuestionTip();
        $this->QuestionTipType = new Model_QuestionTipType();
        $this->view->getAllQuestions        = $this->Question->getAll();
        $this->view->getAllQuestionTipTypes = $this->QuestionTipType->getAll();
    }
    
    public function indexAction()
    {
        $this->view->getAllQuestionTips     = $this->QuestionTip->getAll();
        //
        $this->view->questionId = $questionId = null;
        if ($this->_getParam('question_id')) {
            $questionId = $this->_getParam('question_id');
            $this->view->questionId = $questionId;
        }
        $this->view->getAllQuestionTip = $this->QuestionTip->getAllByQuestionId($questionId)->toArray();
    }
    
    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->questionTipRowData = $questionTipRowData = $this->_getAllParams();
        $insert = $this->QuestionTip->createQuestionTip($questionTipRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->questionTipInsertSucess = true;
        $this->_forward('index');
    }
    
    public function editAction()
    {
        $questionTipId = $this->_getParam('id');
        $questionTipRow = $this->QuestionTip->getQuestionTipById($questionTipId);
        if (!$questionTipRow) {
            throw new Exception('Ajuda de questão inválida, não encontrada.');
        }

        $this->view->questionTipRow = $questionTipRow;
        $this->view->questionTipRowData = array(
            'question_id'           => $questionTipRow->getQuestionId(),
            'question_tip_type_id'  => $questionTipRow->getQuestionTipTypeId(),
            'value'                 => $questionTipRow->getValue(),
        );

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $this->view->questionTipRowData = $questionTipRowData = $this->_getAllParams();

        $update = $this->QuestionTip->updateQuestionTip($questionTipRow, $questionTipRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->questionTipUpdateSucess = true;
        $this->_forward('index');
    }
    
    public function deleteAction()
    {
        $questionTipId = $this->_getParam('id');
        $questionTipRow = $this->QuestionTip->getQuestionTipById($questionTipId);
        if (!$questionTipRow) {
            throw new Exception('Invalid Question Tip');
        }

        $delete = $this->QuestionTip->deleteQuestionTip($questionTipRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->questionTipDeleteSucess = true;
        $this->_forward('index');
    }

}