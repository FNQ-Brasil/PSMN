<?php

class View_Helper_Request extends Vtx_View_Helper_Abstract
{
    public function request()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }
}