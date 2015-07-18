<?php

class DbTable_Blacklist extends Vtx_Db_Table_Abstract {
    protected $_name = 'Blacklist';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getByContext($context){
        return $this->fetchAll($this->selectByContext($context));
    }

    public function getByContextAndValue($context, $value){
        return $this->fetchAll($this->selectByContext($context)->where('Value = ?', $value));
    }

    public function getByContextAndValueMatch($context, $value){
        return $this->fetchAll($this->selectByContext($context)->where('? regexp Value', $value));
    }

    private function selectByContext($context){
        return $this->select()->where('Context = ?', $context)->order('Value ASC');
    }
}

?>