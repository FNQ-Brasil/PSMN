<?php
/**
 * 
 * Controller_Question
 * @uses  
 * @author mcianci
 *
 */
class Management_QuestionController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('insert', array('json'))
             ->addActionContext('index', array('json'))
             ->addActionContext('edit', array('json'))
             ->addActionContext('move', array('json'))
             ->addActionContext('delete', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        
        $this->Question         = new Model_Question();
        $this->Criterion        = new Model_Criterion();
        $this->Alternative      = new Model_Alternative();
        $this->QuestionTip      = new Model_QuestionTip();
        $this->QuestionType     = new Model_QuestionType();
        $this->DbTable_Question = new DbTable_Question();
    }
    
    public function indexAction()
    {
        $this->view->criterionId = $criterionId = null;
        if ($this->_getParam('criterion_id')) {
            $criterionId = $this->_getParam('criterion_id');
            $this->view->criterionId = $criterionId;
        }
        $this->view->getAllQuestion = $this->Question->getAllByCriterionId($criterionId)->toArray();
    }

    public function insertAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $questionData = $this->_getAllParams();
        $insertTransaction = $this->Question->createQuestionTransaction($questionData);
        if (!$insertTransaction['status']) {
            $this->view->messageError = $insertTransaction['messageError'];
            return;
        }
        $this->view->lastInsertId = $insertTransaction['lastInsertId'];
        $this->view->lastDesignation = $insertTransaction['lastDesignation'];
        $this->view->itemSuccess = true;
    }

    public function editAction()
    {
        $questionId = $this->_getParam('id');
        $questionRow = $this->Question->getQuestionById($questionId);
        if (!$questionRow) {
            throw new Exception('Questão inválida, não encontrada.');
        }
        $this->view->questionRow = $questionRow;
        $this->view->questionRowData = array(
            'question_type_id'     => $questionRow->getQuestionTypeId(),
            'parent_id'            => $questionRow->getCriterionId(),
            'parent_question_id'   => $questionRow->getParentQuestionId(),
            'designation'          => $questionRow->getDesignation(),
            'value'                => $questionRow->getValue(),
            'supporting_text'      => $questionRow->getSupportingText(),
            'version'              => $questionRow->getVersion(),
            'status'               => $questionRow->getStatus()
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $questionRowData = $this->_getAllParams();
        // start transaction externo
        $editTransaction = $this->Question->updateQuestionTransaction($questionRow, $questionRowData);
        if (!$editTransaction['status']) {
            $this->view->messageError = $editTransaction['messageError'];
            return;
        }
        // end transaction externo
        //$this->view->annual = $editTransaction['annual'];
        $this->view->itemSuccess = true;
    }

    public function moveAction()
    {
        $questionId = $this->_getParam('id');
        $questionRow = $this->Question->getQuestionById($questionId);
        if (!$questionRow) {
            throw new Exception('Questão inválida, não encontrado.');
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $questionRowData = $this->_getAllParams();
        $fromId = $questionRowData['id'];
        $designation = $questionRowData['new_position_designation'];
        $criterionId = $questionRowData['parent_id'];
        $toRowData = $this->Question->getQuestionByPosition($designation,$criterionId);
        $toId = $toRowData->getId();
        $update = $this->Question->moveQuestion($fromId, $toId);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->getIdDesignation =  $this->DbTable_Question
            ->getIdDesignation($questionRowData['parent_id'])
            ->toArray();
        $this->view->itemSuccess = true;
    }

    public function deleteAction()
    {
        $questionId = $this->_getParam('id');
        $questionRow = $this->Question->getQuestionById($questionId);
        if (!$questionRow) {
            throw new Exception('Invalid Question');
        }
        $delete = $this->Question->deleteQuestion($questionRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
    }
}