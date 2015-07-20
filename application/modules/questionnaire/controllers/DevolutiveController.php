<?php

class Questionnaire_DevolutiveController extends Vtx_Action_Abstract
{
    /* @TODO colocar os métodos daqui na model */

    protected $_messagesError = array(
        'questionnaireNotExists' => 'Questionário inexistente.',
        'questionnaireNotFullyAnswered' => 'O questionário precisa ser totalmente respondido.',
        'relatoNotAnswered' => 'Para gerar a devolutiva, é necessário responder completamente o questionário de gestão e realizar o preenchimento do relato.',
        'blocksNotExists' => 'Blocos Negócios e/ou Empreendedorismo não existem'
    );
    
    /** @var Model_Devolutive **/
    protected $Devolutive;

    /** @var Model_Questionnaire  **/
    protected $Questionnaire;    
    
    protected $questionnaire_id;
    
    protected $devolutive_id;
    
    protected $report;
    
    /** @var Model_Enterprise **/
    protected $enterprise;
    
    /**
     * @var Model_Execution 
     */
    protected $Execution;    
    
    /** @var Model_ProtocoloDevolutiva **/
    protected $modelProtocolo;
    

    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('index', array('json'))
             ->addActionContext('verificacao', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();

        $this->Devolutive = new Model_Devolutive();
        $this->Questionnaire = new Model_Questionnaire();
        $this->Acl = Zend_Registry::get('acl');
        $this->auth = Zend_Auth::getInstance();
        $this->report = new Model_EnterpriseReport();      
        $this->enterprise = new Model_Enterprise();
        $this->Execution = new Model_Execution();
        $this->modelProtocolo = new Model_ProtocoloDevolutiva();
    }
    
    /**
     * Action que executa procs para Calcular Caracteristica Empreendedora
     * Calcula Pontuacao Caracteristica Empreendedora
     */
    //

    /*
     * Action responsavel por processar a devolutiva.
     * Esta action eh executada via ajax em chamada js descrita em "respond.js" linha 126.
     * 
     * //todo do desenvolvedor antigo
     * @TODO arrumar logica de devolutiva da empresa para perfil de digitador
     */
    public function indexAction()
    {
        
       $seconds = 360; //3 minutos
       set_time_limit($seconds); 
       $limit = $this->_getParam('limit');
       $enterpriseProgramaIdMaiorQue = $this->_getParam('maiorque');
       
       //mpe9 / mpe9 
       if ( isset($limit) && isset($enterpriseProgramaIdMaiorQue) ) {

            /**************************************************************/
            //Ex: http://site-ambiente/questionnaire/devolutive/index/?limit=10&maiorque=96 
            $QUEM_FARA_PROCESSAMENTO = "Pontuacao_Em_Massa"; //Devolutiva_Em_Massa
            $this->Devolutive->setStartProcessamentoEmMassa(true);
            //execucao em massa de geracao devolutiva
            $this->cligrava($limit, $enterpriseProgramaIdMaiorQue, $QUEM_FARA_PROCESSAMENTO);
            exit;
       }         
        
        
        /*if (!$this->getRequest()->isPost()) {
            return;
        }
         * 
         */
        
        $this->questionnaire_id = $this->_getParam('qstn');

        if(!$this->questionnaire_id){
            $this->questionnaire_id = $this->Questionnaire->getCurrentExecution()->getId();
        }
        
        ////////////////////////
        // Calcula Pontuacao Caracteristica Empreendedora
        //        $n = new Model_BlockEnterpreneurGrade();
        //        $QuestionnaireId=50;
        //        $BlockId=60;
        //        $UserId=2;        
        //        $CompetitionId = 2013;
        //        $x = $n->execProcPontuacaoGrade($QuestionnaireId, $BlockId, $UserId, $CompetitionId  );
        //        //$x = $n->getBlockById(105);
        //        var_dump($x);
        //        die;
        ////////////////////////
        
        $questionnaire_id = $this->questionnaire_id;
        
        //$user_id = Zend_Auth::getInstance()->getIdentity()->getUserId();
        $user_id = $this->enterprise->getUserIdByIdKey($this->_getParam('enterprise-id-key'));
        //$user_id = $this->_getParam('enterprise-user');
       
        $enterprise = $this->enterprise->getEnterpriseByUserId($user_id);
        $enterpriseId = $enterprise->getId();

        //desabilita layout
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);

