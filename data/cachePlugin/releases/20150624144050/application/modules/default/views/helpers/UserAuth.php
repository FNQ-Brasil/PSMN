<?php

class View_Helper_UserAuth extends Vtx_View_Helper_Abstract
{
    public function userAuth()
    {
        $auth = Zend_Auth::getInstance();
        return $auth->hasIdentity()? $auth->getIdentity() : null;
    }
}