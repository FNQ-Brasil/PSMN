<?php

class Model_ContactUsRecipient {
    public $contactUsRecipient_table;
    public $context;

    public function __construct(){
        $this->contactUsRecipient_table = new DbTable_ContactUsRecipient();
    }

    public function getRecipientsByUf($uf){
        return $this->contactUsRecipient_table->getRecipientsByUf($uf);
    }
}

?>