<?php

class DbTable_ResponsabilityType extends Vtx_Db_Table_Abstract
{

    protected $_name = 'ResponsabilityType';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_dependentTables = array(
        'DbTable_Responsability'
    );

}
