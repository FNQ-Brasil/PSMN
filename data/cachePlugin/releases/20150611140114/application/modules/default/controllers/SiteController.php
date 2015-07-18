<?php

class SiteController extends Vtx_Action_Abstract
{ 
    
    /** @var Model_User **/
    protected $modelUser;
    protected $modelQuestionnaire;
    protected $ContactUsRecipient;


    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('index', array('json'))
            ->addActionContext('fale', array('json'))
            ->setAutoJsonSerialization(true)
            ->initContext();
        
        $this->modelUser = new Model_User();
        $this->modelQuestionnaire = new Model_Questionnaire();
        $this->ContactUsRecipient = new Model_ContactUsRecipient();
    }
    
    public function indexAction()
    {
        $this->view->publicSubscriptionEnded = !$this->modelQuestionnaire->subscriptionPeriodIsOpenFor(null,null);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('/login/logout'); //ir pra logout temporario
            return;
        }
        $this->view->originalRequest = $this->_getParam('originalRequest', null);

        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $dados = $this->_getAllParams();
        $buscaLogin = $this->modelUser->buscarLogin($dados['username']);

        if (!$buscaLogin['status']) {
            
            $this->view->existe = false;
            $this->view->cpf = $buscaLogin['cpf'];
            $this->view->forward = 'true';
            $this->view->loadUrlRegister = $this->view->baseUrl('/questionnaire/register/');
            $this->view->messageError = false;
            $this->view->cpfValid = isset($buscaLogin['cpfValid'])? $buscaLogin['cpfValid'] : false;

            $this->view->urlForward = $this->view->baseUrl(
                '/questionnaire/register/index/cpf/' . $buscaLogin['cpf'] . '/forward/true'
            );
            
            return;
        }
        $this->view->login = $dados['username'];
        $this->view->existe = true;

        if($buscaLogin['userRow']->getStatus() == 'I'){
            $this->view->messageError = 'Não foi possível efetuar o login: usuário desativado. Entre em contato com o seu Gestor ou o Administrador do sistema.';
        }
        
        try {
            $Authenticate = new Model_Authenticate();
            $redirect = $Authenticate->identify($this->_getAllParams());
            $headerRedirect = $this->_getParam('headerRedirect', 0);
            if ($headerRedirect) {
                $this->_redirect($redirect);
            } else {
                $this->view->urlForward = $this->view->baseUrl($redirect);
            }
		} catch (Vtx_UserException $e) {
            $this->view->messageError = $e->getMessage();
            $this->view->originalRequest = array('uri' => $this->_getParam('uri'));
		}
    }
    
    public function premioAction() {}
    public function participarAction() {}
    public function premiacaoAction() {}
    public function regulamentoAction() {}
    public function historiaAction() {}
    public function premiadasAction() {}
    
    public function cronogramaAction() {}
    
    /**
     * formulario fale conosco
     * 
     * @return type
     */
    public function faleAction() 
    {

        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $data = $this->getRequest()->getParams();

        $to = Zend_Registry::get('configDb')->addr->sescoopContactEmail;

        $fix_number = $data['fale']['ddd']."-".$data['fale']['telefone'];
        $cellphone = $data['fale']['ddd_celular']."-".$data['fale']['celular'];

        $this->createContactUsNotification($to, $data['fale']['nome'], $data['fale']['comentario'],
            $data['fale']['empresa'], $data['fale']['email'], $fix_number, $cellphone, $data['fale']['cidade'],
            $data['fale']['uf']);

        $this->view->itemSendSuccess = true;
        $this->view->messageSuccess = "Mensagem enviada com sucesso.";
    }

    private function createContactUsNotification($to, $name, $comment, $enterprise, $email, $fix_number, $cellphone,
        $city, $uf){

        $context = 'contact_us_notification';
        $searches = array(':date', ':name',':comment',':enterprise',':email',':fix_number',':cellphone',':city',':uf');
        $replaces = array(date("d/m/Y"), $name, $comment, $enterprise, $email, $fix_number, $cellphone, $city, $uf);
        $recipients = array($to);

        $contactUsRecipients = $this->ContactUsRecipient->getRecipientsByUf($uf);
        foreach($contactUsRecipients as $recipient) $recipients[] = $recipient->getEmail();

        return Manager_EmailMessage::createByEmailDefinitionWithRecipients($context, $searches, $replaces, $recipients);
    }
}
