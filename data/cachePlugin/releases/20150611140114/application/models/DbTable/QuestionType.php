<?php

class DbTable_QuestionType extends Vtx_Db_Table_Abstract
{

    protected $_name = 'QuestionType';
    protected $_id = 'Id';
    protected $_sequence = true;
 
    protected $_dependentTables = array(
        'DbTable_Question'
    );



}
