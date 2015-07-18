<?php
class Management_ContactTypeController extends Vtx_Action_Abstract
{
    public function indexAction()
    {
        $ContactTypes = new Model_ContactType();
        $this->view->geral = $ContactTypes->getContactTypes();
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $data                   = $this->getRequest()->getParams();
        $this->view->dataRow    = $ContactTypes->add($data);
        $this->view->geral      = $ContactTypes->getContactTypes();  // pegar todos
    }
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        if(!$id) {
            return;
        }
        $ContactType = new Model_ContactType();
        $this->view->data = $ContactType->getContactType($id);        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $data                       = $this->getRequest()->getParams();
        $this->view->dataRowEdit    = $ContactType->edit($data);
        $this->view->data = $ContactType->getContactType($id);
    }
    
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if(!$id) {
            return;
        }
        $ContactType                = new Model_ContactType();
        $this->view->dataRowDelete  = $ContactType->delete($id);
        $this->_forward('index'); // obs, isso não faz com que ele identifique o post na action index.
    }
}