<?php

class AddressController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('index', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        $this->modelAddress = new Model_Address();
    }

    public function indexAction() 
    {
        if ($this->_getParam('cep')) {
            $filter['cep'] = $this->_getParam('cep');
        }
        $address = $this->modelAddress->getAddressByFilter($filter);
        if (!$address) {
            $this->view->itemSuccess = false;
            return;
        }
        $this->view->address = $address->toArray();
        $this->view->itemSuccess = true;
    }
}