<?php

class Model_Whitelist {
    public $whitelist_table;
    public $context;

    public function __construct($context){
        $this->whitelist_table = new DbTable_Whitelist();
        $this->context = $context;
    }

    public function contains($value){
        return count($this->whitelist_table->getByContextAndValue($this->context, $value)) > 0;
    }

    public function matches($value){
        return count($this->whitelist_table->getByContextAndValueMatch($this->context, $value)) > 0;
    }
}

?>