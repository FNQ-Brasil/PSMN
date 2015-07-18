<?php

class SiteController extends Vtx_Action_Abstract
{ 
    
    /** @var Model_User **/
    protected $modelUser;


    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('index', array('json'))
            ->addActionContext('fale', array('json'))
            ->setAutoJsonSerialization(true)
            ->initContext();
        
        $this->modelUser = new Model_User();
    }
    
    public function indexAction()
    {
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
        $from = $data['fale']['email'];
        $subject = 'PSMN :: Fale conosco enviado pelo site';
        $message = $data['fale']['nome'] ." escreveu:<br>". nl2br($data['fale']['comentario']);
        $message .= "
        <br /><br /> Dados do contato:
        <br />Nome: " .$data['fale']['nome']."<br />
        <br />Empresa: " .$data['fale']['empresa']."<br />
        <br />Email: " .$data['fale']['email']."<br />
        <br />Tel: " .$data['fale']['ddd']."-".$data['fale']['telefone']."<br />
        <br />Celular: " .$data['fale']['ddd_celular']."-".$data['fale']['celular']."<br />
        <br />Cidade: " .$data['fale']['cidade']."-".$data['fale']['uf']."<br />
        ";

        //entra fila para disparo de email
        $eQueue = new Model_EmailQueue();

        $res = $eQueue->setEmailQueue($to, $from, $subject, $message, '', 'ESPERA');    

        if (!$res) {
            $this->view->itemSendSuccess = false;
            $this->view->messageError = $res['messageError'];
            return;
        }        

        //mensagem para gestores
        $uf = $data['fale']['uf'];
        //echo "UF: ".$uf;
        $gestoresEmail = $this->modelUser->dbTable_User->gestoresDaUfParaReceberemFaleConosco($uf);
        //ha gestores?
        if ( count($gestoresEmail) > 0 ) {
           foreach ($gestoresEmail as $emailGestor) { 
               //echo "<br>emailGestor: ".$emailGestor['Email'];
               $res = $eQueue->setEmailQueue($emailGestor['Email'], $from, $subject, $message, '', 'ESPERA');    
           }
        }        
        //$res = true;
        if (!$res) {
            $this->view->itemSendSuccess = false;
            $this->view->messageError = $res['messageError'];
            return;
        }        

        $this->view->itemSendSuccess = true;
        $this->view->messageSuccess = "Mensagem enviada com sucesso.";
    }
}
