<?php

class DbTable_AddressPresident extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AddressPresident';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Address' => array(
            'columns' => 'AddressId',
            'refTableClass' => 'Address',
            'refColumns' => 'Id'
        ),
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
        ),
        'President' => array(
            'columns' => 'PresidentId',
            'refTableClass' => 'President',
            'refColumns' => 'Id'
        )
    );
}
