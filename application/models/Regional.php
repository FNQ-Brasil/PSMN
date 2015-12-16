<?php
/**
 * 
 * Model_Regional
 * @uses  
 * @author gersonlv
 *
 */
class Model_Regional
{

    public $dbTable_Regional = "";
    
    function __construct() {
        $this->dbTable_Regional = new DbTable_Regional();
    }
    
    public function hasPermissionToEdit($regionalEditorId,$regionalRow) 
    {
        $regionalEditorRow = $this->getRegionalById($regionalEditorId);
        if ($regionalEditorRow->getNational() == 'S') {
            return true;
        } else if ($regionalEditorRow->getNational() == $regionalRow->getNational()) {
            return true;
        }
        return false;
    }
    
    public function hasPermissionToEditUser($userEditingId,$userEditorId) 
    {
        $modelUserLocality = new Model_UserLocality();
        
        $userLocalityEditingRow = $modelUserLocality->getUserLocalityByUserId($userEditingId);
        $userLocalityEditorRow = $modelUserLocality->getUserLocalityByUserId($userEditorId);
        
        $regionalEditorRow = $this->getRegionalById($userLocalityEditorRow->getRegionalId());
        $regionalEditingRow = $this->getRegionalById($userLocalityEditingRow->getRegionalId());
            
        if ($regionalEditorRow->getNational() == 'S') {
            return true;
        } else if ($regionalEditorRow->getNational() == $regionalEditingRow->getNational()) {
            return true;
        }

        return false;
    }
    
    public function createRegional($data)
    {
        $data = $this->_filterInputRegional($data)->getUnescaped();
        $verifyRegional = DbTable_Regional::getInstance()->fetchRow(array(
            'Description = ?' => $data['description']
        ));
        if ($verifyRegional) {
            return array(
                'status' => false, 
                'messageError' => 'Nome ('.$data['description'].') existente.'
                    );
        }
        $regionalRowData = DbTable_Regional::getInstance()->createRow()
            ->setDescription($data['description'])
            ->setStatus($data['status'])
            ->setNational(isset($data['national'])? $data['national']:'N')
            ;
        $regionalRowData->save();
        return array(
            'status' => true,
            'lastInsertId' => $regionalRowData->getId()
        );
    }

