<?php
/**
 * 
 * Controller_Criterion
 * @uses  
 * @author mcianci
 *
 */
class Management_CriterionController extends Vtx_Action_Abstract
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
        $this->Block = new Model_Block();
        $this->Criterion = new Model_Criterion();
        $this->DbTable_Criterion = new DbTable_Criterion();
    }

    public function indexAction()
    {
        //$this->view->getAllBlock = $this->Block->getAll();
        
        $this->view->blockId = $blockId = null;
        if ($this->_getParam('block_id')) {
            $blockId = $this->_getParam('block_id');
            $this->view->blockId = $blockId;
        }
        $this->view->getAllCriterion = $this->Criterion->getAllByBlockId($blockId)->toArray();
    }

    public function insertAction()
    {
        //$this->view->getAllBlock = $this->Block->getAll();
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        //$this->view->criterionRowData = $criterionRowData = $this->_getAllParams();
        $criterionRowData = $this->_getAllParams();
        $insert = $this->Criterion->createCriterion($criterionRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        //$this->view->criterionInsertSucess = true;
        $this->view->lastInsertId = $insert['lastInsertId'];
        $this->view->lastDesignation = $insert['lastDesignation'];
        $this->view->itemSuccess = true;
        //$this->_forward('index');
    }
    
    public function editAction()
    {
        $this->view->getAllBlock = $this->Block->getAll();
        $criterionId = $this->_getParam('id');
        $criterionRow = $this->Criterion->getCriterionById($criterionId);
        if (!$criterionRow) {
            throw new Exception('Critério inválido, não encontrado.');
        }
        $this->view->criterionRow = $criterionRow;
        $this->view->criterionRowData = array(
            'block_id'          => $criterionRow->getBlockId(),
            'designation'       => $criterionRow->getDesignation(),
            'value'             => $criterionRow->getValue(),
            'long_description'  => $criterionRow->getLongDescription(),
            'conclusion_text'   => $criterionRow->getConclusionText(),
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        //$this->view->criterionRowData = $criterionRowData = $this->_getAllParams();
        $criterionRowData = $this->_getAllParams();
        $update = $this->Criterion->updateCriterion($criterionRow, $criterionRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
        //$this->view->criterionUpdateSucess = true;
        //$this->_forward('index');
    }

    public function moveAction()
    {
        $criterionId = $this->_getParam('id');
        $criterionRow = $this->Criterion->getCriterionById($criterionId);
        if (!$criterionRow) {
            throw new Exception('Critério inválido, não encontrado.');
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $criterionRowData = $this->_getAllParams();
        $fromCriterionId = $criterionRowData['id']; // ID da Origem
        $toRowData = $this->Criterion->getCriterionByPosition(
            $criterionRowData['new_position_designation'], 
            $criterionRowData['parent_id']
        );
        $toCriterionId = $toRowData->getId(); // ID do Destino
        $update = $this->Criterion->moveCriterion($fromCriterionId, $toCriterionId);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->getIdDesignation =  $this->DbTable_Criterion
            ->getIdDesignation($criterionRowData['parent_id'])
            ->toArray();
        $this->view->itemSuccess = true;
    }

    public function deleteAction()
    {
        $criterionId = $this->_getParam('id');
        $criterionRow = $this->Criterion->getCriterionById($criterionId);
        if (!$criterionRow) {
            throw new Exception('Critério inválido.');
        }
        $delete = $this->Criterion->deleteCriterion($criterionRow);
        
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
    }

}