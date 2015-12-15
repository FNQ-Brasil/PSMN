<?php

class StateController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('index', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        $this->modelState = new Model_State();
        $this->DbTable_State = new DbTable_State();
    }

    public function indexAction() 
    {
        $this->view->states = $this->modelState->getAll()->toArray();
        $this->view->itemSuccess = true;
    }
}