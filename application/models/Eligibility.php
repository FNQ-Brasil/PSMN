<?php
/**
 * 
 * Model_Neighborhood
 * @uses  
 * @author gersonlv
 *
 */
class Model_Eligibility
{
    function __construct() {
        //$this->diagnosticoId = Zend_Registry::get('configDb')->qstn->currentDiagnosticoId;
        $this->autoavaliacaoId = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;
        $this->premioId = Zend_Registry::get('configDb')->qstn->currentPremioId;
       // $this->score = Zend_Registry::get('configDb')->elgb->diagnosticoScore;
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
    }
    
    public function doDiagnosticoEligibility($enterprise) {
    
        if ( $enterprise['HeadOfficeStatus'] == '1'
                /*
            ($enterprise['CentralName'] == null) &&
             ($enterprise['HeadOfficeStatus'] == '0') &&
             ($enterprise['FederationName'] == null) &&
             ($enterprise['ConfederationName'] == null) &&
             $enterprise['OcbRegister']
                */
        ) {
            return $this->setDiagnosticoEligibility($enterprise->getId(), 1);
        }

        $to = Zend_Registry::get('configDb')->addr->eligibilityGestorEmail;
        $toEnterprise = $enterprise->getEmailDefault();
        $enterpriseName = $enterprise->getSocialName();
        $enterpriseCnpj = Vtx_Util_Formatting::maskFormat($enterprise->getCnpj(), '##.###.###/####-##');

        $this->createElegibilityNotification('manager', $to, $enterpriseName, $enterpriseCnpj);

        if($toEnterprise != null && $toEnterprise != '')
            $this->createElegibilityNotification('enterprise', $toEnterprise, $enterpriseName, $enterpriseCnpj);

        return $this->setDiagnosticoEligibility($enterprise->getId(), 0);
    }

    private function createElegibilityNotification($recipientType, $to, $enterpriseName,
        $enterpriseCnpj){

        $context = 'ineligibility_'.$recipientType.'_notification';
        $searches = array(':date',':enterpriseName',':enterpriseCnpj');
        $replaces = array(date('d/m/Y'), $enterpriseName, $enterpriseCnpj);
        $recipients = array($to);

        return Manager_EmailMessage::createByEmailDefinitionWithRecipients($context, $searches, $replaces, $recipients);
    }
    
    /**
     * Regra para definir elegibilidade do diagnostico para autoavaliacao
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @return boolean
     */
    public function doAutoavaliacaoEligibility($questionnaireId, $userId)
    {
        //$blockDb = DbTable_Block::getInstance();
        $objQuestionnaire = DbTable_Questionnaire::getInstance();
        
        //dados da empresa
        $enterpriseRow = DbTable_Enterprise::getInstance()->getEnterpriseByUserId($userId);
        
        //id
        $enterpriseId = $enterpriseRow->getId();

        //email
        $enterpriseEmail = $enterpriseRow->getEmailDefault();

        //tipo do questionario
        $questionnaireType = $objQuestionnaire->getQuestionnaireById($questionnaireId)->getDevolutiveCalcId();

        //recupera blocos do questionario
        $blocks = $objQuestionnaire->getBlocks($questionnaireId);

        $atLegislacaoBlock = $blocks->current()->getId();

        if ($questionnaireType == 1 && $atLegislacaoBlock) {
            // Score para o bloco de Atendimento a Legislação - Questionario de Diagnóstico
            $score = $objQuestionnaire->makeScore($questionnaireId, $userId, $atLegislacaoBlock);
            // Elegibilidade para o Questionário de Autoavaliação
            //$eligibility = ($score >= $this->score)? 1 : 0;
            $eligibility = 1;
            // Grava a elegibilidade na Enterprise, tabela EligibilityHistory
            $this->setAutoavaliacaoEligibility($enterpriseId, $eligibility);
            // Envia o E-mail para a empresa
            $this->sendDiagnosticoFeedback($eligibility, $enterpriseEmail); 
        }
        
        return true;
        
    }
    
    public function sendDiagnosticoFeedback($eligibility, $enterpriseEmail) 
    {
        $notificationType = $eligibility == 1 ? 'success' : 'fail';

        if($enterpriseEmail != null && $enterpriseEmail != '')
            $this->createPdgcNotification($notificationType, $enterpriseEmail);

        return true;
    }

    private function createPdgcNotification($notificationType, $to){
        $contextPrefix = $notificationType == 'success' ? '' : 'in';
        $context = 'pdgc_'.$contextPrefix.'eligibility_notification';
        $searches = array(':date');
        $replaces = array(date('d/m/Y'));
        $recipients = array($to);

        return Manager_EmailMessage::createByEmailDefinitionWithRecipients($context, $searches, $replaces, $recipients);
    }
    
    public function getEligibilityHistory($enterpriseId, $questionnaireId, $premio = false)
    {
        return DbTable_EligibilityHistory::getInstance()->getEligibilityHistory($enterpriseId, $questionnaireId, $premio);
    }
    
    public function setDiagnosticoEligibility($enterpriseId, $eligibility)
    {
        DbTable_Enterprise::getInstance()->setDiagnosticoEligibility($enterpriseId, $eligibility);
        
        $questionnaireId = $this->diagnosticoId;
        
        $diagnosticoHistory = $this->getEligibilityHistory($enterpriseId, $questionnaireId);
        
        if (!$diagnosticoHistory || ($diagnosticoHistory->getEligibility() != $eligibility)) {
            $userId = (isset($this->userAuth)) ? $this->userAuth->getUserId() : null;
            DbTable_EligibilityHistory::getInstance()
            ->setEligibilityHistory($enterpriseId, $questionnaireId, $userId, $eligibility);
        }
        return $eligibility;
    }
    
    public function setAutoavaliacaoEligibility($enterpriseId, $eligibility)
    {
        DbTable_Enterprise::getInstance()->setAutoavaliacaoEligibility($enterpriseId, $eligibility);
        
        $questionnaireId = $this->autoavaliacaoId;
        
        $autoavaliacaoHistory = $this->getEligibilityHistory($enterpriseId, $questionnaireId);
        
        if (!$autoavaliacaoHistory || ($autoavaliacaoHistory->getEligibility() != $eligibility)) {
            $userId = (isset($this->userAuth)) ? $this->userAuth->getUserId() : null;
            DbTable_EligibilityHistory::getInstance()
            ->setEligibilityHistory($enterpriseId, $questionnaireId, $userId, $eligibility);
        }
        return $eligibility;
    }
    
    public function setPremioEligibility($enterpriseId, $eligibility)
    {
        DbTable_Enterprise::getInstance()->setPremioEligibility($enterpriseId, $eligibility);
        
        $questionnaireId = $this->premioId;
        
        $premioHistory = $this->getEligibilityHistory($enterpriseId, $questionnaireId, true);
        
        if (!$premioHistory || ($premioHistory->getEligibility() != $eligibility)) {
            $userId = (isset($this->userAuth)) ? $this->userAuth->getUserId() : null;
            DbTable_EligibilityHistory::getInstance()
            ->setEligibilityHistory($enterpriseId, $questionnaireId, $userId, $eligibility, true);
        }
        return $eligibility;
    }
}