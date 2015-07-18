<?php

class DbTable_EmailRecipient extends Vtx_Db_Table_Abstract {
    protected $_name = 'EmailRecipient';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getByEmailMessageId($emailMessageId){
        return $this->fetchAll($this->selectByEmailMessageId($emailMessageId));
    }

    private function selectByEmailMessageId($emailMessageId){
        return $this->select()->where('EmailMessageId = ?', $emailMessageId)->order('Id ASC');
    }
}

?>