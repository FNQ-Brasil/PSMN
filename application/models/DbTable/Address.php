<?php

class DbTable_Address extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Address';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'State' => array(
            'columns' => 'StateId',
            'refTableClass' => 'State',
            'refColumns' => 'Id'
        ),
        'City' => array(
            'columns' => 'CityId',
            'refTableClass' => 'City',
            'refColumns' => 'Id'
        ),
        'Neighborhood' => array(
            'columns' => 'NeighborhoodId',
            'refTableClass' => 'Neighborhood',
            'refColumns' => 'Id'
        )
    );
}
