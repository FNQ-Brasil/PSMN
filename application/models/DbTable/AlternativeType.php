<?php

class DbTable_AlternativeType extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AlternativeType';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_dependentTables = array(
        'DbTable_Alternative'
    );

}
