<?php

class View_Helper_Acl extends Vtx_View_Helper_Abstract
{
    public function acl()
    {
        return Zend_Registry::isRegistered('acl')? Zend_Registry::get('acl') : null;
    }
}