        //recupera e valida questionario
        if (!$this->recuperaValidaQuestionario($questionnaire_id)) {
            $this->view->questionnaire_id = "";
            throw new Exception($this->_messagesError['questionnaireNotExists']);
            return;
        }

        //relatoNotAnswered
        if (!$this->report->getCurrentEnterpriseReportByEnterpriseId($enterpriseId)) {
            $this->view->questionnaire_id = "";
            $this->view->messageError = $this->_messagesError['relatoNotAnswered'];
            //echo 'relato nao respondido: '.$this->view->messageError;
            return;
        }
        
        //verifica se questoes foram respondidas
        if (!$this->verificaQuestoesRespondidas($questionnaire_id, $user_id)) {
            $this->view->questionnaire_id = "";
            $this->view->messageError = $this->_messagesError['questionnaireNotFullyAnswered'];
            return;
        }

        //permissoes de acesso
        $userLogged = Zend_Auth::getInstance()->getIdentity();       
        
        $this->loggedUserId = $userLogged->getUserId();
        
        $permissionEvaluationOfResponse = $this->Acl->isAllowed(
            $userLogged->getRole(), 'management:questionnaire', 'evaluation-of-response'
        );
        
        //seta dados para objeto Devolutive
        $this->Devolutive->setDevolutiveId($this->devolutive_id);
        $this->Devolutive->setIsRA($permissionEvaluationOfResponse);
        $this->Devolutive->setQuestionnaireId($questionnaire_id);
        $this->Devolutive->setUserId($user_id);
        
        //ids dos blocos sao setados
        $this->validaBlocosQuestionario($questionnaire_id);
        
        $competitionId = Zend_Registry::get('configDb')->competitionId;
        
        //exec procedures
        
        $this->processaCaracteristicaEmpreendedora($questionnaire_id,
                                                   $user_id,
                                                   $this->Devolutive->getBlockIdEmpreendedorismo(), 
                                                   $competitionId  
                                                   );

        /** faz geracao e processamento do Protocolo Id da devolutiva  **/
        $geraProtocolo = $this->modelProtocolo->geracaoDoProtocolo( $this->view, $this->Devolutive, $this->Execution ,new Model_User(), $questionnaire_id, 
                                                                    $user_id, $this->loggedUserId, $competitionId , $permissionEvaluationOfResponse
                                                                   );

       /**
        * model responsavel pelas regras negocio de geracao da devolutiva
        */

        $devolutiveAlreadyExists = $this->Devolutive->devolutivaJaExiste();
        
        $devolutivePath = $this->Devolutive->makePdfDevolutive();
      
        if ($geraProtocolo) { //se protocolo foi gerado
            //grava caminho da devolutiva gerada na tabela de protocolo devolutiva
            $this->modelProtocolo->updateDevolutivaPath($devolutivePath, $this->Devolutive->getProtocoloIdDevolutiva());
        }
        
        //valores default para Pontuacao
        $this->Devolutive->configuraGravaPontuacaoExecution($user_id);
        
        
        //recupera pontuacao e processa pontuacao
        $this->Devolutive->processaPontuacaoBlocosDeUmQuestionario();

        if(!$devolutiveAlreadyExists){
            $pdf = new Report_Devolutive_PDF($this->Devolutive, APPLICATION_PATH.'/../htdocs'.$devolutivePath);
            $pdf->saveToFile();
        }
        
        if ($devolutivePath and $geraProtocolo) {
            $modelLogCadastroEmpresa = new Model_LogCadastroEmpresa();
            // Insere LOG de quem gerou o PDF.
            $modelLogCadastroEmpresa->createLogDevolutiva(
                $this->loggedUserId, $enterpriseId
            );
        }
        
