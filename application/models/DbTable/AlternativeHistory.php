<?php

class DbTable_AlternativeHistory extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AlternativeHistory';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Alternative' => array(
          	'columns' => 'AlternativeId',
            'refTableClass' => 'Alternative',
            'refColumns' => 'Id'
        ),
    ); 
}
