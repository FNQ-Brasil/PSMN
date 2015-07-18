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
        $from = $data['contact']['email'];
        $subject = 'Mulher de Negócios 2014 :: Contado enviado pelo site';
        $message = nl2br($data['contact']['message']);
        
        //if (Vtx_Util_Mail::send($to,$from,$subject,$message)) {
        //    $this->view->itemSendSuccess = true;
        //}
        
        //entra fila para disparo de email
        $eQueue = new Model_EmailQueue();
        $res = $eQueue->setEmailQueue($to, $from, $subject, $message);        
        
        $this->view->itemSendSuccess = $res['status']; //boolean
        
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