<?php

class DbTable_WinningNotificationEnterprise extends Vtx_Db_Table_Abstract {
    protected $_name = 'WinningNotificationEnterprise';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getByWinningNotificationId($winningNotificationId){
        return $this->fetchAll($this->selectByWinningNotificationId($winningNotificationId));
    }

    private function selectByWinningNotificationId($winningNotificationId){
        return $this->select()->where('WinningNotificationId = ?', $winningNotificationId)->order('Id ASC');
    }
}

?>