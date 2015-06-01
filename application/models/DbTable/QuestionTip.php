<?php

class DbTable_QuestionTip extends Vtx_Db_Table_Abstract
{

    protected $_name = 'QuestionTip';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Question' => array(
          	'columns' => 'QuestionId',
            'refTableClass' => 'Question',
            'refColumns' => 'Id'
        ),
        'QuestionTipType' => array(
          	'columns' => 'QuestionTipTypeId',
            'refTableClass' => 'QuestionTipType',
            'refColumns' => 'Id'
        )
    );
    
    protected $_dependentTables = array(
        'DbTable_QuestionTipType'
    );
    
    public function getAllByQuestionId($questionId)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('qt' => $this->_name))
            ->join(
                array('qtt' => 'QuestionTipType'), 
                    'qtt.Id = qt.QuestionTipTypeId', 
                array('TipTypeTitle' => 'qtt.Title')
            )
            ->where('qt.QuestionId = ?', $questionId);
        return $this->fetchAll($select);
    }
}
