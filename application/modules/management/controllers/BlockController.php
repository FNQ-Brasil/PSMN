<?php
/**
 * Controller_Block
 * @uses  
 * @author mcianci
 *
 */
class Management_BlockController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('index', array('json'))
             ->addActionContext('insert', array('json'))
             ->addActionContext('edit', array('json'))
             ->addActionContext('move', array('json'))
             ->addActionContext('delete', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext(); 
        $this->Questionnaire = new Model_Questionnaire();
        $this->Block = new Model_Block();
        $this->DbTable_Block = new DbTable_Block();
    }

    public function indexAction()
    {
        $this->view->questionnaireId = $questionnaireId = null;
        if ($this->_getParam('questionnaire_id')) {
            $questionnaireId = $this->_getParam('questionnaire_id');
            $this->view->questionnaireId = $questionnaireId;
        }
        $this->view->getAllBlock = $this->Block->getAllByQuestionnaireId($questionnaireId)->toArray();
        $this->view->itemSuccess = true;
    }

    public function insertAction()
    {
        $this->view->getAllQuestionnaire = $this->Questionnaire->getAll();
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $blockRowData = $this->_getAllParams();
        $insert = $this->Block->createBlock($blockRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->lastInsertId = $insert['lastInsertId'];
        $this->view->lastDesignation = $insert['lastDesignation'];
        $this->view->itemSuccess = true;
    }

    public function editAction()
    {
        $this->view->getAllQuestionnaire = $this->Questionnaire->getAll();
        $blockId = $this->_getParam('id');
        $blockRow = $this->Block->getBlockById($blockId);
        if (!$blockRow) {
            throw new Exception('Bloco inválido, não encontrado.');
        }
        $this->view->blockRow = $blockRow;
        $this->view->blockRowData = array(
            'questionnaire_id'  => $blockRow->getQuestionnaireId(),
            'designation'       => $blockRow->getDesignation(),
            'value'             => $blockRow->getValue(),
            'long_description'  => $blockRow->getLongDescription(),
            'conclusion_text'   => $blockRow->getConclusionText(),
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $blockRowData = $this->_getAllParams();
        $update = $this->Block->updateBlock($blockRow, $blockRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
    }

    public function moveAction()
    {
        $blockId = $this->_getParam('id');
        $blockRow = $this->Block->getBlockById($blockId);
        if (!$blockRow) {
            throw new Exception('Bloco inválido, não encontrado.');
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $blockRowData = $this->_getAllParams();
        $fromBlockId = $blockRowData['id'];
        $toRowData = $this->Block->getBlockByPosition(
            $blockRowData['new_position_designation'], 
            $blockRowData['parent_id'] // parent_id => questionnaire_id
        );
        $toBlockId = $toRowData->getId();
        $update = $this->Block->moveBlock($fromBlockId, $toBlockId);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }

        $this->view->getIdDesignation =  $this->DbTable_Block
            ->getIdDesignation($blockRowData['parent_id'])
            ->toArray();
        $this->view->itemSuccess = true;
    }

    public function deleteAction()
    {
        $blockId = $this->_getParam('id');
        $blockRow = $this->Block->getBlockById($blockId);
        if (!$blockRow) {
            throw new Exception('Bloco inválido.');
        }
        $delete = $this->Block->deleteBlock($blockRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
    }
}
