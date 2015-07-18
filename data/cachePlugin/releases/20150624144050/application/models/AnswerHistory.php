<?php
/**
 * 
 * Model_AnswerHistory
 *
 */
class Model_AnswerHistory
{
    private $tbAnswerHistory = "";

    function __construct() {
        $this->tbAnswerHistory = new DbTable_AnswerHistory();
    }

    public function createAnswerHistory($data)
    {
        $data = $this->_filterInputAnswerHistory($data)->getUnescaped();
        $answerHistoryRow = DbTable_AnswerHistory::getInstance()->createRow()
            ->setUserId($data['user_id'])
            ->setAnswerId($data['answer_id'])
            ->setAlternativeId($data['alternative_id'])
            ->setAnswerValue($data['answer_value'])
            ->setStartTime($data['start_time'])
            ->setEndTime($data['end_time'])
            ->setAnswerDate($data['answer_date'])    
            ->setLogDate(date('Y-m-d'));
        $answerHistoryRow->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputAnswerHistory($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'answer_value' => array(
                    array('Alnum', 
                        array('allowwhitespace' => true)
                        )
                )
            ),
            array( //validates
                'user_id' => array('NotEmpty'),
                'answer_id' => array('NotEmpty'),
                'alternative_id' => array('NotEmpty'),
                'answer_value' => array('allowEmpty' => true),
                'start_time' => array('allowEmpty' => true),
                'answer_date' => array('allowEmpty' => true),
                'end_time' => array('allowEmpty' => true)
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

    public function deleteAnswerHistory($answerHistoryRow)
    {   
        DbTable_AnswerHistory::getInstance()->getAdapter()->beginTransaction();
        try {
            $answerHistoryRow->delete();
            DbTable_AnswerHistory::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnswerHistory::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerHistory::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getAll()
    {
        return $this->tbAnswerHistory->fetchAll();
    }

    function getAnswerHistoryById($Id)
    {
        $objResultAnswerHistory = $this->tbAnswerHistory->fetchRow(array('Id = ?' => $Id));
        return $objResultAnswerHistory;
    }

}