<?php
/**
 * 
 * Model_ServiceArea
 * @uses  
 * @author mcianci
 *
 */
class Model_ServiceArea
{

    public $dbTable_ServiceArea = "";
    
    function __construct() {
        $this->dbTable_ServiceArea = new DbTable_ServiceArea();
    }

    function createServiceArea($regionalId, $data)
    {
        $data['regional_id'] = $regionalId;
        $data = $this->_filterInputServiceArea($data)->getUnescaped();
        $serviceAreaRowData = DbTable_ServiceArea::getInstance()->createRow()
            ->setRegionalId($data['regional_id'])
            ->setStateId(isset($data['StateId'])? $data['StateId']:null)
            ->setCityId(isset($data['CityId'])? $data['CityId']:null)
            ->setNeighborhoodId(isset($data['NeighborhoodId'])? $data['NeighborhoodId']:null)
            ;
        $serviceAreaRowData->save();
        return array(
            'status' => true,
            'lastInsertId' => $serviceAreaRowData->getId()
        );
    }

    protected function _filterInputServiceArea($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
            ),
            array( //validates
                'regional_id' => array('NotEmpty', 'presence' => 'required'),
                'StateId' => array('allowEmpty' => true),
                'CityId' => array('allowEmpty' => true),
                'NeighborhoodId' => array('allowEmpty' => true)
            ),
            $params
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }

    public function getServiceAreaByRegionalId($regionalId)
    {
        return $this->dbTable_ServiceArea->fetchRow(array('RegionalId = ?' => $regionalId));
    }

    public function getAllServiceAreaByRegionalId($regionalId)
    {
        
        $index['StateId'] = null;
        $index['CityId'] = null;
        $index['NeighborhoodId']=null;
        
        $serviceArea = $this->getAll(array('RegionalId = ?' => $regionalId));
        
        foreach ($serviceArea as $valueData) {
            foreach ($valueData as $keyData => $valueRow) {
                if ($keyData == 'StateId' and $valueRow != NULL) {
                    $where[] = array($keyData => $valueRow);
                    $col = $keyData;
                    $index['StateId'][] = $valueRow;
                } else if ($keyData == 'CityId' and $valueRow != NULL) {
                    $where[] = array($keyData => $valueRow);
                    $col = $keyData;
                    $index['CityId'][] = $valueRow;
                } else if($keyData == 'NeighborhoodId' and $valueRow != NULL) {
                    $where[] = array($keyData => $valueRow);
                    $col = $keyData;
                    $index['NeighborhoodId'][] = $valueRow;
                }
            }
        }
        
        return array('dados' => $where, 'indice' => $col, 'value' => $index);
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_ServiceArea->fetchAll($where, $order, $count, $offset);
    }

    public function deleteServiceAreaByRegional($regionalRow)
    {   
        $whereDeleteServiceArea = array('RegionalId = ?' => $regionalRow->getId());
        DbTable_ServiceArea::getInstance()->delete($whereDeleteServiceArea);
        return array(
            'status' => true
        );
        
    }

    public function filterAddress($data) {

        $cityModel = new Model_City();
        $stateModel = new Model_State();
        $dbTable_City = new DbTable_City();
        $neighborhoodModel = new Model_Neighborhood();
        
        $filter = array(
            'allStateId' => array(0 => array('StateId' => 0)),
            'allCityId' => null,
            'allNeighborhoodId' => null,
            'getAllCities'=> null,
            'getAllNeighborhoods' => null
        );
       switch ($data['indice']) {
            case 'StateId':
                $filter['allStateId'] = $data['dados'];
                break;
            case 'CityId':
                $filter['allCityId'] = $data['dados'];
                $city = $dbTable_City->getById($data['dados'][0]['CityId']);
                $stateId = $city->getStateId();
                $filter['allStateId'] = array(0 => array('StateId' => $stateId));
                $localData['uf'] = $city->getUf();

                break;
            case 'NeighborhoodId':
                $filter['allNeighborhoodId'] = $data['dados'];
                $neighborhood = $neighborhoodModel
                    ->getNeighborhoodById($data['dados'][0]['NeighborhoodId']);
                $cityId = $neighborhood->getCityId();
                $state = $stateModel->getStateByUf($neighborhood->getUf());
                $filter['allCityId'] = array(0 => array('CityId' => $cityId));
                $filter['allStateId'] = array(0 => array('StateId' => $state->getId()));
                $localData['uf'] = $neighborhood->getUf();
                $localData['city_id'] = $cityId;
                break;
            default:
                break;
        }
        
        $filter['getAllStates'] = $stateModel->getAll();
        
        if (isset($localData['uf'])){
            $filter['getAllCities'] = $cityModel->getAllCityByUf($localData['uf']);
        }
        
        if (isset($localData['city_id'])){
            $filter['getAllNeighborhoods'] = $neighborhoodModel
                ->getAllNeighborhoodByCityId($localData['city_id']);
        }

        return $filter;
    }
    
    public function filterAddressToInsertRegional($data) {

        $cityModel = new Model_City();
        $stateModel = new Model_State();
        $dbTable_City = new DbTable_City();
        $neighborhoodModel = new Model_Neighborhood();
        
        $filter = array(
            'getAllStates' => null,
            'getAllCities'=> null,
            'getAllNeighborhoods' => null
        );
        
       switch ($data['indice']) {
           
            case 'StateId':
                $filter['allStateId'] = $data['dados'];
                $filter['getAllStates'] = $stateModel->getAll();
                break;
            
            case 'CityId':
                $filter['allCityId'] = $data['dados'];
                $city = $dbTable_City->getById($data['dados'][0]['CityId']);
                $stateId = $city->getStateId();
                $filter['allStateId'] = array(0 => array('StateId' => $stateId));
                $localData['uf'] = $city->getUf();

                break;
            case 'NeighborhoodId':
                $filter['allNeighborhoodId'] = $data['dados'];
                $neighborhood = $neighborhoodModel
                    ->getNeighborhoodById($data['dados'][0]['NeighborhoodId']);
                $cityId = $neighborhood->getCityId();
                $state = $stateModel->getStateByUf($neighborhood->getUf());
                $filter['allCityId'] = array(0 => array('CityId' => $cityId));
                $filter['allStateId'] = array(0 => array('StateId' => $state->getId()));
                $localData['uf'] = $neighborhood->getUf();
                $localData['city_id'] = $cityId;
                break;
            
            default:
                break;
        }
        
        $filter['getAllStates'] = $stateModel->getAll();
        
        if (isset($localData['uf'])){
            $filter['getAllCities'] = $cityModel->getAllCityByUf($localData['uf']);
        }
        
        if (isset($localData['city_id'])){
            $filter['getAllNeighborhoods'] = $neighborhoodModel
                ->getAllNeighborhoodByCityId($localData['city_id']);
        }

        return $filter;
    }
    
}