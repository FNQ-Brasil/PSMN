<?php

class Model_ExecutionPontuacaoLog {
    protected $table;

    public function createByExecutionPontuacao($executionPontuacao){
        return $this->table->create($executionPontuacao->getExecutionId(), $executionPontuacao->getNegociosTotal());
    }

    public function __construct(){
        $this->table = new DbTable_ExecutionPontuacaoLog();
    }

    public function getAll(){
        return $this->table->getAll();
    }

    public function getByExecution($executionId){
        return $this->table->getByExecution($executionId);
    }
}

?>