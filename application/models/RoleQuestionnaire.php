<?php
/**
 * 
 * Model_RoleQuestionnaire
 * @uses  
 * @author mcianci
 *
 */
class Model_RoleQuestionnaire
{
    public $dbTable_RoleQuestionnaire = "";
    function __construct() {
        $this->dbTable_RoleQuestionnaire = new DbTable_RoleQuestionnaire();
        $this->modelQuestionnaire = new Model_Questionnaire();
    }
    function getRoleQuestionnaireById($Id)
    {
        return $this->dbTable_RoleQuestionnaire->fetchRow(array('Id = ?' => $Id));
    }
    function getRoleQuestionnaireByRoleId($roleId)
    {
        return $this->dbTable_RoleQuestionnaire->fetchAll(array('RoleId = ?' => $roleId));
    } 
    function getRoleQuestionnaireByQuestionnaireId($questionnaireId)
    {
        return $this->dbTable_RoleQuestionnaire->fetchAll(array('QuestionnaireId = ?' => $questionnaireId));
    }
    public function getAllRoleQuestionnaire($where = null, $order = null, $count = null, $offset = null, $filter = null)
    {
        $query = $this->dbTable_RoleQuestionnaire
                    ->select()
                    ->distinct()
                    ->setIntegrityCheck(false)
                    ->from(
                        array('RQ' => 'RoleQuestionnaire'),
                        array('Id','RoleId','QuestionnaireId','StartDate','EndDate'),
                        null
                );
        
        if (isset($filter['role_id']) and $filter['role_id']) {
            $query->joinLeft(
                array('R' => 'Role'),
                'R.Id = RQ.RoleId',
                null // array('RoleDescription'=>'Description','RoleLongDescription'=>'LongDescription', 'ParentRole','IsSystemAdmin','IsSystemRole')
            ); 
        }
        
        if (isset($filter['questionnaire_id']) and $filter['questionnaire_id']) {
            $query->joinLeft(
                array('Q' => 'Questionnaire'),
                'Q.Id = RQ.QuestionnaireId',
                null // array('Title','QuestionnaireDescription''=>'Description','QuestionnaireLongDescription'=>'LongDescription','Version','OperationBeginning','OperationEnding','DevolutiveCalcId')
            ); 
        }
        if ($where) {
            $query->where($where);
        }
        if (!$order) {
            $order = 'Id ASC';
        }
        $query->order($order);
        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
    }
    public function createRoleQuestionnaire($data)
    {
        $data = $this->_filterInputRoleQuestionnaire($data)->getUnescaped();
        
        $questionnaireId = $data['questionnaire_id'];
        $startDate = Vtx_Util_Date::format_iso($data['start_date']);
        $endDate = Vtx_Util_Date::format_iso($data['end_date']);
        
        if (!$this->modelQuestionnaire->verifyQuestionnaireOperation($questionnaireId,$startDate,$endDate)) {
            return array(
                'status' => false,
                'messageError' => 'Período de configuração inválido.'
            );
        }
        
        $rqRowData = DbTable_RoleQuestionnaire::getInstance()->createRow()
            ->setRoleId($data['role_id'])
            ->setQuestionnaireId($questionnaireId)
            ->setStartDate($startDate)
            ->setEndDate($endDate);
        $rqRowData->save();
        return array(
            'status' => true,
            'lastInsertId' => $rqRowData->getId()
        );
    }
    public function updateRoleQuestionnaire($rqRowData, $data)
    {
        $data = $this->_filterInputRoleQuestionnaire($data)->getUnescaped();
        
        $questionnaireId = $data['questionnaire_id'];
        $startDate = Vtx_Util_Date::format_iso($data['start_date']);
        $endDate = Vtx_Util_Date::format_iso($data['end_date']);
        
        if (!$this->modelQuestionnaire->verifyQuestionnaireOperation($questionnaireId,$startDate,$endDate)) {
            return array(
                'status' => false,
                'messageError' => 'Período de configuração inválido.'
            );
        }
        
        $rqRowData
                /*
            ->setRoleId($data['role_id'])
            ->setQuestionnaireId($data['questionnaire_id'])
                */
            ->setStartDate($startDate)
            ->setEndDate($endDate);
        $rqRowData->save();
        return array(
            'status' => true
        );
    }
    protected function _filterInputRoleQuestionnaire($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
            ),
            array( //validates
                'role_id' => array(
                    'NotEmpty',
                    'messages' => array('Escolha o Papel (perfil)'),
                    ),
                'questionnaire_id' => array(
                    'NotEmpty',
                    'messages' => array('Escolha o questionário'),
                    ),
                'start_date' => array(
                    'NotEmpty',
                    new Zend_Validate_Date('dd/MM/yyyy')
                ),
                'end_date' => array(
                    'NotEmpty',
                    new Zend_Validate_Date('dd/MM/yyyy')
                )
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
    public function deleteRoleQuestionnaire($roleQuestionnaireRow)
    {
        DbTable_RoleQuestionnaire::getInstance()->getAdapter()->beginTransaction();
        try {
            $roleQuestionnaireRow->delete();
            DbTable_RoleQuestionnaire::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_RoleQuestionnaire::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_RoleQuestionnaire::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }  
}