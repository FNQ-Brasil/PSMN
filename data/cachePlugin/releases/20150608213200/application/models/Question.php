<?php
/**
 * 
 * Model_Question
 * @uses  
 * @author mcianci
 *
 */
class Model_Question
{

    public $dbTable_Question = "";
    
    public function __construct() {
        $this->dbTable_Question = new DbTable_Question();
    }

    /**
     * recupera ou grava os dados de uma questao no Cache
     * 
     * @param type $questionId
     * @return type
     * 
     */
    public function cacheOrModelQuestionById($questionId)
    {
        $this->cache = Zend_Registry::get('cache_FS');
  
        $nameCache = 'question_'.$questionId;
        
        $questionCache = $this->cache->load($nameCache);
        
        $origem = "--->question vem do cache---";
        
        //recupera do cache
        if ($questionCache == false) 
        {               
            $questionCache = $this->getHtmlQuestionById($questionId);
            $this->cache->save($questionCache, $nameCache);
            $origem = "--->question NAO vem do cache---";
        }
        
        //echo $origem;
        
        return $questionCache;
        
    } //end function
    
    
    public function createQuestion($data, $questionParentId = null)
    {
        if (isset($data['alternative'])) {
            unset($data['alternative']);
        }
        if (isset($data['helper'])) {
            unset($data['helper']);
        }
        if (isset($data['annualresult'])) {
            unset($data['annualresult']);
        }
        $data = $this->_filterInputQuestion($data)->getUnescaped();
        $questionRow = DbTable_Question::getInstance()->createRow()
            ->setQuestionTypeId($data['question_type_id'])
            ->setCriterionId($data['parent_id'])
            ->setParentQuestionId($questionParentId)
            ->setDesignation((DbTable_Question::getInstance()
                ->getHigherOrder($data['parent_id']) + 1))
            ->setValue($data['value'])
            ->setSupportingText(isset($data['supporting_text'])?
                $data['supporting_text'] : null
            )
            ->setVersion(isset($data['version'])?
                $data['version'] : null
            )
            ->setStatus(isset($data['status'])?
                $data['status'] : null
            );
        $questionRow->save();
        return array(
            'status' => true,
            'lastDesignation' => $questionRow->getDesignation(),
            'lastInsertId' => $questionRow->getId()
        );
    }

    public function updateQuestion($questionRow, $data)
    {
        // utilizar transaction externa.
        if (isset($data['alternative'])) {
            unset($data['alternative']);
        }
        if (isset($data['helper'])) {
            unset($data['helper']);
        }
        if (isset($data['annualresult'])) {
            unset($data['annualresult']);
        }
        $data = $this->_filterInputQuestion($data)->getUnescaped();
        $questionRow->setQuestionTypeId($data['question_type_id'])
            ->setCriterionId($data['parent_id'])
            //->setParentQuestionId($questionParentId)
            ->setDesignation($data['designation'])
            ->setValue($data['value'])
            ->setSupportingText($data['supporting_text'])
            ->setVersion($data['version'])
            ->setStatus($data['status']);
        $questionRow->save();
        return array(
            'status' => true
        );
    }

