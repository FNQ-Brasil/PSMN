<?php

class View_Helper_Menu extends Vtx_View_Helper_Abstract
{
    public function menu()
    {
        $auth = Zend_Auth::getInstance();
        $request = Zend_Controller_Front::getInstance()->getRequest();       
        //$menu = $request->getParam('menu', '');

        $this->view->assign(array(
            'controller' => $request->getControllerName(),
            'action' => $request->getActionName(), 'request' => $request
        ));

        /*if ($auth->hasIdentity()) {
            $this->view->identify = $auth->getIdentity();
            return $this->view->render('menu/logged.phtml');
        }*/
        return $this->view->render('includes/menu/notlogged.phtml');
    }
}