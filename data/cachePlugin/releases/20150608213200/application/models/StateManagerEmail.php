<?php
class Model_StateManagerEmail {
    public $stateManagerEmail_table;

    public function __construct(){
        $this->stateManagerEmail_table= new DbTable_StateManagerEmail();
    }

    public function getAll(){
        return $this->stateManagerEmail_table->getAll();
    }

    public function getByStateId($stateId){
        return $this->stateManagerEmail_table->getByStateId($stateId);
    }
}

?>