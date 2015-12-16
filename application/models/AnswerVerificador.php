<?php
/**
 * 
 * Model_AnswerVerificador
 *
 */
class Model_AnswerVerificador
{
    protected $_messagesError = array(
        'AnswerFormError' => 'Erro no preenchimento do campo: ',
    );
    
    private $tbAnswerVerificador = "";

    function __construct()
    {
        $this->tbAnswerVerificador = new DbTable_AnswerVerificador();
        $this->AnnualResultData = new Model_AnnualResultData();
        $this->Acl = Zend_Registry::get('acl');
    }

    public function createAnswer($data, $alternativeRow)
    {
        DbTable_AnswerVerificador::getInstance()->getAdapter()->beginTransaction();
 
			
        try {
            $qstnId = $data['qstn_id'];
            $aaresult_value = $data['aaresult_value'];
            $data = $this->_filterInputAnswer($data)->getUnescaped();
            $data['aaresult_value'] = $aaresult_value;
			


            $answerRow = DbTable_AnswerVerificador::getInstance()->createRow()
                ->setAlternativeId($data['alternative_id'])
                ->setAnswerValue($data['answer_value'])
                ->setStartTime($data['start_time'])
                ->setEndTime($data['end_time'])
                ->setAnswerDate($data['answer_date'])
                ->setUserId($data['user_id'])
				->setEnterpriseId($data['enterprise_id']);
				//print_r($answerRow);exit

//Zend_Debug::Dump($answerRow );exit;
            $answerRow->save();
  

            $AnswerHistory = new Model_AnswerHistoryVerificador();
            $AnswerHistory->createAnswerHistory(array(
                'user_id' => $data['logged_user_id'],
                'answer_id' => $answerRow->getId(),
                'alternative_id' => $answerRow->getAlternativeId(),
                'answer_value' => $answerRow->getAnswerValue(),
                'start_time' => $answerRow->getStartTime(),
                'end_time' => $answerRow->getEndTime(),
                'answer_date' => $answerRow->getAnswerDate(),
				'enterprise_id' => $answerRow->getEnterpriseId()
            ));
			
            
            if ($alternativeRow->getAlternativeTypeId() == Model_AlternativeType::RESULT_ACTION) {
                $this->saveAnswerAnnualResult($answerRow, $data);
            }
            
            $Execution = new Model_Execution();
            $Execution->initExecution($qstnId, $data['user_id']);
            
            DbTable_AnswerVerificador::getInstance()->getAdapter()->commit();
            return array(
                'status' => true, 'row' => $answerRow
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnswerVerificador::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerVerificador::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public function updateAnswer($answerId, $data, $alternativeRow)
    {
        DbTable_AnswerVerificador::getInstance()->getAdapter()->beginTransaction();
        try {
            $qstnId = $data['qstn_id'];
            $aaresult_value = $data['aaresult_value'];
            
            $answerRow = $this->getAnswerById($answerId);

            $alternativeIdOld = $answerRow->getAlternativeId();
            
 
			
            $data = $this->_filterInputAnswer($data)->getUnescaped();
            $data['aaresult_value'] = $aaresult_value;
            $answerRow
                ->setAlternativeId($data['alternative_id'])
                ->setAnswerValue($data['answer_value'])
                ->setStartTime($data['start_time'])
                ->setEndTime($data['end_time'])
                ->setAnswerDate($data['answer_date']);
            $answerRow->save();

            $AnswerHistory = new Model_AnswerHistoryVerificador();
            $AnswerHistory->createAnswerHistory(array(
                'user_id' => $data['logged_user_id'],
                'answer_id' => $answerRow->getId(),
                'alternative_id' => $answerRow->getAlternativeId(),
                'answer_value' => $answerRow->getAnswerValue(),
                'start_time' => $answerRow->getStartTime(),
                'end_time' => $answerRow->getEndTime(),
                'answer_date' => $answerRow->getAnswerDate(),
				'enterprise_id' => $answerRow->getEnterpriseId()
            ));
            
            $this->deleteOldAnswerAnnualResult($answerRow, $alternativeIdOld);
        
            if ($alternativeRow->getAlternativeTypeId() == Model_AlternativeType::RESULT_ACTION) {
                $this->saveAnswerAnnualResult($answerRow, $data, $alternativeIdOld);
            }
            
            $userLogged = Zend_Auth::getInstance()->getIdentity();
            $permissionEvaluationOfResponse = $this->Acl->isAllowed(
            $userLogged->getRole(), 'management:questionnaire', 'evaluation-of-response'
            );
            
            $Execution = new Model_Execution();
            $Execution->cleanDevolutive($qstnId, $data['user_id'], $permissionEvaluationOfResponse);

            DbTable_AnswerVerificador::getInstance()->getAdapter()->commit();
            return array(
                'status' => true, 'row' => $answerRow
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnswerVerificador::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerVerificador::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public function deleteOldAnswerAnnualResult($answerRow, $alternativeIdOld)
    {
        $answerAnnualResult = DbTable_Question::getInstance()
             ->getAnswerAnnualResult($answerRow->getId(), $alternativeIdOld);

        foreach ($answerAnnualResult AS $answerAnnualResultRow) {
            if ($answerAnnualResultRow->getAnswerAnnualResultId()) {
                DbTable_AnswerAnnualResult::getInstance()
                   ->delete(array('Id = ?' => $answerAnnualResultRow->getAnswerAnnualResultId()));
            }
        }
    }

    public function saveAnswerAnnualResult($answerRow, $data)
    {
        $annualResultData = $this->AnnualResultData
            ->getAllAnnualResultDataByAlternativeId($answerRow->getAlternativeId());

        foreach ($annualResultData AS $chave => $annualResultDataRow) {
            DbTable_AnswerAnnualResult::getInstance()->createRow()
               ->setAnnualResultId($annualResultDataRow->getAnnualResultId())
               ->setAnnualResultDataId($annualResultDataRow->getId())
               ->setAnswerId($answerRow->getId())
               ->setValue($data['aaresult_value'][$chave])
               ->save();
        }
    }
    
    protected function _filterInputAnswer($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'answer_value' => array(
                    array('Alnum', array('allowwhitespace' => true))
                )
            ),
            array( //validates
                'alternative_id' => array('NotEmpty'),
                'answer_value' => array('allowEmpty' => true),
                'start_time' => array('allowEmpty' => true),
                'end_time' => array('allowEmpty' => true),
                'answer_date' => array('allowEmpty'=> true),
                'user_id' => array('NotEmpty'),
                'logged_user_id' => array('NotEmpty'),
				'enterprise_id' => array('NotEmpty')
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

    public function deleteAnswer($answerRow)
    {   
        DbTable_AnswerVerificador::getInstance()->getAdapter()->beginTransaction();
        try {
            $answerRow->delete();

            DbTable_AnswerVerificador::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnswerVerificador::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerVerificador::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getAll()
    {
        return $this->tbAnswerVerificador->fetchAll();
    }

    function getAnswerById($Id)
    {
        $objResultAnswer = $this->tbAnswerVerificador->fetchRow(array('Id = ?' => $Id));
        return $objResultAnswer;
    }
    
    public function hasChange($answerId, $newAnswer, $alternativeRow) 
    {
        DbTable_Alternative::getInstance();
        $oldAnswerRow = $this->getAnswerById($answerId);
            
        $arrNewAnswer['alternative_id'] = $newAnswer['alternative_id'];
        $arrNewAnswer['answer_value'] = $newAnswer['answer_value'];
		
		$arrOldAnswer = array();
		
		


		if($oldAnswerRow !=null){
		 
			$arrOldAnswer['alternative_id'] = $oldAnswerRow->getAlternativeId();
			$arrOldAnswer['answer_value'] =  $oldAnswerRow->getAnswerValue();
		}else{
		 
			$arrOldAnswer = $arrNewAnswer;
		}
  
        if ($alternativeRow->getAlternativeTypeId() == Model_AlternativeType::RESULT_ACTION &&
                $newAnswer['alternative_id'] == $arrOldAnswer['alternative_id']) {
            if ($this->hasChangeAnswerAnnualResult($answerId, $newAnswer, $alternativeRow)) {
                return true;
            }
        } 
		
        return $arrNewAnswer == $arrOldAnswer ? false : true;
    }
    
    public function hasChangeAnswerAnnualResult($answerId, $newAnswer, $alternativeRow) {
        
        $oldAnswerAnnualResult = DbTable_Question::getInstance()
            ->getAnswerAnnualResult($answerId, $alternativeRow->getId());
        
        $oldRegs = array();
        foreach ($oldAnswerAnnualResult AS $oldAnswerAnnualResultRow) {
            $oldRegs[] = $oldAnswerAnnualResultRow->getValue();
        }
        
        $newRegs = array();
        foreach ($newAnswer['aaresult_value'] AS $newAnswerAnnualResultRow) {
            $newRegs[] = $newAnswerAnnualResultRow;
        }
        
        return $oldRegs == $newRegs ? false : true;        
    }
    
    public function filterAnswerForm($parameters)
    {
        $filters = array(
                '*' => 'StripTags', 
                'question_id' => 'StringTrim',
                'alternative_id' => 'StringTrim',
                //'user_id' => 'StringTrim',
                //'answer_date' => 'StringTrim',
                'start_time' => 'StringTrim',
                //'end_time' => 'StringTrim',
            );
        
        $validator = array(
                'question_id' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['AnswerFormError'].'QuestÃ£o'
                ),
                'alternative_id' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['AnswerFormError'].'Alternativa'
                ),
                /*'user_id' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['AnswerFormError'].'UsuÃ¡rio'
                ),*/
                'answer_value' => array(
                    'allowEmpty' => true
                ),
                /*'answer_date' => array(
                    'NotEmpty', 
                     new Zend_Validate_Date('dd/MM/yyyy')
                ),  */
                'start_time' => array(
                    'NotEmpty', 
                     new Zend_Validate_Date('H:i:s')
                ),
                /*'end_time' => array(
                    'NotEmpty', 
                     new Zend_Validate_Date('H:i:s')
                )*/
            );
        
        $options = array(
                'presence' => 'required'
            );
        
        $input = new Zend_Filter_Input($filters,$validator,$parameters,$options);
        
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }       
        return $input;        
    }
	
	 function getAllScore()
    {
        return $this->tbAnswerVerificador->getAllScore();
    }
	
    function getAllScoreByEnterpriseId($enterpriseId)
    {
        return $this->tbAnswerVerificador->getAllScoreByEnterpriseId($enterpriseId);
    }
}
