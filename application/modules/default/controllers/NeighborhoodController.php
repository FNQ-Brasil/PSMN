<?php

class NeighborhoodController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('index', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        $this->Neighborhood = new Model_Neighborhood();
        $this->DbTable_Neighborhood = new DbTable_Neighborhood();
    }

    public function indexAction() 
    {
        $this->view->cityId = $cityId = null;
        if ($this->_getParam('city_id')) {
            $cityId = $this->_getParam('city_id');
            $this->view->cityId = $cityId;
        }
        $serviceArea=null;
        if ($this->_getParam('serviceArea')) {
            $serviceArea = $this->_getParam('serviceArea');
            $this->view->serviceArea = $serviceArea;
        }
        $this->view->neighborhoods = $this->Neighborhood->getAllNeighborhoodByCityId($cityId,$serviceArea)->toArray();
        $this->view->itemSuccess = true;
    }
}