<?php

class View_Helper_BuscaPadrao extends Vtx_View_Helper_Abstract
{
    public function buscaPadrao($filter = array())
    {
        $City = new Model_City();
        $Neighborhood = new Model_Neighborhood();

        $request = $this->view->request();
        $this->view->controller = $request->getControllerName();
        $this->view->action = $request->getActionName();
        $this->view->filter = $filter;
        
        if (isset($filter['state_id']) and !empty($filter['state_id'])) {
            $this->view->getAllCities = $City->getAllCityByStateId($filter['state_id']);
        }
        if (isset($filter['city_id']) and !empty($filter['city_id'])) {
            $this->view->getAllNeighborhoods = $Neighborhood->getAllNeighborhoodByCityId($filter['city_id']);
        }
        
        return $this->setModuleScriptPath('default')
            ->render('busca-padrao/form.phtml');
    }
}