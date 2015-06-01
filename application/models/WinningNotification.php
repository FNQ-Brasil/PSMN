<?php

class Model_WinningNotification {
    public $table;

    public function __construct(){
        $this->table = new DbTable_WinningNotification();
        $this->WinningNotificationEnterprise = new Model_WinningNotificationEnterprise();
    }

    public function createWithEnterprises($emailMessageId, $stateId, $competitionId, $responsibleId, $enterprises){
        $this->validateEnterprises($enterprises);
        $winningNotification = $this->create($emailMessageId, $stateId, $competitionId, $responsibleId);

        for($i=0;$i<count($enterprises);$i++) $enterprises[$i]['WinningNotificationId'] = $winningNotification->getId();
        $this->WinningNotificationEnterprise->createList($enterprises);
        return $winningNotification;
    }

    public function create($emailMessageId, $stateId, $competitionId, $responsibleId){
        $winningNotification = $this->table->createRow(array(
            'EmailMessageId' => $emailMessageId, 'StateId' => $stateId, 'CompetitionId' => $competitionId,
            'ResponsibleId' => $responsibleId, 'CreatedAt' => (new Zend_Db_Expr('NOW()'))
        ));
        $winningNotification->save();
        return $winningNotification;
    }

    private function validateEnterprises($enterprise_ids){
        if(count($enterprise_ids) < 1) throw new Exception('At least one enterprise should be given');
    }
}

?>