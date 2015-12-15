<?php

class Management_PositionsController extends Vtx_Action_Abstract
{
    public function indexAction()
    {
        $Positions = new Model_Position();
        $this->view->geral = $Positions->getPositions();
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $data = $this->getRequest()->getParams();
        $this->view->dataRow = $Positions->add($data);
        $this->view->geral = $Positions->getPositions();
    }
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if(!$id) {
            return;
        }
        $Position = new Model_Position();
        $this->view->dataRowDelete  = $Position->delete($id);
        $this->_forward('index');
    }
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        if(!$id) {
            return;
        }
        $Position = new Model_Position();
        $this->view->data = $Position->getPosition($id);        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $data                       = $this->getRequest()->getParams();
        $this->view->dataRowEdit    = $Position->edit($data);
        $this->view->data = $Position->getPosition($id);
    }
}