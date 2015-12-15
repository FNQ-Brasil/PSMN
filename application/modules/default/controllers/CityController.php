<?php

class CityController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('index', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        $this->City = new Model_City();
        $this->DbTable_City = new DbTable_City();
    }

    public function indexAction() 
    {
        $this->view->stateId = $stateId = null;
        if ($this->_getParam('state_id')) {
            $stateId = $this->_getParam('state_id');
            $this->view->stateId = $stateId;
        }
        $serviceArea=null;
        if ($this->_getParam('serviceArea')) {
            $serviceArea = $this->_getParam('serviceArea');
            $this->view->serviceArea = $serviceArea;
        }
        $this->view->cities = $this->City->getAllCityByStateId($stateId,$serviceArea)->toArray();
        $this->view->itemSuccess = true;
    }
}