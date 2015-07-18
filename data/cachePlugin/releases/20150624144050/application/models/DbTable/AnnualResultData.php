<?php

class DbTable_AnnualResultData extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AnnualResultData';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_referenceMap = array(
	     'AnnualResult' => array(
                'columns' => array('AnnualResultId'),
             'refTableClass' => 'AnnualResult',
             'refColumns' => array('Id')
         )
    );
    protected $_dependentTables = array(
    );

}
