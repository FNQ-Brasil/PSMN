<?php
/**
 * 
 * Controller_Alternative
 * @uses  
 * @author mcianci
 *
 */
class Management_AlternativeController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('insert', array('json'))
             ->addActionContext('index', array('json'))
             ->addActionContext('edit', array('json'))
             ->addActionContext('delete', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        
        $this->Alternative = new Model_Alternative();
        $this->Question = new Model_Question();
        $this->QuestionTip = new Model_QuestionTip();
        /*$this->AnnualResult = new Model_AnnualResult();
        $this->AnnualResultData = new Model_AnnualResultData();*/
        $this->AlternativeType = new Model_AlternativeType();
    }

    public function indexAction()
    {
        $this->view->questionId = $questionId = null;
        
        if ($this->_getParam('question_id')) {
            $questionId = $this->_getParam('question_id');
            $this->view->questionId = $questionId;
        }
        
        $this->view->getAllQuestionTip = $this->QuestionTip->getAllByQuestionId($questionId)->toArray();
        $getAllAlternative = $this->Alternative->getAllByQuestionId($questionId, true);

        $this->view->getAllAlternative = $getAllAlternative;
        $this->view->itemSucess = true;
        
    }

    public function insertAction()
    {
        //$this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        //$this->view->alternativeRowData = $alternativeRowData = $this->_getAllParams();
        $alternativeRowData = $this->_getAllParams();
        
        $alternativeParentId = isset($alternativeRowData['parent_alternative_id'])?
            $alternativeRowData['parent_alternative_id'] : null;
        
        $insert = $this->Alternative->createAlternative($alternativeRowData,$alternativeParentId);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->itemSucess = true;
        //$this->_forward('index');
    }
    
    public function editAction()
    {
        $alternativeId = $this->_getParam('id');
        $alternativeRow = $this->Alternative->getAlternativeById($alternativeId);
        if (!$alternativeRow) {
            throw new Exception('Alternativa inválida, não encontrada.');
        }
        $this->view->alternativeRow = $alternativeRow;
        $this->view->alternativeRowData = array(
            'alternative_type_id'   => $alternativeRow->getAlternativeTypeId(), 
            'question_id'           => $alternativeRow->getQuestionId(),
            'parent_alternative_id' => $alternativeRow->getParentAlternativeId(),
            'designation'           => $alternativeRow->getDesignation(),
            'value'                 => $alternativeRow->getValue(),
            'version'               => $alternativeRow->getVersion(),
            'status'                => $alternativeRow->getStatus(),
            'score_level'           => $alternativeRow->getScoreLevel(),
            'feedback_default'      => $alternativeRow->getFeedbackDefault(),
            'dialogue_description'  => $alternativeRow->getDialogueDescription()
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->alternativeRowData = $alternativeRowData = $this->_getAllParams();
        $update = $this->Alternative->updateAlternative($alternativeRow, $alternativeRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->questionUpdateSucess = true;
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $alternativeId = $this->_getParam('id');
        $alternativeRow = $this->Alternative->getAlternativeById($alternativeId);
        if (!$alternativeRow) {
            throw new Exception('Alternativa inválida');
        }
        $delete = $this->Alternative->deleteAlternative($alternativeRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        $this->view->alternativeDeleteSucess = true;
        $this->_forward('index');
    }

}