<?php
/**
 * 
 * Model_QuestionTip
 * @uses  
 * @author mcianci
 *
 */
class Model_QuestionTip
{
    public $dbTableQuestionTip = "";
    public $modelQuestionTipType = "";
    
    public function __construct() {
        $this->dbTableQuestionTip = new DbTable_QuestionTip();
        $this->modelQuestionTipType = new Model_QuestionTipType();
    }
    
    public function createQuestionTip($data)
    {
        // utilizar transaction externa.
        $data = $this->_filterInputQuestionTip($data)->getUnescaped();
        $questionTipRow = DbTable_QuestionTip::getInstance()->createRow()
            ->setQuestionId($data['question_id'])
            ->setQuestionTipTypeId($data['question_tip_type_id'])
            ->setValue($data['value']);
        $questionTipRow->save();
        return array(
            'status' => true
        );
    }

    public function updateQuestionTip($questionTipRow, $data)
    {
        DbTable_QuestionTip::getInstance()->getAdapter()->beginTransaction();

        try {
            $data = $this->_filterInputQuestionTip($data)->getUnescaped();
            
            $questionTipRow->setQuestionId($data['question_id'])
                           ->setQuestionTipTypeId($data['question_tip_type_id'])
                           ->setValue($data['value']);
            $questionTipRow->save();

            DbTable_QuestionTip::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_QuestionTip::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_QuestionTip::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function createQuestionTipsByQuestion($helpersData, $questionId)
    {
        // utilizar transaction externa.
        $count_helpers = count($helpersData['helper_type']);
        for ($i = 0 ; $i < $count_helpers ; $i++) {
            $questionTipRow = array();
            $questionTipRow['question_id'] = $alternativesData['question_id'][$i] = $questionId;
            $questionTipRow['value'] = $helpersData['helper_value'][$i];
            $questionTipRow['question_tip_type_id'] = $this->modelQuestionTipType->getQuestionTipTypeIdByTitle($helpersData['helper_type'][$i]);
            
            $insertQuestionTip = $this->createQuestionTip($questionTipRow);
            if (!$insertQuestionTip['status']) {
                return $insertQuestionTip['messageError'];
            }
        }
        return array(
            'status' => true
        );
    }

    public function updateQuestionTipsByQuestion($helpersData, $questionId)
    {
        $oldQuestionTips = $this->getAllByQuestionId($questionId); // 4
        $count_helpers = count($helpersData['helper_type']); // 10
        $count_oldTips = count($oldQuestionTips); // 10
        foreach($oldQuestionTips as $key => $questionTip) {
            $questionTip->setValue($helpersData['helper_value'][$key]);
            $questionTip->setQuestionTipTypeId($this->modelQuestionTipType->getQuestionTipTypeIdByTitle($helpersData['helper_type'][$key]));
            $questionTip->save();
        }
        //      4 ________________ 4 < 10 ______________ 5 ____ passa 6x
        for ($i = $count_oldTips ; $i < $count_helpers ; $i++) {
            $questionTipRow = array();
            $questionTipRow['question_id'] = $alternativesData['question_id'][$i] = $questionId;
            $questionTipRow['value'] = $helpersData['helper_value'][$i];
            $questionTipRow['question_tip_type_id'] = $this->modelQuestionTipType->getQuestionTipTypeIdByTitle($helpersData['helper_type'][$i]);
            
            $insertQuestionTip = $this->createQuestionTip($questionTipRow);
            if (!$insertQuestionTip['status']) {
                return $insertQuestionTip['messageError'];
            }
        }
        return array(
            'status' => true
        );
    }

    protected function _filterInputQuestionTip($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags','StringTrim'),
                'question_id' => array(
                    array('Alnum', 
                        array('allowwhitespace' => false)
                        )
                ),
                'question_tip_type_id' => array(
                    array('Alnum', 
                        array('allowwhitespace' => false)
                        )
                ),
                'value' => array(
                ),
            ),
                        
            array( //validates
                'question_id' => array('NotEmpty'),
                'question_tip_type_id' => array('NotEmpty'),
                'value' => array('NotEmpty'),
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

    function getAll()
    {
        return $this->dbTableQuestionTip->fetchAll();
    }

    function getQuestionTipById($Id)
    {
        $objResultQuestionTip = $this->dbTableQuestionTip->fetchRow(array('Id = ?' => $Id));
        return $objResultQuestionTip;
    }
    
    function getAllByQuestionId($questionId)
    {
        return $this->dbTableQuestionTip->getAllByQuestionId($questionId);
    }

    public function deleteAllByQuestionId($questionId)
    {   
        // utilizar transaction externa.
        $whereDeleteQuestionTip = array('QuestionId = ?' => $questionId);
        DbTable_QuestionTip::getInstance()->delete($whereDeleteQuestionTip);
        return array(
            'status' => true
        );
    }
}