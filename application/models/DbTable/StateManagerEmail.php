<?php
class DbTable_StateManagerEmail extends Vtx_Db_Table_Abstract {
    protected $_name = 'StateManagerEmail';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getByStateId($stateId){
       return $this->fetchAll(array('StateId = ?' => $stateId));
    }
}

?>