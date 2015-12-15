<?php

class DbTable_EmailMessage extends Vtx_Db_Table_Abstract {
    protected $_name = 'EmailMessage';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getByStatus($status){
        return $this->fetchAll($this->selectByStatus($status));
    }

    public function getByContextAndStatus($context, $status){
        return $this->fetchAll($this->selectByContextAndStatus($context, $status));
    }

    private function selectByContextAndStatus($context, $status){
        return $this->selectByStatus($status)->where('Context = ?', $context);
    }

    private function selectByStatus($status){
        return $this->select()->where('Status = ?', $status)->order('Id ASC');
    }
}

?>