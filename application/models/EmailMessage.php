<?php

class Model_EmailMessage {
    public $table;

    public function __construct(){
        $this->table = new DbTable_EmailMessage();
        $this->EmailRecipient = new Model_EmailRecipient();
    }

    public function getByStatus($status){
        return $this->table->getByStatus($status);
    }

    public function getByContextAndStatus($context, $status) {
        return $this->table->getByContextAndStatus($context, $status);
    }

    public function createWithRecipients($context,$senderName,$senderAddress,$subject,
                                         $body,$recipients,$status='pending'){
        $this->validateRecipients($recipients);
        $emailMessage = $this->create($context,$senderName,$senderAddress,$subject,$body,$status);

        for($i=0;$i<count($recipients);$i++) $recipients[$i]['EmailMessageId'] = $emailMessage->getId();
        $this->EmailRecipient->createList($recipients);
        return $emailMessage;
    }

    public function create($context,$senderName,$senderAddress,$subject,$body,$status='pending'){
        $emailMessage = $this->table->createRow(array(
            'Context' => $context, 'SenderName' => $senderName, 'SenderAddress' => $senderAddress,
            'Subject' => $subject, 'Body' => $body, 'Status' => $status, 'CreatedAt' => (new Zend_Db_Expr('NOW()'))
        ));
        $emailMessage->save();
        return $emailMessage;
    }

    private function validateRecipients($recipients){
        if(count($recipients) < 1) throw new Exception('At least one recipient should be given');
    }
}