<?php

class Management_IndexController extends Vtx_Action_Abstract
{
    public function indexAction()
    {
        $this->_redirect('management/questionnaire');
    }

}