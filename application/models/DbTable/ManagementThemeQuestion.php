<?php

class DbTable_ManagementThemeQuestion extends Vtx_Db_Table_Abstract {
    protected $_name = 'ManagementThemeQuestion';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getByManagementTheme($managementThemeId){
        return $this->fetchAll(array('ManagementThemeId = ?' => $managementThemeId));
    }
}

?>