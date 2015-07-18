<?php
/**
 * 
 * Model_AnswerFeedback
 * @uses  
 *
 */
class Model_AnswerFeedback
{
    private $tbAnswerFeedback = "";

    function __construct() {
        $this->tbAnswerFeedback = new DbTable_AnswerFeedback();
    }

    public function createAnswerFeedback($data)
    {
        DbTable_AnswerFeedback::getInstance()->getAdapter()->beginTransaction();
        try {
            if (!trim($data['feedback'])) {
                return;
            }
            $data = $this->_filterInputAnswerFeedback($data)->getUnescaped();

            //Verifica se já existe alguma avaliação
            $feedbackRow = DbTable_AnswerFeedback::getInstance()
                ->fetchRow(array('AnswerId = ?' => $data['answer_id']),"FeedbackDate DESC");

            //Se existe, verifica se o conteúdo é igual a avaliação mais atual no banco
            if ($feedbackRow and $data['feedback'] == $feedbackRow->getFeedback()) {
                return array('status' => true);
            }

            $answerFeedbackRow = DbTable_AnswerFeedback::getInstance()->createRow()
                ->setUserId($data['user_id'])
                ->setAnswerId($data['answer_id'])
                ->setFeedback($data['feedback'])
                ->setFeedbackDate(new Zend_Db_Expr('NOW()'));
            $answerFeedbackRow->save();
            DbTable_AnswerFeedback::getInstance()->getAdapter()->commit();
            return array('status' => true);
        } catch (Vtx_UserException $e) {
            DbTable_AnswerFeedback::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerFeedback::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
/*
    public function updateAnswerFeedback($answerFeedbackRow, $data)
    {
        DbTable_AnswerFeedback::getInstance()->getAdapter()->beginTransaction();
        try {
            $data = $this->_filterInputAnswerFeedback($data)->getUnescaped();
            $answerFeedbackRow
                ->setUserId($data['user_id'])
                ->setAnswerId($data['answer_id'])
                ->setFeedback($data['feedback'])
                ->setFeedbackDate($data['feedback_date']);
            $answerFeedbackRow->save();
            DbTable_AnswerFeedback::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnswerFeedback::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerFeedback::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
*/
    protected function _filterInputAnswerFeedback($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                /*'feedback' => array(
                    array('Alnum', array('allowwhitespace' => true))
                )*/
            ),
            array( //validates
                'user_id' => array('NotEmpty'),
                'answer_id' => array('NotEmpty'),
                'feedback' => array('NotEmpty')
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

    public function deleteAnswerFeedback($answerFeedbackRow)
    {   
        DbTable_Answer::getInstance()->getAdapter()->beginTransaction();
        try {
            $answerFeedbackRow->delete();

            DbTable_AnswerFeedback::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnswerFeedback::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnswerFeedback::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getAll()
    {
        return $this->tbAnswerFeedback->fetchAll();
    }

    function getAnswerFeedbackById($Id)
    {
        $objResultAnswerFeedback = $this->tbAnswerFeedback->fetchRow(array('Id = ?' => $Id));
        return $objResultAnswerFeedback;
    }

}