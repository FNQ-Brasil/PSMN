<?php
/**
 * 
 * Model_EnterpriseCategoryAwardCompetition
 * @uses  
 * @author mcianci
 *
 */
class Model_EnterpriseCategoryAwardCompetition
{

    public $dbTable_EnterpriseCategoryAwardCompetition = "";
    
    function __construct() {
        $this->dbTable_EnterpriseCategoryAwardCompetition = new DbTable_EnterpriseCategoryAwardCompetition();
    }

    public function getAllEnterpriseCategoryAwardCompetition($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_EnterpriseCategoryAwardCompetition->fetchAll($where, $order, $count, $offset);
    }
 
    public function hasECAC($enterpriseId,$competitionId)
    {
        /*
        echo $enterpriseId.' '.$competitionId;
        die;
        */
        $obj = $this->dbTable_EnterpriseCategoryAwardCompetition->fetchRow(
                array(
                'EnterpriseId = ?' => $enterpriseId,
                'CompetitionId = ?' => $competitionId,
                )
        );
        if (!$obj) {
            return false;
        }
        return true;
    }

    function getEnterpriseCategoryAwardCompetitionById($Id)
    {
        return $this->dbTable_EnterpriseCategoryAwardCompetition->fetchRow(array('Id = ?' => $Id));
    }
    
    function getEnterpriseCategoryAwardCompetitionByEnterpriseId($enterpriseId)
    {
        return $this->dbTable_EnterpriseCategoryAwardCompetition->fetchRow(array('EnterpriseId = ?' => $enterpriseId));
    }
    
    public function createECAC($data) 
    {
        $data = $this->_filterInputECAC($data)->getUnescaped();
        $enterpriseCategoryAwardCompetitionRow = DbTable_EnterpriseCategoryAwardCompetition::getInstance()->createRow()
            ->setEnterpriseId($data['enterprise_id'])
            ->setCompetitionId($data['competition_id'])
            ->setEnterpriseCategoryAwardId($data['category_award_id'])
            ->setCreatedAt(new Zend_Db_Expr('NOW()'))
            ->setToken($this->createRandomToken())
            ;
        $enterpriseCategoryAwardCompetitionRow->save();
        return array(
            'status' => true
        );
    }



    protected function createRandomToken(){
        do{
            $token = substr(md5(uniqid(rand(), true)),16,16);
        }while($this->tokenAlreadyInUse($token));
        return $token;
    }
    
    protected function _filterInputECAC($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
            ),
            array( //validates
                'enterprise_id' => array(
                    'NotEmpty', 
                    'messages' => array('Erro ao cadastrar empresa'),
                    'presence' => 'required'
                ),
                'competition_id' => array(
                    'NotEmpty', 
                    'messages' => array('Erro ao cadastrar empresa'),
                    'presence' => 'required'
                ),
                'category_award_id' => array(
                    'NotEmpty', 
                    'messages' => array('Escolha a categoria do premio'),
                    'presence' => 'required'
                ),
                'token' => array('allowEmpty' => true)
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

    public function enterpriseHasVerifiedECAC($enterpriseId,$enterpriseIdKey = null){
        if($enterpriseIdKey){
            $enterpriseModel = new Model_Enterprise();
            $enterpriseId = $enterpriseModel->getEnterpriseByIdKey($enterpriseIdKey)->getId();
        }
        return count($this->dbTable_EnterpriseCategoryAwardCompetition->getVerifiedECACsForEnterprise($enterpriseId)) > 0;
    }

    public function tokenAlreadyInUse($token){
        return count($this->dbTable_EnterpriseCategoryAwardCompetition->getECACByToken($token)) > 0;
    }

    public function getECACByEnterpriseIdAndYear($enterpriseId, $year){
        return $this->dbTable_EnterpriseCategoryAwardCompetition->getECACByEnterpriseIdAndYear($enterpriseId,$year);
    }

    public function getECACByTokenAndYear($token, $year){
        return $this->dbTable_EnterpriseCategoryAwardCompetition->getECACByTokenAndYear($token,$year);
    }

    public function updateECACVerifiedByToken($token){
        return $this->dbTable_EnterpriseCategoryAwardCompetition->updateECACVerifiedByToken($token);
    }

    public function updateECACVerifiedByEnterpriseIdAndYear($enterpriseId, $competitionId){
        return $this->dbTable_EnterpriseCategoryAwardCompetition->updateECACVerifiedByEnterpriseIdAndYear($enterpriseId, $competitionId);
    }
}