<?php

class Questionnaire_IndexController extends Vtx_Action_Abstract
{
    public function indexAction()
    {  
        //$this->diagnosticoId = Zend_Registry::get('configDb')->qstn->currentDiagnosticoId;
        //$this->autoavaliacaoId = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;
        $this->dbTable_Questionnaire = new DbTable_Questionnaire();
        $this->enterprise = new Model_Enterprise();
        $Acl = Zend_Registry::get('acl');
        $userLogged = Zend_Auth::getInstance()->getIdentity();
        

        //digitador
        $this->permissaoDigitador = (!$Acl->isAllowed($userLogged->getRole(), 'management:enterprise', 'index')
            and $Acl->isAllowed($userLogged->getRole(), 'management:enterprise', 'edit'));
        if ($this->permissaoDigitador) {
            $this->_redirect('/management/enterprise/cadastro');
            return;
        }
        
        $this->perfilAvaliador = (
             $userLogged->getRoleId() == Zend_Registry::get('config')->acl->roleAppraiserId
        );
        $this->perfilVerificador = (
             $userLogged->getRoleId() == Zend_Registry::get('config')->acl->roleVerificadorId
        );
        if ($this->perfilAvaliador) {
            $this->_redirect('/management/appraiser/');
            return;
        } elseif ($this->perfilVerificador) {
            $this->_redirect('/management/appraiser/checker');
            return;
        }
        
        //Privilégio 'not-coop-responding' tem permissão para alterar/responder para todas coops.
        $this->permissionNotEnterprise = ($Acl->isAllowed(
            $userLogged->getRole(), 'management:enterprise', 'index'
        ) or $Acl->isAllowed($userLogged->getRole(), 'management:report', 'acompanhareport'));
        if ($this->permissionNotEnterprise) {
            $this->_redirect('/management/enterprise/?coop');
            return;
        }
        
        $this->_redirect('questionnaire/register/edit');
    }
}
