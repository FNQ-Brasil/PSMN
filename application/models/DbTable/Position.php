<?php

class DbTable_Position extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Position';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_dependentTables = array(
        'DbTable_User'
    );

}
