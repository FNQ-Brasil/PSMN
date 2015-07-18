<?php
class DbTable_QuestionLog extends Vtx_Db_Table_Abstract
{
    protected $_name = 'QuestionLog';
    protected $_primary = 'Id';

    protected $_referenceMap = array(
	    'Question'  => array(
	    	'columns'     => array('QuestionId'),
	    	'refTableClass'   => 'Question',
	    	'refColumns'    => array('Id')
	    )
    );
    
}
