<?php

class DbTable_EnterpriseCategoryAwardCompetition extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EnterpriseCategoryAwardCompetition';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Enterprise' => array(
            'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        )
    );
}
