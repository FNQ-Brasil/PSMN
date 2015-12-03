<?php
/**
 * 
 * Model_AnswerHistoryVerificador
 *
 */
class Model_AnswerHistoryVerificador
{
    private $tbAnswerHistoryVerificador = "";

    function __construct() {
        $this->tbAnswerHistoryVerificador = new DbTable_AnswerHistoryVerificador();
    }

    public function createAnswerHistory($data)
    {
        $data = $this->_filterInputAnswerHistory($data)->getUnescaped();
        $answerHistoryRow = DbTable_AnswerHistoryVerificador::getInstance()->createRow()
            ->setUserId($data['user_id'])
            ->setAnswerId($data['answer_id'])
            ->setAlternativeId($data['alternative_id'])
            ->setAnswerValue($data['answer_value'])
            ->setStartTime($data['start_time'])
            ->setEndTime($data['end_time'])
            ->setAnswerDate($data['answer_date'])    
            ->setLogDate(date('Y-m-d'))
			->setEnterpriseId($data['enterprise_id']);
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
                'end_time' => array('allowEmpty' => true),
				'enterprise_id' => array('allowEmpty' => true)
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
        DbTable_AnswerHistoryVerificador::getInstance()->getAdapter()->beginTransaction();
        try {
            $answerHistoryRow->delete();
            DbTable_AnswerHistoryVerificador::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnswerHistoryVerificador::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerHistoryVerificador::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getAll()
    {
        return $this->tbAnswerHistoryVerificador->fetchAll();
    }

    function getAnswerHistoryById($Id)
    {
        $objResultAnswerHistory = $this->tbAnswerHistoryVerificador->fetchRow(array('Id = ?' => $Id));
        return $objResultAnswerHistory;
    }

}
