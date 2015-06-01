<?php
class Model_QuestionType
{
    CONST ABCD_ID = 1;
    CONST YESNO_ID = 3;
    CONST AGREEDISAGREE_ID = 5;
    CONST ALWAYS_ID = 6;
    
    function getAll()
    {
        $tbQuestionType = new DbTable_QuestionType();
        return $tbQuestionType->fetchAll();
    }
}
