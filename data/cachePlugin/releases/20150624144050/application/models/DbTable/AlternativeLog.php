<?php
class DbTable_AlternativeLog extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AlternativeLog';
    protected $_primary = 'Id';

    protected $_referenceMap = array(
	    'AlternativeLog'  => array(
	    	'columns'     => array('AlternativeId'),
	    	'refTableClass'   => 'Alternative',
	    	'refColumns'    => array('Id')
	    )
    );
    
}