<?php

class Model_ManagementTheme {
    protected $table;

    public function __construct(){
        $this->table = new DbTable_ManagementTheme();
    }

    public function getAll(){
        return $this->table->getAll();
    }

    public function getScoreByTh7eme($questionnaireId, $userId){
        return $this->table->getScoreByTheme($questionnaireId, $userId);
    }
    public function getScoreByTheme($questionnaireId, $userId,$verificador){
        return $this->table->getScoreByTheme($questionnaireId, $userId,$verificador);
    }
}

?>