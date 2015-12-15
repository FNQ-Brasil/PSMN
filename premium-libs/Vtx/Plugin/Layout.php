<?php

/**
 * 
 * Vtx_Plugin_Layout Plugin Controller Plugin
 * @uses Zend_Layout_Controller_Plugin_Layout
 * @author tsouza
 *
 */
class Vtx_Plugin_Layout extends Zend_Layout_Controller_Plugin_Layout
{
    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     */   
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $moduleName = $request->getModuleName();
        
        if ($moduleName == 'default') {
            return;
        }

        $this->getLayout()
            ->setLayout($moduleName);
    }
}