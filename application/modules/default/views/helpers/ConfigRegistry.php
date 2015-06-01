<?php

class View_Helper_ConfigRegistry extends Vtx_View_Helper_Abstract
{
    public function configRegistry()
    {
        return Zend_Registry::get('config');
    }
}