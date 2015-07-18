<?php

class Cli_EnterpriseController extends Vtx_Action_Abstract{
    protected $context;
    protected $EmailMessage;
    protected $EmailRecipient;
    protected $Enterprise;
    protected $President;

    public function init(){
        $this->context = 'winning_notification';
        $this->EmailMessage = new Model_EmailMessage();
        $this->EmailRecipient = new Model_EmailRecipient();
        $this->Enterprise = new Model_Enterprise();
        $this->President = new Model_President();
    }

    // php cli.php -e development|homolog|production -a cli.enterprise.index
    public function indexAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        echo "* delivering e-mails\n";
        $this->deliverPendingEmails();
    }

    public function pendingconfirmationAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        echo "* creating e-mails\n";

        $loggedUserId = 41458; // adminwkm
        $filter = array('verified_subscription' => 0);

        $enterprises = $this->Enterprise->getAllForSubscriptions($loggedUserId, $filter);
        
        foreach($enterprises as $enterprise){
            $emailDefault = $enterprise->getEmailDefault();
            $enterpriseId = $enterprise->getId();
            $socialName = $enterprise->getSocialName();

            $president = $this->President->getPresidentByEnterpriseId($enterpriseId);
            $email = $president->getEmail();
            $name = $president->getName();

            if($emailDefault != ''){
                $this->Enterprise->createSubscriptionNotification($emailDefault, $enterpriseId, $socialName);
            }

            if($email != $emailDefault){
                $this->Enterprise->createSubscriptionNotification($email, $enterpriseId, $name);
            }
        }
    }

    private function deliverPendingEmails(){
        foreach($this->EmailMessage->getByContextAndStatus($this->context, 'pending') as $emailMessage){
            $this->sendEmail($emailMessage, $this->EmailRecipient->getByEmailMessageId($emailMessage->getId()));
            $now = new Zend_Db_Expr('NOW()');
            $emailMessage->setSentAt($now)->setStatus('sent')->save();
        }
    }

    private function sendEmail($emailMessage, $recipients){
        $mailerConfig = Zend_Registry::get("config")->mail;
        $transport = new Zend_Mail_Transport_Smtp($mailerConfig->host, $mailerConfig->smtpconfig->toArray());
        Zend_Mail::setDefaultTransport($transport);

        $email = new Zend_Mail('UTF-8');
        $email->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
        $email->addHeader('Content-type','text/html');
        $email->setFrom($emailMessage->getSenderAddress(),$emailMessage->getSenderName());
        $email->setSubject($emailMessage->getSubject());
        $email->setBodyHtml($emailMessage->getBody(),'UTF-8');

        foreach($recipients as $recipient) $email->addBcc($recipient->getAddress(), $recipient->getName());

        $email->send();
    }
}

?>