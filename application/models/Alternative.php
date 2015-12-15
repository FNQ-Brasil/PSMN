<?php
/**
 * 
 * Model_Alternative
 * @uses  
 * @author mcianci
 *
 */
class Model_Alternative
{
 
    public $tbAlternative = "";
    
    public function __construct()
    {
        $this->tbAlternative = new DbTable_Alternative();
    }

    public function createAlternative($data, $ParentAlternativeId = null) 
    {
        $data = $this->_filterInputAlternative($data)->getUnescaped();
        $alternativeRow = DbTable_Alternative::getInstance()->createRow()
            ->setAlternativeTypeId(isset($data['alternative_type_id'])?
                    $data['alternative_type_id'] : null
            )
            ->setQuestionId($data['question_id'])
            ->setParentAlternativeId($ParentAlternativeId)
            ->setDesignation($data['designation'])
            ->setValue($data['value'])
            ->setVersion($data['version'])
            ->setStatus($data['status'])
            ->setScoreLevel($data['score_level'])
            ->setFeedbackDefault($data['feedback_default'])
            ->setDialogueDescription($data['dialogue_description']);
        $alternativeRow->save();
        return array(
            'status' => true,
            'lastInsertId' => $alternativeRow->getId()
        );
    }

    public function createAlternativesByQuestion($alternativesData, $questionId) 
    {
        $count_alternatives = count($alternativesData['alternative_type_id']);
        $alternativesArray = array();
        for ( $i = 0 ; $i < $count_alternatives ; $i++ ) {

            $alternativeRow['question_id'] = $alternativesData['question_id'][$i] = $questionId;
            $alternativeRow['value'] = $alternativesData['value'][$i];
            $alternativeRow['alternative_type_id'] = $alternativesData['alternative_type_id'][$i];
            $alternativeRow['dialogue_description'] = isset($alternativesData['dialogue_description'][$i])? $alternativesData['dialogue_description'][$i] : null;
            $alternativeParentId = null;
            $alternativeRow['designation'] = $i + 1;
            $alternativeRow['version'] = isset($alternativesData['version'][$i])? $alternativesData['version'][$i] : 1;
            $alternativeRow['status'] = isset($alternativesData['status'][$i])? $alternativesData['status'][$i] : 'A';
            $alternativeRow['score_level'] = isset($alternativesData['score_level'][$i])? $alternativesData['score_level'][$i] : null;
            $alternativeRow['feedback_default'] = isset($alternativesData['feedback_default'][$i])? $alternativesData['feedback_default'][$i] : null;
                
            $insertAlternative = $this->createAlternative($alternativeRow,$alternativeParentId);
            $alternativesArray['alternative_id'][] = $insertAlternative['lastInsertId'];
            $alternativesArray['alternative_type_id'][] = $alternativeRow['alternative_type_id'];
            
            if (!$insertAlternative['status']) {
                return array(
                    'status' => false,
                    'messageError' => $insertAlternative['messageError']
                );
            }
        }
        return array(
            'status' => true,
            'alternatives' => $alternativesArray
        );
    }
    
