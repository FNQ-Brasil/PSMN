<?php

class DbTable_Education extends Vtx_Db_Table_Abstract
{

    protected $_name = 'Education';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_dependentTables = array(
        'DbTable_User'
    );
}