    public function createQuestionTransaction($questionData)
    {
        $QuestionTip        = new Model_QuestionTip();
        $Alternative        = new Model_Alternative();

        DbTable_Question::getInstance()->getAdapter()->beginTransaction();
        try {

            $questionParentId = isset($questionData['parent_question_id'])?
                $questionData['parent_question_id'] : null;
            $insert = $this->createQuestion($questionData,$questionParentId);
            if (!$insert['status']) {
                throw new Vtx_UserException($insert['messageError']);
            }

            if (isset($questionData['helper'])) {
                $helpersData = $questionData['helper'];
                $insertHelper = $QuestionTip
                    ->createQuestionTipsByQuestion($helpersData,$insert['lastInsertId']);
                if (!$insertHelper['status']) {
                    throw new Vtx_UserException($insertHelper['messageError']);
                }
            }
            
            $alternativeRowData = $questionData['alternative'];

            if (isset($alternativeRowData)) {
                $insertAlternative = $Alternative
                    ->createAlternativesByQuestion(
                        $alternativeRowData,
                        $insert['lastInsertId']
                    );
                
                if (!$insertAlternative['status']) {
                    throw new Vtx_UserException($insertAlternative['messageError']);
                }
                
            }

            DbTable_Question::getInstance()->getAdapter()->commit();
            return array(
                'status' => true,
                'lastInsertId' => $insert['lastInsertId'],
                'lastDesignation' => $insert['lastDesignation']
            );
            
        } catch (Vtx_UserException $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function updateQuestionTransaction($questionRow, $questionRowData)
    {
        $QuestionTip        = new Model_QuestionTip();
        $Alternative        = new Model_Alternative();
        DbTable_Question::getInstance()->getAdapter()->beginTransaction();
        try {

            $updateQuestion = $this->updateQuestion($questionRow, $questionRowData);
            if (!$updateQuestion['status']) {
                throw new Vtx_UserException($updateQuestion['messageError']);
            }
            
            $questionId = $questionRow->getId();
            $questionIsAnswered = $this->dbTable_Question->isAnswered($questionId);
            
            // Logica da "Ajuda da Questão"
            $QuestionTip->deleteAllByQuestionId($questionId);
            if (isset($questionRowData['helper'])) {
                $helpersData = $questionRowData['helper'];
                $insertHelper = $QuestionTip
                    ->createQuestionTipsByQuestion($helpersData,$questionId);
                if (!$insertHelper['status']) {
                    throw new Vtx_UserException($insertHelper['messageError']);
                }
            }

            // Logica das "Alternativas"
            if ($questionIsAnswered) {
                if ($questionRow->getQuestionTypeId() != $questionRowData['question_type_id']) {
                    $errorQuestionType['messageError'] = 'Não é possível alterar o tipo da questão, pois há respostas.';
                    throw new Vtx_UserException($errorQuestionType['messageError']);
                }
            }
            
            if (isset($questionRowData['alternative'])) {
                
                $updateAlternative = $Alternative
                    ->updateAlternativesByQuestion($questionRowData['alternative'],$questionId);
                if (!$updateAlternative['status']) {
                    throw new Vtx_UserException($updateAlternative['messageError']);
                }
                
            }
        
            DbTable_Question::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    /**
     * Verifica se um determinada Empresa respondeu uma questao
     * 
     * @param type $questionId
     * @param type $userId
     * @return type
     */
    public function isAnsweredByEnterprise($questionId,$userId)
    {
        try {
            
            $answer = DbTable_Question::getInstance()->isAnsweredByUserId($questionId,$userId);
            if ($answer > 1) {
                return array(
                    'status' => false, 'messageError' => 'Questão atual possui mais de uma resposta.'
                );
            }
            elseif ($answer == 0) {
                return array(
                    'status' => false, 'messageError' => 'Questão atual não possui resposta.'
                );
            }
            return array(
                'status' => true,
                'objAnswered' => DbTable_Question::getInstance()->getAnswer($questionId,$userId)
            );
            
        } catch (Vtx_UserException $e) {
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } 
    }

    protected function _filterInputQuestion($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'question_type_id' => array(
                    array('Alnum', array('allowwhitespace' => true))
                ),
                'parent_id' => array(
                    array('Alnum', array('allowwhitespace' => true))
                ),
                //'question_parent_question_id' => array( ),
                'designation' => array(
                    array('Alnum', array('allowwhitespace' => true))
                ),
                'supporting_text' => array(
                ),
                'value' => array(
                ),
                'version' => array(),
                'status' => array(
                    array('Alnum', array('allowwhitespace' => true))
                ),
            ),
            array( //validates
                'question_type_id' => array('NotEmpty'),
                'parent_id' => array('NotEmpty'),
                //'question_parent_question_id' => array(),
                'designation' => array('NotEmpty'),
                'supporting_text' => array('allowEmpty' => true),
                'value' => array('NotEmpty', 'messages' => array('O nome da questão não pode ser vazia.')),
                'version' => array(),
                'status' => array('NotEmpty'),
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

    public function deleteQuestion($questionRow)
    {   
        
        DbTable_Question::getInstance()->getAdapter()->beginTransaction();
        try {

            // verifica se ha respostas para a questao
            $objIsAnswered = $this->dbTable_Question->isAnswered($questionRow->getId());
            
            if ($objIsAnswered) {
                return array(
                    'status' => false,
                    'messageError' => 'Há respostas para esta questão.'
                );
            }
            // verifica se ha alternativas
            /*
            $query = DbTable_Alternative::getInstance()->select()
                ->from(
                    array('a' => 'Alternative'),
                    array('Question' => 'a.QuestionId')
                )
                ->where('a.QuestionId = ?', $questionRow->getId());
            $objResultAlternative = DbTable_Alternative::getInstance()->fetchRow($query);
            
            if ($objResultAlternative) {
                return array(
                    'status' => false,
                    'messageError' => 'Há alternativas nesta questão.'
                );
            }
            */

            /* Deleta todos os resultados anuais da questão */
            $whereDeleteAnnualResult = array('QuestionId = ?' => $questionRow->getId());
            DbTable_AnnualResult::getInstance()->delete($whereDeleteAnnualResult);
            
            /* Deleta todos as alternativas da questão */
            $whereDeleteAlternative = array('QuestionId = ?' => $questionRow->getId());
            DbTable_Alternative::getInstance()->delete($whereDeleteAlternative);
             
            /* Deleta todos as ajudas da questão */
            $whereDeleteQuestionTip = array('QuestionId = ?' => $questionRow->getId());
            DbTable_QuestionTip::getInstance()->delete($whereDeleteQuestionTip);
            
            $criterionId = $questionRow->getCriterionId();
            
            $questionRow->delete();
                DbTable_Question::getInstance()->getAdapter()->commit();
                DbTable_Question::getInstance()->reorder($criterionId);
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    /**
     * 
     * 
     * 
     * @param type $questionId
     * @param type $userId
     * @return type
     */
    public function getQuestionAnswer($questionId, $userId)
    {
        $answerRow = DbTable_Question::getInstance()->getAnswer($questionId,$userId);
        $answerResult = array();
        
        //caso tenha retornado 1 linha, com alternativa assinalada para a questao, entao ...
        if ($answerRow) {
            //$answerRow->toArray();
            isset($answerRow['AlternativeId']) ? $answerResult['alternative_id'] = $answerRow['AlternativeId'] : "";
            isset($answerRow['AnswerValue']) ? $answerResult['answer_value'] = $answerRow['AnswerValue'] : "";
            isset($answerRow['StartTime']) ? $answerResult['start_time'] = $answerRow['StartTime'] : "";
            isset($answerRow['EndTime']) ? $answerResult['end_time'] = $answerRow['EndTime'] : "";
            isset($answerRow['AnswerDate']) ? $answerResult['answer_date'] = $answerRow['AnswerDate'] : "";
            isset($answerRow['AnswerId']) ? $answerResult['AnswerIdValue'] = $answerRow['AnswerId'] : "";
            
            $answerResult['annual_result'] = array();
            if (isset($answerRow['AlternativeTypeId']) && $answerRow['AlternativeTypeId'] == '3') {
                $objAAR = $this->getAnswerAnnualResult($answerRow['AnswerId'], $answerRow['AlternativeId']);
                $arrAAR = array();
                foreach ($objAAR AS $tmpAAR) {
                    $arrAAR[$tmpAAR->getYear()] = $tmpAAR->getValue();
                    $answerResult['annual_result_unit'] = $tmpAAR->getMask();
                }
                $answerResult['annual_result'] = $arrAAR;
            }
        }
        
        return $answerResult;
    }
    
    public function getAnswerAnnualResult($answerId, $alternativeId)
    {
        $answerAnnualResultRows = DbTable_Question::getInstance()->getAnswerAnnualResult($answerId, $alternativeId);
        
        return $answerAnnualResultRows;
    }

    public function moveQuestion($fromId, $toId)
    {
        DbTable_Question::getInstance()->getAdapter()->beginTransaction();
        try {
            $questionNewPositionRowData = $this->getQuestionById($toId);
            
            $fromOldPosition    = $this->getPositionByQuestionId($fromId);
            $newDesignation     = $questionNewPositionRowData->getDesignation();
            $criterionId        = $questionNewPositionRowData->getCriterionId();
            
            $questionOldPositionRowData = $this->getQuestionById($fromId);
            $questionOldPositionRowData->setDesignation($newDesignation);
            $questionOldPositionRowData->save();
            
            DbTable_Question::getInstance()->getAdapter()->commit();
            
            DbTable_Question::getInstance()->reorder($criterionId, $fromId, $fromOldPosition, $toId);
            
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Question::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

	static public function getPositionByQuestionId($questionId){
        if ( !is_numeric($questionId) ){
            return false;
        }
        $query = DbTable_Question::getInstance()->select()
            ->from(
                array('q' => 'Question'),
                array('Position' => 'q.Designation')
            )
            ->where('q.Id = ?', $questionId);
        $objResultQuestion = DbTable_Question::getInstance()->fetchRow($query);
        if ($objResultQuestion) {
            return $objResultQuestion->getPosition();
        }
	}
    
	static public function getQuestionByPosition($designation, $criterionId){
        if ( !is_numeric($designation) || !is_numeric($criterionId) ){
            return false;
        }
        $select = DbTable_Question::getInstance()->select()
            ->from(
                array('q' => 'Question'),
                array('*')
            )
            ->where('q.Designation = ?', $designation)
            ->where('q.CriterionId = ?', $criterionId);
        $objResultQuestion = DbTable_Question::getInstance()->fetchRow($select);
        if ($objResultQuestion) {
            return $objResultQuestion;
        }
    }
    
    /**
     * Recupera campo Feedback da tabela AnswerFeedback
     * ou campo Pontos Fortes do form Questionario
     * 
     * @param type $answerId
     * @return boolean
     */
    public function getAnswerFeedback($answerId) 
    {
        if (!is_numeric($answerId) ){
            return false;
        }
        
        $objResultAnswerFeedback = DbTable_AnswerFeedback::getInstance()
            ->fetchRow(array(
                'AnswerId = ?' => $answerId
            ),"FeedbackDate DESC"
        );
        if ($objResultAnswerFeedback) {
            return $objResultAnswerFeedback->getFeedback();
        }
    }

    /**
     * Recupera campo FeedbackImprove da tabela AnswerFeedbackImprove
     * ou campo 'Oportunidades de melhoria' do form Questionario
     * 
     * @param type $answerId
     * @return boolean
     */
    public function getAnswerFeedbackImprove($answerId) 
    {
        if (!is_numeric($answerId) ){
            return false;
        }
        
        $objResultAnswerFeedbackImprove = DbTable_AnswerFeedbackImprove::getInstance()
            ->fetchRow(array(
                'AnswerId = ?' => $answerId
            ),"FeedbackDate DESC"
        );
        if ($objResultAnswerFeedbackImprove) {
            return $objResultAnswerFeedbackImprove->getFeedbackImprove();
        }
    }    
    
    public function getAllByCriterionId($criterionId)
    {
        $where = null;
        $order = array('Designation ASC');
        if ($criterionId) {
            $where = array('CriterionId = ?' => $criterionId);
        }
        return $this->getAll($where, $order);
    }
    
    public function getAllByQuestionnaireId($questionnaireId)
    {
        return DbTable_Question::getInstance()
            ->getAllByQuestionnaireId($questionnaireId);
    }
    /**
     * recupera dados Questionario/Bloco/Questao
     * 
     * @param int $questionnaireId
     * @param int $blockId
     * @return array fetchall
     */
    public function getAllByQuestionnaireIdBlockId($questionnaireId,$blockId)
    {
        return DbTable_Question::getInstance()
            ->getAllByQuestionnaireIdBlockId($questionnaireId,$blockId);
    }
    
    public function getQuestionById($Id)
    {
        return $this->dbTable_Question->fetchRow(array('Id = ?' => $Id));
    }
    
    public function getHtmlQuestionById($Id)
    {
        $Glossary = new Model_Glossary();

        $question = $this->getQuestionById($Id);
        $questionValue = $Glossary->getHtmlWord($question->getValue());
        $questionSupporting = $Glossary->getHtmlWord($question->getSupportingText());
        $questionTypeId = $question->getQuestionTypeId();

        return array(
            'I' => $question->getId(), 'V' => $questionValue, 'S' => $questionSupporting,
            'T' => $questionTypeId, 'Q' => $question->getValue()
        );
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_Question->fetchAll($where, $order, $count, $offset);
    }
}