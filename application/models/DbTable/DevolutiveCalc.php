<?php

class DbTable_DevolutiveCalc extends Vtx_Db_Table_Abstract
{
    protected $_name = 'DevolutiveCalc';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_dependentTables = array(
        'DbTable_Question'
    );
}