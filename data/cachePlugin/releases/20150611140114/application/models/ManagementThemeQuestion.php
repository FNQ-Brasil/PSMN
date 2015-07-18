<?php

class Model_ManagementThemeQuestion {
    protected $table;

    public function __construct(){
        $this->table = new DbTable_ManagementThemeQuestion();
    }

    public function getAll(){
        return $this->table->getAll();
    }

    public function getByManagementTheme($managementThemeId){
        return $this->table->getByManagementTheme($managementThemeId);
    }
}

?>