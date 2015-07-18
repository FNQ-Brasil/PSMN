<?php
/**
 * 
 * Model_AddressPresident
 * @uses  
 * @author mcianci
 *
 */
class Model_AddressPresident
{

    public $dbTable_AddressPresident = "";
    
    function __construct() {
        $this->dbTable_AddressPresident = new DbTable_AddressPresident();
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_AddressPresident->fetchAll($where, $order, $count, $offset);
    }

    public function createAddressPresident($data)
    {
        $data = $this->_filterInputAddressPresident($data)->getUnescaped();
        $addressPresidentRowData = DbTable_AddressPresident::getInstance()->createRow()
            ->setAddressId(isset($data['address_id'])? $data['address_id'] : null)
            ->setPresidentId($data['president_id'])
            ->setCityId(isset($data['city_id'])? $data['city_id'] : null)
            ->setStateId(isset($data['state_id'])? $data['state_id'] : null)
            ->setCep(isset($data['cep'])? $data['cep'] : null)
            ->setStreetNameFull(isset($data['name_full_log'])? $data['name_full_log'] : null)
            ->setStreetNumber(isset($data['street_number'])? $data['street_number'] : null)
            ->setStreetCompletion(isset($data['street_completion'])? $data['street_completion'] : null)
            ->setNeighborhoodId(isset($data['neighborhood_id'])? $data['neighborhood_id'] : null);
        $addressPresidentRowData->save();
        //DbTable_AddressPresident::getInstance()->getAdapter()->commit();
        return array(
            'status' => true,
            'lastInsertId' => $addressPresidentRowData->getId()
        );
    }

    public function updateAddressPresident($addressPresidentRow,$data)
    {
        $data = $this->_filterInputAddressPresident($data)->getUnescaped();
        $addressPresidentRow
            ->setAddressId(isset($data['address_id'])? 
                $data['address_id'] : $addressPresidentRow->getAddressId())
            ->setPresidentId(isset($data['president_id'])? 
                $data['president_id'] : $addressPresidentRow->getPresidentId())
            ->setCityId(isset($data['city_id'])? 
                $data['city_id'] : $addressPresidentRow->getCityId())
            ->setStateId(isset($data['state_id'])? 
                $data['state_id'] : $addressPresidentRow->getStateId())
            ->setCep(isset($data['cep'])? 
                $data['cep'] : $addressPresidentRow->getCep())
            ->setStreetNameFull(isset($data['name_full_log'])? 
                $data['name_full_log'] : $addressPresidentRow->getStreetNameFull())
            ->setStreetNumber(isset($data['street_number'])? 
                $data['street_number'] : $addressPresidentRow->getStreetNumber())
            ->setStreetCompletion(isset($data['street_completion'])? 
                $data['street_completion'] : $addressPresidentRow->getStreetCompletion())
            ->setNeighborhoodId(isset($data['neighborhood_id'])? 
                $data['neighborhood_id'] : $addressPresidentRow->getNeighborhoodId());
        $addressPresidentRow->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputAddressPresident($params)
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
                'president_id' => array(
                    'NotEmpty',
                    'presence' => 'required'
                ),
                'cep' => array(
                    'NotEmpty',
                    'messages' => array('Digite apenas números no CEP da candidata.'),
                    'presence' => 'required'
                ),
                'state_id' => array('NotEmpty', 'messages' => array('Escolha o estado da candidata.')),
                'city_id' => array('NotEmpty', 'messages' => array('Escolha a cidade da candidata.')),
                'neighborhood_id' => array('NotEmpty', 'messages' => array('Escolha o bairro da candidata.')),
                'name_full_log' => array('NotEmpty', 'messages' => array('Digite o nome da Rua da candidata.')),
                'street_number' => array(
                    'NotEmpty', 
                    'messages' => array('Digite o Número da Rua da candidata.')
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

    function getAddressPresidentByPresidentId($presidentId)
    {
        return $this->dbTable_AddressPresident->fetchRow(array('PresidentId = ?' => $presidentId));
    }

}