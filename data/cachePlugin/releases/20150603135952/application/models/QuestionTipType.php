<?php
/**
 * 
 * Model_QuestionTipType
 * @uses  
 * @author mcianci
 *
 */
class Model_QuestionTipType
{
    public $dbTableQuestionTip = "";

    function __construct() {
        $this->dbTableQuestionTipType = new DbTable_QuestionTipType();
    }

    function getQuestionTipTypeIdByTitle($title) {
        $objQuestionTipType = $this->dbTableQuestionTipType->fetchRow(array('Title = ?' => $title));
        return $objQuestionTipType->getId();
    }

    function getAll()
    {
        return $this->dbTableQuestionTipType->fetchAll();
    }
}