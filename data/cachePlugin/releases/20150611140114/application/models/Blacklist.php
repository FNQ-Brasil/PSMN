<?php

class Model_Blacklist {
    public $blacklist_table;
    public $context;

    public function __construct($context){
        $this->blacklist_table = new DbTable_Blacklist();
        $this->context = $context;
    }

    public function contains($value){
        return count($this->blacklist_table->getByContextAndValue($this->context, $value)) > 0;
    }

    public function matches($value){
        return count($this->blacklist_table->getByContextAndValueMatch($this->context, $value)) > 0;
    }
}

?>