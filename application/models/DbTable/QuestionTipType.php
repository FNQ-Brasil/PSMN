<?php

class DbTable_QuestionTipType extends Vtx_Db_Table_Abstract
{

    protected $_name = 'QuestionTipType';
    protected $_id = 'Id';
    protected $_sequence = true;
 
    protected $_dependentTables = array(
        'DbTable_QuestionTip'
    );



}
