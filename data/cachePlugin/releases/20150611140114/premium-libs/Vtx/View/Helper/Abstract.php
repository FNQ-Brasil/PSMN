<?php

abstract class Vtx_View_Helper_Abstract
{
	/**
	 * @var Zend_View_Interface
	 */
	protected $view;

	/**
	 * Sets the view field
	 * @param $view Zend_View_Interface
	 */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function scriptPath($script)
    {
        return $this->view->getScriptPath($script);
    }
    
    public function setModuleScriptPath($module = 'default')
    {
        $scriptPath = sprintf('%s/modules/%s/views/scripts', APPLICATION_PATH, $module);
        return $this->view->setScriptPath($scriptPath);
    }
}