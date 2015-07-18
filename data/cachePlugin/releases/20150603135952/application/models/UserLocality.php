<?php
/**
 * 
 * Model_UserLocality
 * @uses  
 * @author mcianci
 *
 */
class Model_UserLocality
{

    public $dbTable_UserLocality = "";
    
    function __construct() {
        $this->dbTable_UserLocality = new DbTable_UserLocality();
    }

    function createUserLocality($data)
    {
        $data = $this->_filterInputUserLocality($data)->getUnescaped();
        $userRowLocalityData = DbTable_UserLocality::getInstance()->createRow()
            ->setUserId($data['user_id'])
            ->setEnterpriseId(isset($data['enterprise_id'])? $data['enterprise_id']:null)
            ->setRegionalId(isset($data['regional_id'])? $data['regional_id']:null);
        $userRowLocalityData->save();
        return array(
            'status' => true,
            'lastInsertId' => $userRowLocalityData->getId()
        );
    }

    function updateUserLocality($userLocalityRow, $data)
    {
        $userLocalityRow
            ->setEnterpriseId(isset($data['enterprise_id'])? $data['enterprise_id']:null)
            ->setRegionalId(isset($data['regional_id'])? $data['regional_id']:null);
        $userLocalityRow->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputUserLocality($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
            ),
            array( //validates
                'user_id' => array('NotEmpty', 'presence' => 'required'),
                'enterprise_id' => array('allowEmpty' => true),
                'regional_id' => array('allowEmpty' => true)
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

    function getUserLocalityByUserId($userId)
    {
        return $this->dbTable_UserLocality->fetchRow(array('UserId = ?' => $userId));
    }
    function getUserLocalityByEnterpriseId($enterpriseId)
    {
        return $this->dbTable_UserLocality->fetchRow(array('EnterpriseId = ?' => $enterpriseId));
    }
    function getEnterpriseByAppraiserId($appraiserId) {
        $where = array('UserId = ?' => $appraiserId, 'EnterpriseId != ?' => 'NULL'); // , 
        return $this->dbTable_UserLocality->fetchAll($where);
    }
    
    
    
}