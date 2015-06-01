<?php
class Model_AlternativeType
{

    CONST NO_ACTION     = 1;
    CONST TEXT_ACTION   = 2;
    CONST RESULT_ACTION = 3;

    function getAll()
    {
        $tbAlternativeType = new DbTable_AlternativeType();
        return $tbAlternativeType->fetchAll();
    }
    
    function getOne($Id)
    {
        $tbAlternativeType = new DbTable_AlternativeType();
        $objResultAlternativeType = $tbAlternativeType->fetchRow("Id = $Id");
        return $objResultAlternativeType;
    }
    
}
