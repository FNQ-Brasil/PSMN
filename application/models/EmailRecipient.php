<?php

class Model_EmailRecipient {
    public $table;

    public function __construct(){
        $this->table = new DbTable_EmailRecipient();
    }

    public function createList($recipients){
        $returnList = array();

        foreach($recipients as $r){
            $name = array_key_exists('Name',$r) ? $r['Name'] : NULL;
            $returnList[] = $this->create($r['EmailMessageId'],$name,$r['Address']);
        }

        return $returnList;
    }

    public function create($emailMessageId,$name,$address){
        return $this->table->createRow(array('EmailMessageId' => $emailMessageId, 'Name' => $name, 'Address' => $address))->save();
    }

    public function getByEmailMessageId($emailMessageId){
        return $this->table->getByEmailMessageId($emailMessageId);
    }
}

?>