    public function updateRegional($regionalRowData, $data)
    {
        $data = $this->_filterInputRegional($data)->getUnescaped();
        $regionalRowData
            ->setDescription($data['description'])
            ->setStatus($data['status'])
            ->setNational(isset($data['national'])? $data['national']:'N')
            ;
        $regionalRowData->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputRegional($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StringTrim'), // 'StripTags', 
                'description' => array(
                    //array( 'Alnum', array('allowwhitespace' => true) )
                ),
                'status' => array(
                    array('Alnum',
                        array('allowwhitespace' => true)
                        )
                )
            ),
            array( //validates
                'description' => array('NotEmpty'),
                'status' => array('NotEmpty')
            ),
            $params,
            array('presence' => 'required')
        );
        
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        
        return $input;
    }

    public function deleteRegional($regionalRow)
    {
        DbTable_Regional::getInstance()->getAdapter()->beginTransaction();
        try {
            
            $whereDeleteServiceArea = array('RegionalId = ?' => $regionalRow->getId());
            DbTable_ServiceArea::getInstance()->delete($whereDeleteServiceArea);            
            
            $regionalRow->delete();
            DbTable_Regional::getInstance()->getAdapter()->commit();
            
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Regional::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Regional::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getRegionalById($Id)
    {
        return $this->dbTable_Regional->fetchRow(array('Id = ?' => $Id));
    }
    
    public function getAllRegionalByOneRegionalServiceArea(
        $roleId = null, $userLoggedRegionalId, $filter = null, $orderBy = null, $count = null, $offset = null
    ) {
        if (isset($userLoggedRegionalId) and $userLoggedRegionalId) {
            $modelRegional = new Model_Regional();
            $regionalRow = $modelRegional->getRegionalById($userLoggedRegionalId);
            $filter['regional_national'] = $regionalRow->getNational();
        }
        $query = $this->dbTable_Regional->getAllRegionalByOneRegionalServiceArea(
                $roleId, $userLoggedRegionalId, 'select', $filter, $orderBy
            );
        
        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
    }
    
    
    /**
     * metodo retirado de MPE : Admin -> /management/regional/index
     * 
     * Recupera regionais de uma UF.
     *  
     */
    public function getAllRegional($stateId) 
    {
        $query = $this->dbTable_Regional
                ->select()
                ->distinct()
                ->setIntegrityCheck(false)
                ->from(
                array('R' => 'Regional'), array('Id', 'Description'), null
        );
        $query->joinLeft(
                array('SA' => 'ServiceArea'), "R.Id = SA.RegionalId", null // array('StateId', 'CityId', 'NeighborhoodId')
        );
//        if ($where) {
//            $query->where($where['cond'], $where['query']);
//        }

        if (isset($stateId) && $stateId) {
            //$stateId = $filter['state_id'];
            $query->where('SA.StateId = ?', $stateId);
            $query->orWhere("SA.CityId in (SELECT Id FROM City WHERE StateId in ($stateId))");
            $query->orWhere("SA.NeighborhoodId in (SELECT Id FROM Neighborhood WHERE CityId in (SELECT Id FROM City WHERE StateId in ($stateId)))");
        }
//
//        if (isset($filter['regional']) && $filter['regional']) {
//            $query->where('Description LIKE (?)', '%' . $filter['regional'] . '%');
//        }

        //if (!$orderBy) {
            $orderBy = 'Description ASC';
        //}
        $query->order($orderBy);
        
//        echo $query;
//        die;
   
        return $this->dbTable_Regional->fetch($query, 'all');
        
    }
    
    
    
    
    
    
    public function getAll($where = null, $orderBy = null, $count = null, $offset = null, $filter = null)
    {
        $query = $this->dbTable_Regional
            ->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(
                array('R' => 'Regional'),
                array('Id','Description','Status'),
                null
            );
        $query->joinLeft(
            array('SA' => 'ServiceArea'),
            'R.Id = SA.RegionalId',
            null // array('StateId', 'CityId', 'NeighborhoodId')
        );
        if ($where) {
            $query->where($where['cond'], $where['query']);
        }

        if (isset($filter['state_id']) && $filter['state_id']) {
            $query->where('SA.StateId = ?', $filter['state_id']);
        }
        
        if (isset($filter['regional']) && $filter['regional']) {
            $query->where('Description LIKE (?)', '%'.$filter['regional'].'%');
        }
        
        if (!$orderBy) {
            $orderBy = 'Description ASC';
        }
        $query->order($orderBy);

        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
        //return $this->dbTable_Regional->fetchAll($where = null, $order = null, $count = null, $offset = null);
    }

    public function createRegionalTransaction($regionalTransactionData,$regionalIdUserLogged = null,$roleRow)
    {
        $serviceArea = new Model_ServiceArea();
        // start transaction externo
        Zend_Registry::get('db')->beginTransaction();
        try {
            // Seta regional como NÃO nacional
            $regionalTransactionData['regional']['national'] = 'N';
            // 1.1 Update Regional 
            $createRegional = $this->createRegional($regionalTransactionData['regional']);
            if (!$createRegional['status']) {
                throw new Vtx_UserException($createRegional['messageError']);
            }
            $regionalId = $createRegional['lastInsertId'];
            // 2.1 Create ServiceArea 
            // inserir bairros
            if ($regionalTransactionData['allNeights'] == 's') {
                if (!isset($regionalTransactionData['serviceArea']['neighborhoods'])) {
                    $error['messageError'] = 'Escolha o(s) bairro(s)';
                    throw new Vtx_UserException(
                       $error['messageError']
                    );
                }
                $serviceAreaData['NeighborhoodId'] = $regionalTransactionData['serviceArea']['neighborhoods'];
                $indice = 'NeighborhoodId';
            // inserir cidades
            } else if ($regionalTransactionData['allCities'] == 's') {
                if (!isset($regionalTransactionData['serviceArea']['cities'])) {
                    $error['messageError'] = 'Escolha a(s) cidade(s)';
                    throw new Vtx_UserException(
                       $error['messageError']
                    );
                }
                $serviceAreaData['CityId'] = $regionalTransactionData['serviceArea']['cities'];
                $indice = 'CityId';
            // inserir estados
            } else if ($regionalTransactionData['allUfs'] == 's') {
                if (!isset($regionalTransactionData['serviceArea']['states'])) {
                    $error['messageError'] = 'Escolha o(s) estado(s)';
                    throw new Vtx_UserException(
                       $error['messageError']
                    );
                }
                $serviceAreaData['StateId'] = $regionalTransactionData['serviceArea']['states'];
                $indice = 'StateId';
            } else {
                //$regionalTansactionData = array('serviceArea' => array('states' => array()));
                /* $error['messageError'] = 'Escolha o(s) estado(s)'; throw new Vtx_UserException($error['messageError'] ); return; */
                for ($index = 1; $index <= 27; $index++) {
                    $statesFor[] = $index; 
                }
                // Seta regional como nacional
                $regionalTransactionData['regional']['national'] = 'S';
                $serviceAreaData['StateId'] = $statesFor;
                $indice = 'StateId';
            }
            // Verifica permissao de insercao de regional por service area, somente pra gestores
            if ($roleRow->getIsSystemAdmin() != 1) {
                $newServiceAreaData = $serviceAreaData;
                $this->hasPermissionToCreateRegional($regionalIdUserLogged,$newServiceAreaData,$indice);
            } 
            $dataSA = array();
            $controle = array();
            foreach ($serviceAreaData as $keyDados => $keyValue) {
                foreach ($keyValue as $value) {
                    if (!in_array($value, $controle)) {
                        $dataSA[$keyDados] = $value;
                        $createServiceArea = $serviceArea->createServiceArea($regionalId,$dataSA);
                        if (!$createServiceArea['status']) {
                            throw new Vtx_UserException($createServiceArea['messageError']);
                        }                        
                    }
                    $controle[] = $value;
                }
            }
            // end transaction externo
            Zend_Registry::get('db')->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            Zend_Registry::get('db')->rollBack();
            return array(
                'status' => false, 
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            Zend_Registry::get('db')->rollBack();
            throw new Exception($e);
        }
    }

    public function updateRegionalTransaction($regionalRow,$regionalTransactionData,$regionalGestorId = null,$roleRow)
    {
        $serviceArea = new Model_ServiceArea();
        // start transaction externo
        Zend_Registry::get('db')->beginTransaction();
        try {
            $regionalId = $regionalRow->getId();
            // 1.1 Update Regional 
            $updateRegional = $this->updateRegional($regionalRow,$regionalTransactionData['regional']);
            if (!$updateRegional['status']) {
                throw new Vtx_UserException($updateRegional['messageError']);
            }
            // 2.1 Delete ServiceArea by Regional
            $serviceArea->deleteServiceAreaByRegional($regionalRow);
            // 2.2 Create ServiceArea
            // inserir bairros
            if ($regionalTransactionData['allNeights'] == 's') {
                if (!isset($regionalTransactionData['serviceArea']['neighborhoods'])) {
                    $error['messageError'] = 'Escolha o(s) bairro(s)';
                    throw new Vtx_UserException(
                       $error['messageError']
                    );
                }
                $serviceAreaData['NeighborhoodId'] = $regionalTransactionData['serviceArea']['neighborhoods'];
                $indice = 'NeighborhoodId';
            // inserir cidades
            } else if ($regionalTransactionData['allCities'] == 's') {
                if (!isset($regionalTransactionData['serviceArea']['cities'])) {
                    $error['messageError'] = 'Escolha a(s) cidade(s)';
                    throw new Vtx_UserException(
                       $error['messageError']
                    );
                }
                $serviceAreaData['CityId'] = $regionalTransactionData['serviceArea']['cities'];
                $indice = 'CityId';
            // inserir estados selecionados
            } else if ($regionalTransactionData['allUfs'] == 's') {
                if (!isset($regionalTransactionData['serviceArea']['states'])) {
                    $error['messageError'] = 'Escolha o(s) estado(s)';
                    throw new Vtx_UserException(
                       $error['messageError']
                    );
                }
                $serviceAreaData['StateId'] = $regionalTransactionData['serviceArea']['states'];
                $indice = 'StateId';
            // inserir todos estados
            } else {
                for ($index = 1; $index <= 27; $index++) {
                    $allStates[] = $index; 
                }
                // Seta regional como nacional
                $regionalTransactionData['regional']['national'] = 'S';
                $serviceAreaData['StateId'] = $allStates;
                $indice = 'StateId';
            }
            
            if ($roleRow->getIsSystemAdmin() != 1) {
                $newServiceAreaData = $serviceAreaData;
                $this->hasPermissionToCreateRegional($regionalGestorId,$newServiceAreaData,$indice);
            }
 
            $dataSA = array();
            $controle = array();
            foreach ($serviceAreaData as $keyDados => $keyValue) {
                foreach ($keyValue as $value) {
                    if (!in_array($value, $controle)) {
                        $dataSA[$keyDados] = $value;
                        $createServiceArea = $serviceArea->createServiceArea($regionalId,$dataSA);
                        if (!$createServiceArea['status']) {
                            throw new Vtx_UserException($createServiceArea['messageError']);
                        }
                    }
                    $controle[] = $value;
                }
            }
            // end transaction externo
            Zend_Registry::get('db')->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            Zend_Registry::get('db')->rollBack();
            return array(
                'status' => false, 
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            Zend_Registry::get('db')->rollBack();
            throw new Exception($e);
        }
    }
    
    public function hasPermissionToCreateRegional($regionalIdOwner,$newServiceAreaData,$indice) {
        $modelServiceArea = new Model_ServiceArea();
        $regionalOwnerSA = $modelServiceArea->getAllServiceAreaByRegionalId($regionalIdOwner);
        switch ($indice) {
            case 'StateId':
                    // só verifica se tem acesso aos Estados
                    if(!($regionalOwnerSA['indice'] == 'StateId')) {
                        $error['messageError'] = 'Sem permissão para criar regional com Estado(s)';
                        throw new Vtx_UserException(
                           $error['messageError']
                        );
                        return;
                    }
                    // verifica se é/são o(s) mesmo(s) Estado(s)
                    if ( !(Vtx_Util_Array::array_all_in_array($newServiceAreaData['StateId'], $regionalOwnerSA['value']['StateId'])) ) {
                        $error['messageError'] = 'Sem permissão para criar regional com este(s) Estado(s)';
                        throw new Vtx_UserException(
                           $error['messageError']
                        );
                        return;
                    }
                break;
            case 'CityId':
                    //Regional Estadual, pode criar p/ cidades.
                    if($regionalOwnerSA['indice'] == 'StateId') {
                        return true;
                    }
                    if($regionalOwnerSA['indice'] == 'NeighborhoodId') {
                        $error['messageError'] = 'Sem permissão para criar regional com esta(s) Cidade(s)';
                        throw new Vtx_UserException(
                           $error['messageError']
                        );
                        return;
                    }
                    //Regional Municipal, apenas para suas cidades.
                    if ( !(Vtx_Util_Array::array_all_in_array($newServiceAreaData['CityId'], $regionalOwnerSA['value']['CityId'])) ) {
                        $error['messageError'] = 'Sem permissão para criar regional com esta(s) Cidade(s)';
                        throw new Vtx_UserException(
                           $error['messageError']
                        );
                        return;
                    }
                break;
            case 'NeighborhoodId':
                    //Regional Estadual, pode criar p/ cidades.
                    if($regionalOwnerSA['indice'] == 'StateId') {
                        return true;
                    }
                    //Regional Municipal, pode criar p/  bairros.
                    if($regionalOwnerSA['indice'] == 'CityId') {
                        return true;
                    }
                    //Regional Bairro, apenas para seus bairros.
                    if ( !(Vtx_Util_Array::array_all_in_array($newServiceAreaData['NeighborhoodId'], $regionalOwnerSA['value']['NeighborhoodId'])) ) {
                        $error['messageError'] = 'Sem permissão para criar regional com este(s) Bairro(s)';
                        throw new Vtx_UserException(
                           $error['messageError']
                        );
                        return;
                    }
                break;
            default:
                break;
        }
        return true;
    }
    
    function userServiceAreaRegional($userLoggedRow) {
        $whereSA = array();
        $model_ServiceArea      = new Model_ServiceArea();
        $model_UserLocality     = new Model_UserLocality();
        if ($userLoggedRow->getRoleId() != 1) {
            $userLocalityRow = $model_UserLocality->getUserLocalityByUserId($userLoggedRow->getUserId());
            $whereSA = array('RegionalId = ?' => $userLocalityRow->getRegionalId());
        }
        $serviceArea = $model_ServiceArea->getAll($whereSA);
        foreach ($serviceArea as $keyRow => $valueData) {
            foreach ($valueData as $keyData => $valueRow) {
                if ($keyData == 'StateId' and $valueRow != NULL) {
                    $col = $keyData;
                    $where[] = array("$keyData = ?" => $valueRow);
                } else if ($keyData == 'CityId' and $valueRow != NULL) {
                    $where[] = array("$keyData = ?" => $valueRow);
                    $col = $keyData;
                } else if($keyData == 'NeighborhoodId' and $valueRow != NULL) {
                    $where[] = array("$keyData = ?" => $valueRow);
                    $col = $keyData;
                }
            }
        }
        $array = array($where,$col);
        return $array;
    }
    
    function getServiceAreaByRegionalId($regionalId) {
        $model_ServiceArea      = new Model_ServiceArea();
        $whereSA = array('RegionalId = ?' => $regionalId);
        $serviceArea = $model_ServiceArea->getAll($whereSA);
        foreach ($serviceArea as $keyRow => $valueData) {
            foreach ($valueData as $keyData => $valueRow) {
                if ($keyData == 'StateId' and $valueRow != NULL) {
                    $col = $keyData;
                    $where[] = array("$keyData = ?" => $valueRow);
                } else if ($keyData == 'CityId' and $valueRow != NULL) {
                    $where[] = array("$keyData = ?" => $valueRow);
                    $col = $keyData;
                } else if($keyData == 'NeighborhoodId' and $valueRow != NULL) {
                    $where[] = array("$keyData = ?" => $valueRow);
                    $col = $keyData;
                }
            }
        }
        $array = array($where, $col);
        return $array;
    }

    public function getRegionalByUser($userId) {
        return $this->dbTable_Regional->getRegionalByUser($userId);
    }
    
}
