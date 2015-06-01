<?php

class View_Helper_LoggedAllowed extends Vtx_View_Helper_Abstract
{
    public function loggedAllowed($privilege = null, $resource = null)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $auth = Zend_Auth::getInstance();
        
        if (!Zend_Registry::isRegistered('acl') or !$auth->hasIdentity()) {
            return null;
        }
 
        $acl = Zend_Registry::get('acl');
        
        if (!$privilege) {
            $privilege = $request->getActionName();
        }
        
        if (!$resource) {
            $resource = $request->getModuleName() . ':' . $request->getControllerName();
        }
        
        return $acl->isAllowed($auth->getIdentity()->getRole(), $resource, $privilege);
    }
}