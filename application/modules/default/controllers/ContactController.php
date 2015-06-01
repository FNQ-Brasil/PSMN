<?php

class ContactController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('ajaxContext')
            ->addActionContext('index', array('json'))
            ->initContext();
    }

    
    public function indexAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $data = $this->getRequest()->getParams();

        $to = 'projeto.sescoop.fnq@vorttex.com.br';
        $subject = 'Mulher de Negócios 2014 :: Contado enviado pelo site';
        $message = nl2br($data['contact']['message']);

        Model_EmailMessage::createQuicklyWithRecipients('contact_notification', $subject, $message, array($to));
        
        $this->view->itemSendSuccess = true;
    }
    
    public function perfAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $Model = new Model_City;
        $cities = $Model->getAll();
        foreach ($cities as $key => $city) {
            Zend_Debug::dump($city->getName());
            
            //$city->findParentState()->getName()
        }

    }
}