<?php

class Cli_EmailMessageController extends Vtx_Action_Abstract{
    protected $context;
    protected $EmailMessage;
    protected $EmailRecipient;

    public function init(){
        $this->EmailMessage = new Model_EmailMessage();
        $this->EmailRecipient = new Model_EmailRecipient();
    }

    // php cli.php -e development|homolog|production -a cli.emailmessage.index -c 'context'

    public function indexAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getContextParam();

        echo "* delivering e-mails\n";
        $this->deliverPendingEmails();
    }

    private function getContextParam(){
        $this->context = isset($_SERVER['argv']) ? $_SERVER['argv'][6] : '';
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