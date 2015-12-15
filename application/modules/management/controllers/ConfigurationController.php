<?php
/**
 * 
 * Controller_Configuration
 * @uses  
 * @author gersonlv
 *
 */
class Management_ConfigurationController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->Configuration = new Model_Configuration();
        $this->Questionnaire = new Model_Questionnaire();
        $this->modelCompetition = new Model_Competition();
        
        $this->currentPremio = Zend_Registry::get('configDb')->qstn->currentPremioId;

        $this->currentAutoavaliacao = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;

        $this->competitionId = Zend_Registry::get('configDb')->competitionId;
    }

    public function indexAction()
    {
        $this->view->getAllCompetition = $this->modelCompetition->getAllCompetition();
        
        $modelBlock = new Model_Block();
        $arrConfiguration = $this->Configuration->getSescoopConfiguration();
        
        $this->view->allConfig = $arrConfiguration;
        
        $this->view->getAutoavaliacaoQuestionnaires = $this->Questionnaire->getQuestionnairesByDevolutiveCalcId(3);
        
        $this->view->getAllBlockByAutoavaliacao = $modelBlock
            ->getAllByQuestionnaireId($arrConfiguration->qstn->currentAutoavaliacaoId);
                
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        // ---------------------------
        // Autoavaliação - ID
        $autoavaliacaoConfigurationRow = $this->Configuration->getConfigurationByConfKey('qstn.currentAutoavaliacaoId');
        $updateAutoavaliacaoConfiguration = $this->Configuration->updateConfig(
            $autoavaliacaoConfigurationRow, $this->_getParam('autoavaliacao_value')
        );
        // bloco de empreendedorismo
        $blockEmpreendedorismoConfigurationRow = $this->Configuration->getConfigurationByConfKey('qstn.currentBlockIdEmpreendedorismo');
        $updateEmpreendedorismoBlockConfiguration = $this->Configuration->updateConfig(
            $blockEmpreendedorismoConfigurationRow, $this->_getParam('block_empreendedorismo_value')
        );
        // bloco de negócios corrente
        $blockNegociosConfigurationRow = $this->Configuration->getConfigurationByConfKey('qstn.currentBlockIdNegocios');
        $updateNegocioBlockConfiguration = $this->Configuration->updateConfig(
            $blockNegociosConfigurationRow, $this->_getParam('block_negocios_value')
        );
        
        // Contato por email
        $secoopContactConfigurationRow = $this->Configuration->getConfigurationByConfKey('addr.sescoopContactEmail');
        $updateSecoopContactConfiguration = $this->Configuration->updateConfig(
            $secoopContactConfigurationRow, $this->_getParam('secoop_contact_value')
        );
        
        // Contato por email (inelegibilidade )
        $eligibilityGestorConfigurationRow = $this->Configuration->getConfigurationByConfKey('addr.eligibilityGestorEmail');
        $updateEligibilityGestorConfiguration = $this->Configuration->updateConfig(
            $eligibilityGestorConfigurationRow, $this->_getParam('eligibility_gestor_value')
        );
        
        // Concurso (competition_id)
        $competitionConfigurationRow = $this->Configuration->getConfigurationByConfKey('competitionIdKey');
        $updateCompetitionConfiguration = $this->Configuration->updateConfig(
            $competitionConfigurationRow, $this->_getParam('competition_id')
        );
        
        
        if (!$updateAutoavaliacaoConfiguration['status']) {
            $this->view->messageError = $updateAutoavaliacaoConfiguration['messageError'];
            return;
        }

        if (!$updateSecoopContactConfiguration['status']) {
            $this->view->messageError = $updateSecoopContactConfiguration['messageError'];
            return;
        }
        if (!$updateEligibilityGestorConfiguration['status']) {
            $this->view->messageError = $updateEligibilityGestorConfiguration['messageError'];
            return;
        }
        if (!$updateCompetitionConfiguration['status']) {
            $this->view->messageError = $updateCompetitionConfiguration['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
        
        return;
    }
   
}