    public function updateAlternativesByQuestion($alternativesData, $questionId) 
    {
        // utilizar transaction externa.
        $alternativesArray = array();
        $oldAlternatives = $this->getAllByQuestionId($questionId, false, 'object');
        foreach ($oldAlternatives as $key => $alternative) {
            $data['question_id']=$questionId;
            $data['designation']= $key+1;
            $data['alternative_type_id']=$alternativesData['alternative_type_id'][$key];
            $data['value']=$alternativesData['value'][$key];
            $data['dialogue_description']=$alternativesData['dialogue_description'][$key];
            $data['feedback_default']=$alternativesData['feedback_default'][$key];
            $data['score_level']=$alternativesData['score_level'][$key];
            $data['version']=$alternative['Version'];
            $data['status']=$alternative['Status'];
            
            $data = $this->_filterInputAlternative($data)->getUnescaped();
            $alternativesArray['alternative_id'][] = $alternative['Id'];
            $alternativesArray['alternative_type_id'][] = $data['alternative_type_id'];
            $alternative->setValue($data['value']);
            $alternative->setDesignation($data['designation']);
            $alternative->setAlternativeTypeId($data['alternative_type_id']);
            $alternative->setDialogueDescription($data['dialogue_description']);
            $alternative->setFeedbackDefault($data['feedback_default']);
            $alternative->setScoreLevel($data['score_level']);
            
            $alternative->save();
        }
        
        return array(
            'status' => true,
            'alternatives' => $alternativesArray
        );
    }
    
    
    public function updateAlternative($alternativeRow, $data) {
        DbTable_Alternative::getInstance()->getAdapter()->beginTransaction();
        try {
            $data = $this->_filterInputAlternative($data)->getUnescaped();
            $alternativeRow
                ->setAlternativeTypeId($data['alternative_type_id'])
                ->setQuestionId($data['question_id'])
                //->setParentAlternativeId($data['parent_alternative_id'])
                ->setDesignation($data['designation'])
                ->setValue($data['value'])
                ->setVersion($data['version'])
                ->setStatus($data['status'])
                ->setScoreLevel($data['score_level'])
                ->setFeedbackDefault($data['feedback_default'])
                ->setDialogueDescription($data['dialogue_description']);
            $alternativeRow->save();
            //self::clearAlternativeCache();
            DbTable_Alternative::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Alternative::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Alternative::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public function updateScoreLevel($alternativeRow, $ScoreLevel) {
        DbTable_Alternative::getInstance()->getAdapter()->beginTransaction();
        try {
            $alternativeRow
                ->setScoreLevel($ScoreLevel);
            $alternativeRow->save();
            DbTable_Alternative::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Alternative::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Alternative::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    protected function _filterInputAlternative($params) {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'designation' => array(
                    array('Alnum', array('allowwhitespace' => true))
                ),
                'value' => array(),
                'feedback_default' => array(),
                'dialogue_description' => array()
            ),
            array( //validates
                'alternative_type_id' => array('allowEmpty' => true),
                'question_id' => array('NotEmpty'),
                //'parent_alternative_id' => array(),
                'designation' => array('NotEmpty'),
                'value' => array('NotEmpty', 'messages' => array('O nome da alternativa não pode ser vazio.')),
                'version' => array(),
                'status' => array(),
                'score_level' => array('allowEmpty' => true),
                'feedback_default' => array('allowEmpty'=> true),
                'dialogue_description' => array('allowEmpty'=> true),
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

    public function deleteAlternative($alternativeRow) {   
        DbTable_Alternative::getInstance()->getAdapter()->beginTransaction();
        try {
            /* Deletar : 
             * 'AlternativeHistory',
             * 'Answer',
             * 'AnswerHistory'
             */
            /*
            $whereDeleteAlternativeHistory = array('AlternativeId = ?' => $alternativeRow->getId());
            DbTable_AlternativeHistory::getInstance()->delete($whereDeleteAlternativeHistory);
            */
            
            $alternativeRow->delete();

            DbTable_Alternative::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Alternative::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Alternative::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function deleteAllAlternativeByQuestionId($QuestionId) 
    {   
        /* Deletar : 
         * 'AlternativeHistory',
         * 'Answer',
         * 'AnswerHistory'
         */

        $whereDeleteAlternative = array('QuestionId = ?' => $QuestionId);
        DbTable_Alternative::getInstance()->delete($whereDeleteAlternative);
        
        return array(
            'status' => true
        );
    }
    
    public function getAll() {
        $tbAlternative = new DbTable_Alternative();
        return $tbAlternative->fetchAll();
    }

    public function getAlternativeById($Id) {
        $tbAlternative = new DbTable_Alternative();
        $objResultAlternative = $tbAlternative->fetchRow(array('Id = ?' => $Id));
        return $objResultAlternative;
    }
    
    public function getAlternativeByQuestionIdDesignation($QuestionId,$Designation) {
        $tbAlternative = new DbTable_Alternative();
        $objResultAlternative = $tbAlternative->fetchRow(array('QuestionId = ?' => $QuestionId, 'Designation = ?' => $Designation));
        return $objResultAlternative;
    }
    
    /**
     * 
     * retorna objeto com dados das respostas da QuestaoId
     * 
     * @param type $QuestionId
     * @param type $toArrayWithAnnualResult -- OFF
     * @param type $modeEditShowAllQuestions
     * @return type
     */
    public function getAllByQuestionId($QuestionId, $modeEditShowAllQuestions = false, $returnType = 'array')
    {
        $tbAlternative = new DbTable_Alternative();
        $questionType = DbTable_Question::getInstance()->fetchRow(array('Id = ?' => $QuestionId))->getQuestionTypeId();
        
        if (!$modeEditShowAllQuestions and $questionType == Model_QuestionType::YESNO_ID) {
            $where = array('QuestionId = ?' => $QuestionId, 'Designation <= 3');
        } elseif (!$modeEditShowAllQuestions and $questionType == Model_QuestionType::ABCD_ID) {
            $where = array('QuestionId = ?' => $QuestionId, 'Designation <= 4');
        } elseif (!$modeEditShowAllQuestions and $questionType == Model_QuestionType::ALWAYS_ID) {
            $where = array('QuestionId = ?' => $QuestionId, 'Designation <= 3');
        } else {
            $where = array('QuestionId = ?' => $QuestionId);
        }
        
        if ($returnType == 'array') {
            return $tbAlternative->fetchAll($where)->toArray();
        } 
        
        
        return $tbAlternative->fetchAll($where);
        /*
        if ($toArrayWithAnnualResult) {
            $AnnualResult = new Model_AnnualResult();
            $AnnualResultData = new Model_AnnualResultData();
            $getAllAlternative = $objResultAlternative->toArray();
            $getAllAnnualResult = $AnnualResult->getAllByQuestionId($QuestionId)->toArray();
            $countGetAlternative = count($getAllAlternative);
            $countAllAnnualResult = count($getAllAnnualResult);
            for ($l = 0; $l < $countGetAlternative; $l++) {
                for ($c = 0; $c < $countAllAnnualResult; $c++) {
                    if ($getAllAlternative[$l]['Id'] == $getAllAnnualResult[$c]['AlternativeId']) {
                        $getAllAlternative[$l]['AnnualResult'] = $getAllAnnualResult[$c];
                        $getAllAlternative[$l]['AnnualResult']['AnnualResultData'] = 
                            $AnnualResultData
                                ->getAllAnnualResultDataByAlternativeId($getAllAlternative[$l]['Id'])
                                ->toArray();
                    }                
                }
            }
            return $getAllAlternative;
        }
        */
        //return $objResultAlternative;
    }
    
    function isQuestionAlternative($AlternativeId, $QuestionId)
    {
        $alternativeRow = $this->getAlternativeById($AlternativeId);
        return ($alternativeRow and $alternativeRow->getQuestionId() == $QuestionId) ?
            $alternativeRow : false;
    }

    public static function clearAlternativeCache()
    {
        $cache = Zend_Registry::get('cache_alternative');
        $cache->clean();
        Zend_Registry::getInstance()->set('alternative', new Model_Alternative(true));
        return $cache;
    }

    public function mustWriteValue($alternativeId)
    {
        return ($this->getAlternativeById($alternativeId)
            ->getAlternativeTypeId() == Model_AlternativeType::TEXT_ACTION) ? true : false;
    }
}
