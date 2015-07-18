<?php

class LoginController extends Vtx_Action_Abstract
{
    
    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('lost', array('json'))
            ->addActionContext('password-hint', array('json'))
            ->setAutoJsonSerialization(true)
            ->initContext();
    }
    
    public function indexAction()
    {
        $this->_forward('index', 'site');
        return;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        $this->_redirect('/');
    }
    
    /**
     * 
     * funcao que executa Esqueci minha Senha
     * 
     * @author ersilva
     * 
     * @return type
     */
    public function lostAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $data = $this->_getAllParams();        
        
        $enterprise = new Model_Enterprise();
        
        $result = $enterprise->lostPassword($data);

        switch ($result) {           
            case "usuario_nao_existe":
                $this->view->messageError = "Usuário não encontrado com os dados informados";
                break;
            
            case "senha_enviada_para_email_cadastrado":
                $this->view->itemSuccess = true;
                break;
            
            default:
                $this->view->messageError = "Requisição não efetuada";
                break;                        
        }
        
        /*
        $createEnterpriseTransaction = $this->Enterprise
            ->createEnterpriseTransaction($data);
        
        if (!$createEnterpriseTransaction['status']) {
            $this->view->itemSuccess = true;
            $this->view->messageError = $createEnterpriseTransaction['messageError'];
            return;
        }
        */
        //$this->view->itemSuccess = false;
        //$this->view->messageError = "teste";   //$createEnterpriseTransaction['messageError'];
        return;
    }
    
    
    /**
     * 
     */
    public function passwordHintAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $data = $this->_getAllParams();        
        
        $modelUser = new Model_User();
        
        $result = $modelUser->getPasswordHintByCpf($data);

        
        if (!$result['status']) {
            $this->view->messageError = $result['messageError'];
            $this->view->itemSuccess = $result['status'];
            return;
        }
        
        $this->view->messageSuccess = $result['messageSuccess'];
        $this->view->itemSuccess = $result['status'];
        return;
    }
}