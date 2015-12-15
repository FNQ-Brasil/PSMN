<?php

class DbTable_ExecutionPontuacaoLog extends Vtx_Db_Table_Abstract {
    protected $_name = 'ExecutionPontuacaoLog';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function create($executionId, $negociosTotalId){
        $executionPontuacaoLog = $this->createRow()
            ->setExecutionId($executionId)
            ->setNegociosTotal($negociosTotalId)
            ->setCreatedAt(new Zend_Db_Expr('NOW()'))
        ;

        $executionPontuacaoLog->save();
        return $executionPontuacaoLog;
    }

    public function getAll(){
        return $this->fetchAll();
    }

    public function getByExecution($executionId){
        return $this->fetchAll(array('ExecutionId = ?' => $executionId));
    }
}

?>