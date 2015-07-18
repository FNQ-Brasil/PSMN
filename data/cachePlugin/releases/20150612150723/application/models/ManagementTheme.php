<?php

class Model_ManagementTheme {
    protected $table;

    public function __construct(){
        $this->table = new DbTable_ManagementTheme();
    }

    public function getAll(){
        return $this->table->getAll();
    }

    public function getScoreByTheme($questionnaireId, $userId){
        return $this->table->getScoreByTheme($questionnaireId, $userId);
    }
}

?>