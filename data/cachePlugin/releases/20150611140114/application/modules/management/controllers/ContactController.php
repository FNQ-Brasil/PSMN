<?php

class Management_ContactsController extends Vtx_Action_Abstract
{
    public function indexAction()
    {
        $Contacts = new Model_Contacts();
        
        $this->view->geral = $Contacts->getContacts();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $data = $this->getRequest()->getParams();
        $this->view->dataRow = $Contacts->add($data);
        $this->view->geral = $Contacts->getContacts();
    }
}