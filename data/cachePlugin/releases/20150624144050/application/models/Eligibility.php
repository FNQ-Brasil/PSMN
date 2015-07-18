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
        
        // Envia o e-mail para o gestor caso não seja elegível 
        $to = Zend_Registry::get('configDb')->addr->eligibilityGestorEmail;
        $from = Zend_Registry::get('config')->util->emailSescoop; // 'sescoop@sescoop.org.br'; 
        $subject = 'Inelegibilidade para Quest. Diagnóstico: '. $enterprise->getSocialName();
        $message = 'Caro(a) Gestor(a),
                    <br /><br />
                    Foi detectado a inelegibilidade da empresa abaixo para a execução do Questionário de Diagnóstico.
                    <br /><br /><b>'.$enterprise->getSocialName().' - CNPJ: '.  Vtx_Util_Formatting::maskFormat($enterprise->getCnpj(), '##.###.###/####-##').'</b>
                    <br /><br />
                    Atenciosamente,
                    <br /><br />
                    Equipe SESCOOP';
        //Vtx_Util_Mail::send($to,$from,$subject,$message);
        
        $eQueue = new Model_EmailQueue();
        $eQueue->setEmailQueue($to, $from, $subject, $message);
        
        
        
        // Envia o e-mail para a empresa caso inelegível 
        $toEnterprise = $enterprise->getEmailDefault();
        $fromEnterprise = Zend_Registry::get('config')->util->emailSescoop; // 'sescoop@sescoop.org.br'; 
        $subjectEnterprise = 'Inelegibilidade para Quest. Diagnóstico: '. $enterprise->getSocialName();
        $messageEnterprise = 'Caro(a) Cooperado(a),
                    <br /><br /><b>'.$enterprise->getSocialName().' - CNPJ: '.  Vtx_Util_Formatting::maskFormat($enterprise->getCnpj(), '##.###.###/####-##').'</b>
                    <br /><br />
                    Foi detectado a inelegibilidade da sua empresa para a execução do Questionário de Diagnóstico.
                    <br />
                    Para maiores esclarecimentos, entre em contato com sua Regional.
                    <br /><br />
                    Atenciosamente,
                    <br /><br />
                    Equipe SESCOOP';
        //Vtx_Util_Mail::send($toEnterprise,$fromEnterprise,$subjectEnterprise,$messageEnterprise);
        
        //classe instanciada na linha 45
        $eQueue->setEmailQueue($toEnterprise,$fromEnterprise,$subjectEnterprise,$messageEnterprise);
        
        
        return $this->setDiagnosticoEligibility($enterprise->getId(), 0);
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
        $to = $enterpriseEmail;
        $from = 'sescoop@sescoop.org.br';
        $subject = 'Inscrição PDGC';
        
        if ($eligibility == 1) {
            $message = 'Caro(a) cooperado(a),
                <br /><br />
                Informamos que seu cadastro é elegível para participação do programa PDGC.<br />
                Participo de programa preenchendo o questionário no endereço abaixo:<br /><br />
                http://www.sescoop.org.br
                <br /><br /><br />                    
                Atenciosamente,
                <br /><br />
                Equipe SESCOOP';
        } else {
            $message = 'Caro(a) cooperado(a),
                <br /><br />
                Informamos que seu cadastro não é elegível para participação do programa PDGC por 
                não cumprir os requisitos da Diretriz Nacional da GEMDC.<br /><br />
                Para maiores detalhes, acesse o SESCOOP em http://www.sescoop.org.br
                <br /><br /><br />                    
                Atenciosamente,
                <br /><br />
                Equipe SESCOOP';
        }
              
        //Vtx_Util_Mail::send($to,$from,$subject,$message);
        $eQueue = new Model_EmailQueue();
        $eQueue->setEmailQueue($to, $from, $subject, $message);        
        
        
        return true;
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