<?php

class Cli_ExecutionController extends Vtx_Action_Abstract{
    protected $context;
    protected $questionnaire;
    protected $competitionId;
    protected $EmailMessage;
    protected $EmailRecipient;
    protected $modelUser;

    public function init(){
        $Questionnaire = new Model_Questionnaire();
        $this->context = 'pending_candidature';
        $this->competitionId = Zend_Registry::get('configDb')->competitionId;
        $this->questionnaire = $Questionnaire->getCurrentExecution();
        $this->EmailMessage = new Model_EmailMessage();
        $this->EmailRecipient = new Model_EmailRecipient();
        $this->modelUser = new Model_User();
        if(!$this->questionnaire) throw new Exception('nenhum questionário ativo');
    }

    // php cli.php -e development|homolog|production -a cli.execution.index
    public function indexAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        echo "* creating e-mails for delivery\n";
        $this->createEmails();

        echo "* delivering e-mails\n";
        $this->deliverPendingEmails();
    }

    private function createEmails(){
        $this->createPendingCandidatureWarningEmails();
    }

    private function createPendingCandidatureWarningEmails(){
        $Execution = new Model_Execution();
        $Enterprise = new Model_Enterprise();
        $executions = $Execution->getPendingExecutionsByProgramaId($this->competitionId);
        foreach($executions as $execution){
            $enterprise = $Enterprise->getEnterpriseByUserId($execution->getUserId());
            $email = $enterprise->getEmailDefault();
            $config = $this->pendingCandidatureWarningEmailConfigurations($execution->getUserId());

            if($email and $email != ''){
                $recipients = array(array('Address' => $email));
                $this->EmailMessage->createWithRecipients($config['Context'], $config['SenderName'],
                    $config['SenderAddress'], $config['Subject'], $config['Body'], $recipients);
            }
        }
    }

    private function pendingCandidatureWarningEmailConfigurations($userId){
        $user = $this->modelUser->getUserById($userId);

        $emailDefinitions = Zend_Registry::get('email_definitions')->warning_notification;
        $emailBody = $emailDefinitions->message;

        $emailBody = str_replace(array(':date',':message'), array(date('d/m/Y'), $emailBody), $emailDefinitions->layout);
        $emailBody = str_replace(':name', $user->getFirstName(), $emailBody);

        return array(
            'Context' => $this->context,
            'SenderName' => 'PSMN',
            'SenderAddress' => 'mulherdenegocios@fnq.org.br',
            'Subject' => '[Prêmio Sebrae Mulher de Negócios] Participação no Prêmio Sebrae Mulher de Negócios',
            'Body' => $emailBody
        );
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