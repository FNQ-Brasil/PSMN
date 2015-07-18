<?php
class DbTable_AnswerLog extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AnswerLog';
    protected $_primary = 'Id';

    protected $_referenceMap = array(
	    'Answer'  => array(
	    	'columns'     => array('AnswerId'),
	    	'refTableClass'   => 'Answer',
	    	'refColumns'    => array('Id')
	    ),
        'Alternative'  => array(
	    	'columns'     => array('AlternativeId'),
	    	'refTableClass'   => 'Alternative',
	    	'refColumns'    => array('Id')
	    )
    );
    
}