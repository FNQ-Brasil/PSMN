<?php
class DbTable_AnnualResult extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AnnualResult';
    protected $_primary = 'Id';

    protected $_referenceMap = array(
	     'AnnualResultData' => array(
                'columns' => array('AnnualResultId'),
             'refTableClass' => 'AnnualResultData',
             'refColumns' => array('Id')
         ),
        'Question' => array(
          	'columns' => 'QuestionId',
            'refTableClass' => 'Question',
            'refColumns' => 'Id'
        )
        
    );
    protected $_dependentTables = array(
        'DbTable_AnnualResultData'
    );
}