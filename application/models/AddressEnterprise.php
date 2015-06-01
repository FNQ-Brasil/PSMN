<?php
/**
 * 
 * Model_AddressEnterprise
 * @uses  
 * @author mcianci
 *
 */
class Model_AddressEnterprise
{

    public $dbTable_AddressEnterprise = "";
    
    function __construct() {
        $this->dbTable_AddressEnterprise = new DbTable_AddressEnterprise();
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_AddressEnterprise->fetchAll($where, $order, $count, $offset);
    }

    public function createAddressEnterprise($data)
    {
        $data = $this->_filterInputAddressEnterprise($data)->getUnescaped();
        $addressEnterpriseRowData = DbTable_AddressEnterprise::getInstance()->createRow()
            ->setAddressId(isset($data['address_id'])? $data['address_id'] : null)
            ->setEnterpriseId($data['enterprise_id'])
            ->setCityId(isset($data['city_id'])? $data['city_id'] : null)
            ->setStateId(isset($data['state_id'])? $data['state_id'] : null)
            ->setCep(isset($data['cep'])? $data['cep'] : null)
            ->setStreetNameFull(isset($data['name_full_log'])? $data['name_full_log'] : null)
            ->setStreetNumber(isset($data['street_number'])? $data['street_number'] : null)
            ->setStreetCompletion(isset($data['street_completion'])? $data['street_completion'] : null)
            ->setNeighborhoodId(isset($data['neighborhood_id'])? $data['neighborhood_id'] : null);
        $addressEnterpriseRowData->save();
        //DbTable_AddressEnterprise::getInstance()->getAdapter()->commit();
        return array(
            'status' => true,
            'lastInsertId' => $addressEnterpriseRowData->getId()
        );
    }

    public function updateAddressEnterprise($addressEnterpriseRow,$data)
    {
        $data = $this->_filterInputAddressEnterprise($data)->getUnescaped();
        $addressEnterpriseRow
            ->setAddressId(isset($data['address_id'])? 
                $data['address_id'] : $addressEnterpriseRow->getAddressId())
            ->setEnterpriseId(isset($data['enterprise_id'])? 
                $data['enterprise_id'] : $addressEnterpriseRow->getEnterpriseId())
            ->setCityId(isset($data['city_id'])? 
                $data['city_id'] : $addressEnterpriseRow->getCityId())
            ->setStateId(isset($data['state_id'])? 
                $data['state_id'] : $addressEnterpriseRow->getStateId())
            ->setCep(isset($data['cep'])? 
                $data['cep'] : $addressEnterpriseRow->getCep())
            ->setStreetNameFull(isset($data['name_full_log'])? 
                $data['name_full_log'] : $addressEnterpriseRow->getStreetNameFull())
            ->setStreetNumber(isset($data['street_number'])? 
                $data['street_number'] : $addressEnterpriseRow->getStreetNumber())
            ->setStreetCompletion(isset($data['street_completion'])? 
                $data['street_completion'] : $addressEnterpriseRow->getStreetCompletion())
            ->setNeighborhoodId(isset($data['neighborhood_id'])? 
                $data['neighborhood_id'] : $addressEnterpriseRow->getNeighborhoodId());
        $addressEnterpriseRow->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputAddressEnterprise($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                'cep' => array(
                    array('Alnum', 
                        array('allowwhitespace' => true)
                        ),
                    array('StripTags', 'StringTrim')
                ),
                'street_name_full' => array(
                    array('Alnum',
                        array('allowwhitespace' => true)
                        ),
                    array('StripTags', 'StringTrim')
                )
            ),
            array( //validates
                'address_id' => array('allowEmpty' => true),
                'enterprise_id' => array(
                    'NotEmpty',
                    'presence' => 'required'
                ),
                'cep' => array(
                    'NotEmpty',
                    'messages' => array('Digite apenas números no CEP da empresa.'),
                    'presence' => 'required'
                ),
                'state_id' => array(
                    'NotEmpty', 
                    'messages' => array('Escolha o estado da empresa.')
                ),
                'city_id' => array(
                    'NotEmpty', 
                    'messages' => array('Escolha a cidade da empresa.')
                ),
                'neighborhood_id' => array(
                    'NotEmpty', 
                    'messages' => array('Escolha o bairro da empresa.')
                ),
                'name_full_log' => array(
                    'NotEmpty', 
                    'messages' => array('Digite o nome da Rua da empresa.')
                ),
                'street_number' => array(
                    'NotEmpty', 
                    'messages' => array('Digite o Número da Rua da empresa.')
                ),
                'street_completion' => array('allowEmpty' => true)
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

    function getAddressEnterpriseByEnterpriseId($enterpriseId)
    {
        return $this->dbTable_AddressEnterprise->fetchRow(array('EnterpriseId = ?' => $enterpriseId));
    }

}