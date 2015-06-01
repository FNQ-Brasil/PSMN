<?php

class DbTable_ContactType extends Vtx_Db_Table_Abstract
{

    protected $_name = 'ContactType';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_dependentTables = array(
        'DbTable_Contact'
    );

}
