<?php
/**
 * 
 * Controller_Glossary
 * @uses  
 * @author gersonlv
 *
 */
class Management_GlossaryController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->Glossary = new Model_Glossary();
    }
    
    public function indexAction()
    {
        //$this->view->getAllGlossaries = $this->Glossary
        //    ->getAll(null, null, 5, $this->_getParam('page'));

        $page = $this->_getParam('page');
        $count = $this->_getParam('count', 10);
        $orderBy = $this->view->orderBy = $this->_getParam('orderBy');
        $filter = $this->view->filter = $this->_getParam('filter');
        $this->view->getAllGlossaries = $this->Glossary->getAll(
            null, $orderBy, $count, $page, $filter
        );
        
        $this->view->glossaryId = $glossaryId = null;
        if ($this->_getParam('glossary_id')) {
            $glossaryId = $this->_getParam('glossary_id');
            $this->view->glossaryId = $glossaryId;
        }
    }
    
    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->glossaryRowData = $glossaryRowData = $this->_getAllParams();
        
        if (trim($glossaryRowData['term']) == "" || trim($glossaryRowData['description']) == "") {
            $this->view->messageError = "Preencha os campos adequadamente..";
            return;
        }
        
        $insert = $this->Glossary->add($glossaryRowData);
        if (!$insert) {
            $this->view->messageError = "Erro na inclusão do Termo.";
            return;
        }
        $this->view->glossaryInsertSucess = true;
        $this->_forward('index');
    }
    
    public function editAction()
    {
        $glossaryId = $this->_getParam('id');
        $glossaryRow = $this->Glossary->getGlossaryRowById($glossaryId);
        if (!$glossaryId) {
            $this->view->messageError = "Não foi possível a localização do Termo.";
            return;
        }

        $this->view->glossaryId = $glossaryId;
        $this->view->glossaryRow = $glossaryRow;
        $this->view->glossaryRowData = array(
            'glossary_id'   => $glossaryId,
            'term'          => $glossaryRow->getTerm(),
            'description'   => $glossaryRow->getDescription(),
        );

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $this->view->glossaryRowData = $glossaryRowData = $this->_getAllParams();

        if (trim($glossaryRowData['term']) == "" || trim($glossaryRowData['description']) == "") {
            $this->view->messageError = "Preencha os campos adequadamente..";
            return;
        }
        
        $update = $this->Glossary->edit($glossaryRowData);
        //if (!$update) {
        //    $this->view->messageError = "Erro na alteração do registro.";
        //    return;
        //}
        
        $this->view->glossaryUpdateSucess = true;
        $this->_forward('index');
    }
    
    public function deleteAction()
    {
        $GlossaryId = $this->_getParam('id');
        $delete = $this->Glossary->delete($GlossaryId);
        if (!$delete) {
            $this->view->messageError = "Erro ao excluir registro.";
        }
        $this->view->questionTipDeleteSucess = true;
        $this->view->glossaryDeleteSucess = true;
        $this->_forward('index');
    }

}