        //informa url onde o pdf da devolutiva foi gravado
        if ($devolutivePath) {
             $this->view->itemSuccess = true;
             $this->view->devolutive = $devolutivePath;
             $this->view->regerar_devolutive =  $this->view->baseUrl('questionnaire/respond')
                . "/index/geraDevolutiva/1/regerar/1/enterprise-id-key/" . $this->_getParam('enterprise-id-key');
             $this->view->permissaoCadastrar = $this->Acl->isAllowed($this->auth->getIdentity()->getRole(), 'management:enterprise', 'cadastro');
             
             return;
        }
        
        $this->view->messageError = 'Náo foi possível a geração da devolutiva.';
        
    } //end action

    public function verificacaoAction(){
        
       $seconds = 360; //3 minutos
       set_time_limit($seconds); 
       $limit = $this->_getParam('limit');
       $enterpriseProgramaIdMaiorQue = $this->_getParam('maiorque');
       
       //mpe9 / mpe9 
       if ( isset($limit) && isset($enterpriseProgramaIdMaiorQue) ) {

            /**************************************************************/
            //Ex: http://site-ambiente/questionnaire/devolutive/index/?limit=10&maiorque=96 
            $QUEM_FARA_PROCESSAMENTO = "Pontuacao_Em_Massa"; //Devolutiva_Em_Massa
            $this->Devolutive->setStartProcessamentoEmMassa(true);
            //execucao em massa de geracao devolutiva
            $this->cligrava($limit, $enterpriseProgramaIdMaiorQue, $QUEM_FARA_PROCESSAMENTO);
            exit;
       }         
        
        
        /*if (!$this->getRequest()->isPost()) {
            return;
        }
         * 
         */
        
        $this->questionnaire_id = $this->_getParam('qstn');
        

        if(!$this->questionnaire_id){
            $this->questionnaire_id = $this->Questionnaire->getCurrentExecution()->getId();
        }
        
        ////////////////////////
        // Calcula Pontuacao Caracteristica Empreendedora
        //        $n = new Model_BlockEnterpreneurGrade();
        //        $QuestionnaireId=50;
        //        $BlockId=60;
        //        $UserId=2;        
        //        $CompetitionId = 2013;
        //        $x = $n->execProcPontuacaoGrade($QuestionnaireId, $BlockId, $UserId, $CompetitionId  );
        //        //$x = $n->getBlockById(105);
        //        var_dump($x);
        //        die;
        ////////////////////////
        
        $questionnaire_id = $this->questionnaire_id;
        
        //$user_id = Zend_Auth::getInstance()->getIdentity()->getUserId();
        $user_id = $this->enterprise->getUserIdByIdKey($this->_getParam('enterprise-id-key'));
        //$user_id = $this->_getParam('enterprise-user');
        
        $enterprise = $this->enterprise->getEnterpriseByUserId($user_id);
        $enterpriseId = $enterprise->getId();
        
        //desabilita layout
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);

        //recupera e valida questionario
        
        if (!$this->recuperaValidaQuestionario($questionnaire_id)) {
            $this->view->questionnaire_id = "";
            throw new Exception($this->_messagesError['questionnaireNotExists']);
            return;
        }

        //relatoNotAnswered        
        if (!$this->report->getCurrentEnterpriseReportByEnterpriseId($enterpriseId)) {
            $this->view->questionnaire_id = "";
            $this->view->messageError = $this->_messagesError['relatoNotAnswered'];
            //echo 'relato nao respondido: '.$this->view->messageError;
            return;
        }
        
        
        
        //verifica se questoes foram respondidas
        if (!$this->verificaQuestoesRespondidas($questionnaire_id, $user_id)) {
            $this->view->questionnaire_id = "";
            $this->view->messageError = $this->_messagesError['questionnaireNotFullyAnswered'];
            return;
        }

        //permissoes de acesso
        $userLogged = Zend_Auth::getInstance()->getIdentity();       
        
        $this->loggedUserId = $userLogged->getUserId();
        
        $permissionEvaluationOfResponse = $this->Acl->isAllowed(
            $userLogged->getRole(), 'management:questionnaire', 'evaluation-of-response'
        );
        
        //seta dados para objeto Devolutive
        $this->Devolutive->setDevolutiveId($this->devolutive_id);
        $this->Devolutive->setIsRA($permissionEvaluationOfResponse);
        $this->Devolutive->setQuestionnaireId($questionnaire_id);
        $this->Devolutive->setUserId($user_id);
        
        //ids dos blocos sao setados
        $this->validaBlocosQuestionario($questionnaire_id);
        
        
        
        $competitionId = Zend_Registry::get('configDb')->competitionId;
        
        
        
        //exec procedures        
        $this->processaCaracteristicaEmpreendedora($questionnaire_id,
                                                   $user_id,
                                                   $this->Devolutive->getBlockIdEmpreendedorismo(), 
                                                   $competitionId  
                                                   );
       
        /** faz geracao e processamento do Protocolo Id da devolutiva  **/
        $geraProtocolo = $this->modelProtocolo->geracaoDoProtocolo( $this->view, $this->Devolutive, $this->Execution ,new Model_User(), $questionnaire_id, 
                                                                    $user_id, $this->loggedUserId, $competitionId , $permissionEvaluationOfResponse
                                                                   );
        
       /**
        * model responsavel pelas regras negocio de geracao da devolutiva
        */

        $devolutiveAlreadyExists = $this->Devolutive->devolutivaJaExiste();
        
        
        $devolutivePath = $this->Devolutive->makePdfDevolutive();
        
        if ($geraProtocolo) { //se protocolo foi gerado
            //grava caminho da devolutiva gerada na tabela de protocolo devolutiva
            $this->modelProtocolo->updateDevolutivaPath($devolutivePath, $this->Devolutive->getProtocoloIdDevolutiva());
        }
        
        //valores default para Pontuacao
        $this->Devolutive->configuraGravaPontuacaoExecution($user_id);
        
        
        //recupera pontuacao e processa pontuacao
        $this->Devolutive->processaPontuacaoBlocosDeUmQuestionario();
          
        if(!$devolutiveAlreadyExists){
            $pdf = new Report_Devolutive_PDF($this->Devolutive, APPLICATION_PATH.'/../htdocs'.$devolutivePath);
            $pdf->saveToFile();
        }
        
        if ($devolutivePath and $geraProtocolo) {
            $modelLogCadastroEmpresa = new Model_LogCadastroEmpresa();
            // Insere LOG de quem gerou o PDF.
            $modelLogCadastroEmpresa->createLogDevolutiva(
                $this->loggedUserId, $enterpriseId
            );
        }
          
        //informa url onde o pdf da devolutiva foi gravado
        if ($devolutivePath) {
            
             $this->view->itemSuccess = true;
             $this->view->devolutive = $devolutivePath;
             
             //link para regerar a devolutive
             $this->view->regerar_devolutive =  $this->view->baseUrl('questionnaire/respond')
                . "/index/geraDevolutiva/1/regerar/1/enterprise-id-key/" . $this->_getParam('enterprise-id-key');
             $this->view->permissaoCadastrar = $this->Acl->isAllowed($this->auth->getIdentity()->getRole(), 'management:enterprise', 'cadastro');
          
             
             return;
        }
        $this->view->messageError = 'Náo foi possível a geração da devolutiva.';
        
        
        
        
          
        
    }//and action
    
    /**
     * geracao em massa de devolutiva via browser
     * 
     * @param type $limit
     * @param type $enterpriseProgramaIdMaiorQue
     */
    private function cligrava($limit, $enterpriseProgramaIdMaiorQue, $QUEM_FARA_PROCESSAMENTO)
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);  
      
        echo "<small><br>EnterpriseCategoryAwardCompetitionMaiorQue: ".$enterpriseProgramaIdMaiorQue;
        echo "<br>";
        $objUser = new DbTable_User();
        
        
        //join com tb Execution
        $res = $objUser->getUserByLimitAndIdMaiorJoinExecution($enterpriseProgramaIdMaiorQue, $limit);

        //sem join com tb Execution
        //$res = $objUser->getUserByLimitAndIdMaior($enterpriseProgramaIdMaiorQue, $limit);
        //var_dump($res);
        echo "<br><br>";
        foreach ($res as $campo )
        {           
           //$userIdParaDevolutive = $campo['UserId'];
           $userId = $campo['UserId'];
           $enterpriseProgramaId = 2014;
           
           //$this->Questionnaire->tbQuestionnaire->getPontuacaoQuestao4BlocoResponsabilidadeSocial(46, $blockId=null, 65);
           
           //exit;
           
           echo "enterpriseProgramaId: " .$enterpriseProgramaId;
           echo " - UserId: " .$userId;
           echo " - ";
           //echo $campo['SocialName'];
           
           
           switch ($QUEM_FARA_PROCESSAMENTO) 
           {           
               //grava tabela ExecutionPontuacao
               case "Pontuacao_Em_Massa":
                   $this->Devolutive->configuraGravaPontuacaoExecution($userId);
                   //grava Pontuacao para bloco Gestao Empresa
                   $this->Devolutive->processaPontuacaoBlocosDeUmQuestionario($userId);
                   break;      
           }
           
           echo "<br>";
           
       }
    
       //echo "<br>";
       //echo "Gerando devolutivas";
    }
    
    
    
    
    /**
     * processa execucao de procedures que calculam pontuacao a partir das 
     * respostas do Questionario de Empreendedorismo
     * 
     * @param type $QuestionnaireId
     * @param type $UserId
     * @param type $BlockId
     * @param type $CompetitionId
     * @return boolean
     */
    public function processaCaracteristicaEmpreendedora($QuestionnaireId, $UserId, $BlockId, $CompetitionId  )
    {
        //forma correta
        $db = Zend_Registry::get('db');
        $sql = "CALL p_pontuacao_grade (?, ?, ?, ?)";
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        $params = array($QuestionnaireId, $BlockId, $UserId, $CompetitionId);
        $stmt->execute($params);
        //$stmt->fetch();
        $db->closeConnection();

        return true; 
    }   
    
    

    
    /**
     * Verifica se questionario possui blocos Padrao: Negocios e Empreendedorismo
     * 
     * @param type $questionnaire_id
     * @throws Exception
     */
    protected function validaBlocosQuestionario($questionnaire_id)
    {
        $blocks = $this->Questionnaire->getBlocksAutoavaliacao($questionnaire_id);
            
         //caso Bloco do questionario nao exista
        if (!$blocks) {
            //$urldevolutiva = $this->printAvisoPdfDevolutivaCasoNaoHajaBlocoQuestionario($arrBlocksResult);
            //return $urldevolutiva;
            throw new Exception($this->_messagesError['blocksNotExists']);                        
        }           
  
        //bloco 1 do questionario
        //$negociosBlockId = $blocks[0];        
        $negociosBlockId = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;

        //bloco 2 do questionario
        //$enterpreneurBlockId = $blocks[1];
        $enterpreneurBlockId = Zend_Registry::get('configDb')->qstn->currentBlockIdEmpreendedorismo;
        
        //seta blocos para uso na classe que processara a devolutiva
        $this->Devolutive->setBlockIdNegocios($negociosBlockId);
        $this->Devolutive->setBlockIdEmpreendedorismo($enterpreneurBlockId);        
    }    
    

    /**
     * metodo para recuperar tudo na z
     * 
     * @param type $questionnaire_id
     * @return boolean
     */
    protected  function recuperaValidaQuestionario($questionnaire_id)
    {
        $return = true;
        
        //objeto Questionario
        $objQuestionnaire = $this->Questionnaire->getQuestionnaireById($questionnaire_id);
        
        //tipo questionario
        $this->devolutive_id = $objQuestionnaire->getDevolutiveCalcId();
        
        if (!$questionnaire_id || !$objQuestionnaire) {

            $return = false;
        }
        
        return $return;
    }
    
    
    
    /**
     * 
     * @param type $questionnaire_id
     * @param type $user_id
     * @throws Exception
     */
    protected function verificaQuestoesRespondidas($questionnaire_id, $user_id)
    {
        /**
         * verifica se todas as questoes foram respondidas
         * É um requisito para que a devolutiva seja gerada
         */
        $return = true;
        $currentBlockIdNegocios = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
        $questoesRespondidas =$this->Questionnaire->isFullyAnswered($questionnaire_id, $user_id, $currentBlockIdNegocios);
        
        if (!$questoesRespondidas) {
            $return = false;
        }              
        
        return $return;
    }
    
    
    
}//end class
