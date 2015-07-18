<?php
/**
* action helper para regional
*/
class Action_Helper_Regional extends Zend_Controller_Action_Helper_Abstract
{

    public $regionalModel;
    public $stateModel;
    public $serviceAreaModel;
    public $roleRow;
    public $aclModel;
    public $userAuth;
    public $userLocalityModel;
    public $view;
    
    /**
     * action helper para Listar e Edit Regional
     */
    public function listarEditarRegional($getParams, $objView)
    {
    	$this->view = $objView;
        
        $regionalId = $getParams['id'];
          
        $regionalRow = $this->regionalModel->getRegionalById($regionalId);
        
        //var_dump($regionalRow);
        
        $listAdmin = false;
        if (!$regionalRow) {
            throw new Exception('Regional inválida.');
        }
        $this->view->regionalRow = $regionalRow;
        $this->view->getAllStates = $this->stateModel->getAll();
        $this->view->regionalRowData = array(
            'description'   => $regionalRow->getDescription(),
        	'status'        => $regionalRow->getStatus()
        );

        $data = $this->serviceAreaModel->getAllServiceAreaByRegionalId($regionalRow->getId());
 
        $this->view->serviceAreaRow = $data;
        $filter = $this->serviceAreaModel->filterAddress($data);
        
        $this->view->allStateId = $filter['allStateId'];
        $this->view->allCityId = $filter['allCityId'];
        $this->view->allNeighborhoodId = $filter['allNeighborhoodId'];
        
        $this->view->getAllCities = $filter['getAllCities'];
        $this->view->getAllNeighborhoods = $filter['getAllNeighborhoods'];
        $this->view->getAllStates = $this->stateModel->getAll();
        
        //var_dump($this->c);
        
        if ($this->roleRow->getIsSystemAdmin() == 1) {
            $listAdmin = true;
        }

        if ($this->aclModel->isAllowed($this->userAuth->getRole(), 'management:regional', 'list-address-by-regional')) {
            if (!$listAdmin) {
                $userLocality = $this->userLocalityModel->getUserLocalityByUserId($this->userAuth->getUserId());
                $regionalIdUserLogged = $userLocality->getRegionalId();
                $serviceAreaData = $this->serviceAreaModel->getAllServiceAreaByRegionalId($regionalIdUserLogged);
                
                $filter[$serviceAreaData['indice']] = $serviceAreaData['value'][$serviceAreaData['indice']];
                $this->view->serviceArea = $serviceAreaData;
                $this->view->getAllStates = $this->stateModel->getAll(null,null,null,null,$filter);
            }
        }
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        //$regionalTransactionData = $this->_getAllParams();
        $regionalTransactionData = $getParams;
        
        $this->view->regionalTransactionData = $regionalTransactionData;
        $regionalIdUserLogged = (isset($regionalIdUserLogged))?$regionalIdUserLogged:null;
        $updateRegionalTransaction = $this->regionalModel->updateRegionalTransaction($regionalRow,$regionalTransactionData,$regionalIdUserLogged,$this->roleRow);
        if (!$updateRegionalTransaction['status']) {
            $this->view->messageError = $updateRegionalTransaction['messageError'];
            return;
        }
        
        $this->view->itemSuccess = true;
        $this->view->loadUrlRegional = $this->view
            ->baseUrl('/management/regional/success/itemUpdateSuccess/true/description/'
                .urlencode($regionalTransactionData['regional']['description']));      
        
    
        
    }
    
    
    public function ola()
    {
        echo "Ola Mundo 2";
        return true;
    }

}