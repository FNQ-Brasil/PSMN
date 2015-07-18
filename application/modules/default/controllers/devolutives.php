<?php
class DevolutivesController extends Vtx_Action_Abstract
{

    public function indexAction()
    {
        $this->_forward('index', 'site');
        return;
    }
}