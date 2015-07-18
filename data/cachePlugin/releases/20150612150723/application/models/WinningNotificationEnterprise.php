<?php

class Model_WinningNotificationEnterprise {
    public $table;

    public function __construct(){
        $this->table = new DbTable_WinningNotificationEnterprise();
    }

    public function createList($notificationEnterprises){
        $returnList = array();

        foreach($notificationEnterprises as $n){
            $returnList[] = $this->create($n['WinningNotificationId'], $n['EnterpriseId']);
        }

        return $returnList;
    }

    public function create($winningNotificationId, $enterpriseId){
        return $this->table->createRow(
            array('WinningNotificationId' => $winningNotificationId, 'EnterpriseId' => $enterpriseId)
        )->save();
    }

    public function getByWinningNotificationId($winningNotificationId){
        return $this->table->getByWinningNotificationId($winningNotificationId);
    }
}

?>