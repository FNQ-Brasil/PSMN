<?php
/**
 * Devolutive_Question
 * @uses  
 */

class Model_DevolutiveVerificador
{

    const QUESTIONNAIRE_ID_PSMN_COMPLETO = 52; // Zend_Registry::get('configDb')->competitionId;
    const BLOCO_EMPREENDEDORISMO = 61;
    const BLOCO_NEGOCIOS = 63; // Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;

    public $root_path;
    protected $_messagesError = array(
        'questionnaireNotExists' => 'Questionário inexistente.',
    );
    
    protected $questionnaireId;
    protected $userId;
    protected $devolutiveId; 
    protected $isRA;
    
    protected $arqPath;
    protected $dirName;
    protected $publicDir;
    protected $arqName;
    
    protected $blockIdNegocios;
    protected $blockIdEmpreendedorismo;
    
    protected $arrEnterprise;
    
    protected $arrContact;
    
    protected $arrIssues;
    
    protected  $arrDevolutiveGov;
    protected  $arrBlocksGov;
    protected  $arrCriteriaGov;
    protected  $arrQuestionnaire;
    
    protected  $arrDevolutiveGes;
    protected  $arrBlocksGes;
    protected  $arrCriteriaGes;

    
    protected  $protocoloIdDevolutiva;
    protected  $protocoloCreateAt;
    protected  $protocolo;
	protected  $userLogadoGerouDevolutiva;
	
	protected  $enterpriseKey;

    /** @var Model_Questionnaire **/
    public $Questionnaire;

    ### variaveis para Pontuacao

    protected $gravaPontuacaoNegocios;
    
    protected $arrPunctuationNeg;
  
    protected $arrPontuacaoObtida = array();

    /** @var Model_ExecutionPontuacao */
    protected $modelExecutionPontuacao;

    protected $startProcessamentoEmMassa;
    protected $ExecutionPontuacaoManager;

    public function __construct() {
        $this->root_path = Zend_Registry::get('config')->paths->root;
        $this->public_path = Zend_Registry::get('config')->paths->public;
		$this->programId = Zend_Registry::get('configDb')->competitionId;
        $this->tbUser = new DbTable_User();
        $this->Question = new Model_Question();
        $this->Execution = new Model_Execution();
        $this->Alternative = new Model_Alternative();
        $this->Eligibility = new Model_Eligibility();
        $this->Questionnaire = new Model_Questionnaire();
        $this->modelExecutionPontuacao = new Model_ExecutionPontuacao();
        $this->ExecutionPontuacaoManager = new Manager_ExecutionPontuacao();
		$this->userAuth = Zend_Auth::getInstance()->getIdentity();
    }
    
    
    
    /**
     * Este metodo é chamado do Controller da Devolutiva: DevolutiveController
     * 
     * Metodo que responde pelas regras negocio de geracao da devolutiva, pdf.
     * 
     * @return string (url da devolutiva)
     */
    public function makePdfDevolutive() 
    {
         
		// var_dump($this->public_path. "/");exit;
        //checa se devolutiva pdf ja existe
        $existsArchive = $this->devolutivaJaExiste();
   //  var_dump( $this->makePdfDevolutiveAutoAvaliacao);
		//$this->makePdfDevolutiveAutoAvaliacao($this->getQuestionnaireId(), $this->getUserId(), $this->getDirName(),$this->getPublicDir(),$this->getArqName(),$this->getIsRA());    
		
		//print_r($this->getArrayDevolutiveRAA(52,$this->getUserId()));               
 
		$this->Enterprise = new Model_Enterprise;
        $this->Appraiser = new Model_Appraiser; 
		$this->enterpriseRow = $this->enterpriseKey;
		$this->evaluationRow = $this->Appraiser->isCheckerPermit(
        $this->enterpriseRow->getId(), 
        $this->userAuth->getUserId(), 
        $this->programId
        );

//$this->public_path. "/";

        if ($existsArchive) {
			 try {
				list ($root, $pasta, $key,$arquivo) = split("/",$existsArchive);
				$extension_pos = strrpos($arquivo, '.');
				$fileName = substr($arquivo, 0, $extension_pos) . 'VR' . substr($arquivo, $extension_pos);
				
				$local = $this->public_path. "/" . $pasta . "/" .$key;
				
				if (!is_dir($local)) {
					mkdir($local);
					chmod($local,0777);
				}
				
				$existsArchive = "/" . $pasta . "/" .$key . "/" . $fileName;
			} catch (Excception $e) {}

 
            return $existsArchive;
        }

        //persiste dados da devolutiva
        $result = $this->persisteTipoDevolutiva();



        return $result;
        
    }
    
    
    /**
     * 
     * Persiste banco dados as informacoes da devolutiva gerada com base no DevolutiveID (tabela Questionaire)
     * 
     * @return mixed
     */
    public function persisteTipoDevolutiva(){
            
        //Quetionários de devolutiva tipo Autoavaliacao devem ter 2 blocos
        
        switch($this->getDevolutiveId()) {
            // case 1 = Tipo do questionario diagnostico.
            case 1 :
                //continue;
                $diagnostico = new Vtx_Devolutive_Tipo_Diagnostico($this);
                $result = $diagnostico->initTipo();
                return $result;
                break;
            // case 2 = Tipo do questionario autoavaliacao.
            case 2 :
                $autoavaliacao = new Vtx_Devolutive_Tipo_Autoavaliacao($this);
                $result = $autoavaliacao->initTipo();
                return $result;
                break;
            // case 3 = Tipo PSMN (Mulhere de Negocio)
            case 3 :
	 
                $psmn = new Vtx_Devolutive_Tipo_PSMN_PSMN($this);
                
                $result = $psmn->initTipo();
                
                return $result;
                break;
            default : 
                
                throw new Exception("Erro: questionario sem tipo (devolutiveCalcId)");
                
        } //end switch
        
        
    } //end function            


    /**
     * seta variaveis importantes para execucao metodos que gravam Pontuacao - tabela ExecutionPontuacao.
     */
    public function configuraGravaPontuacaoExecution($userId)
    {
        $questionnaireId = DbTable_Questionnaire::getInstance()->getQuestionnaireIdByCompetitionId(Zend_Registry::get('configDb')->competitionId);

        $this->setQuestionnaireId($questionnaireId);
        $this->setGravaPontuacaoNegocios(true);
        $this->setUserId($userId);
    }
    
    
    /**
     * utilizado para processar gravacao da pontuacao, em massa.
     * 
     * executado por :
     * //Ex: http://mpe-site-ambiente/questionnaire/devolutive/index/?limit=10&maiorque=96 
     * 
     * @param type $userId
     * 
     */
    public function processaPontuacaoBlocosDeUmQuestionario()
    {
        
        $userId = $this->getUserId();
        $questionnaireId = $this->getQuestionnaireId();

        if (!$questionnaireId) {
            $questionnaireId = DbTable_Questionnaire::getInstance()->getQuestionnaireIdByCompetitionId(Zend_Registry::get('configDb')->competitionId);
        }
   
        //processa Pontuacao para bloco Gestao Empresa
        if ($this->getGravaPontuacaoNegocios()) 
        {            
            
            $objExecution = $this->Execution->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);
            if ($objExecution) {
                $executionId = $objExecution->getId();                
            } else {
                $executionId = 0;
            }

            
            if (!is_null($executionId) && ($executionId > 0) ) 
            {
                $arrPunctuationNeg = array();
                $arrPunctuationDef = DbTable_Questionnaire::getInstance()
                    ->getPunctuationByCriterion($this->getQuestionnaireId(), Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios, $userId);
                
                foreach($arrPunctuationDef AS $punctuation) {
                    $arrPunctuationNeg[$punctuation->getDesignation()] = $punctuation->getNota();
                }

                $this->setArrPunctuationNeg($arrPunctuationNeg);

                //grava Pontuacao para Bloco Negocios, para este usuario.
                
                $IdPontuacaoExecution = $this->gravaPontuacaoParaBlocoNegocios($executionId);
                $this->printVariavelSeProcessamentoEmMassa( " --> IdNegociosExecutionPontuacao: [".$IdPontuacaoExecution."]");
                
            } else {
                $this->printVariavelSeProcessamentoEmMassa("N");
            }
        }
    }    
    
    
    /**
     * BLOCO Negocios
     * grava pontuacao de bloco Negocios, para cada cliente,
     * na tabela ExecutionPontuacao.
     * 
     */    
    public function gravaPontuacaoParaBlocoNegocios($executionId)
    {
        
        $result = 0;

        //variaveis setadas em 
        $booleanGrava = $this->getGravaPontuacaoNegocios(); 
        
        $arrPunctuationNeg = $this->getArrPunctuationNeg();
        
        $questionnaireId = $this->getQuestionnaireId();        
        
        $userId = $this->getUserId();
            
        $existsArchive = $this->Execution->getDevolutivePath($questionnaireId, $userId);         
        
       $currentBlockIdNegocios = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
        
        //grava se esta autorizado e Id_tabela_Execution com Path, existe.
        if ($booleanGrava && $existsArchive && count($arrPunctuationNeg) > 0) {

            $modelExecutionPontuacao = $this->modelExecutionPontuacao;
         
            //pontuacao execution
            $rowExecutionPontuacao = $modelExecutionPontuacao->getRowByExecutionId($executionId);
                   
            
            if (!isset($rowExecutionPontuacao) || is_null($rowExecutionPontuacao)) {
                $executionPontuacaoId = 0;
            } else {
                $executionPontuacaoId = $rowExecutionPontuacao->getId();
            }

            $data = array();

            $data['negociosTotal'] = $this->ExecutionPontuacaoManager->calculateExecutionScore($this->questionnaireId,
                $this->userId);
            
            if ($executionPontuacaoId) { //update
                //$erow = $modelExecutionPontuacao->updateExecutionPontuacao($executionId, $data, self::BLOCO_NEGOCIOS);
                $erow = $modelExecutionPontuacao->updateExecutionPontuacao($executionId, $data, $currentBlockIdNegocios);
                                
                $this->printVariavelSeProcessamentoEmMassa("-> Negocios Update ");
            } else { //insert
                $data['executionId'] = $executionId;
                
                //$erow = $modelExecutionPontuacao->createExecutionPontuacao($data, self::BLOCO_NEGOCIOS);
                $erow = $modelExecutionPontuacao->createExecutionPontuacao($data, $currentBlockIdNegocios);
                $this->printVariavelSeProcessamentoEmMassa( "-> Negocios Insert ");
            }

            if ($erow['status']) {
                $idExecutionPontuacao = $erow['row']->getId();
                $result = $idExecutionPontuacao;
            } else {
                $result = $erow['messageError'];
            }
        }        
        return $result;               
    }
    

    /**
     * printa string quando o processamento eh feito em massa.
     * @param string $string
     */
    public function printVariavelSeProcessamentoEmMassa($string)
    {
        if ($this->getStartProcessamentoEmMassa())
        {
            echo $string;
        }
    }    
    
    
    
    /**
     * 
     * @param type $userId
     * @return type
     */
    public function getEnterpriseData($userId)
    {
        
        $User = new Model_User();
        $President = new Model_President();
        $Enterprise = new Model_Enterprise();
        $UserLocality = new Model_UserLocality();
        $AddressEnterprise = new Model_AddressEnterprise();
        //$userId = 81;
        $userRow = $User->getUserById($userId);
        $userLocalityRow = $UserLocality
            ->getUserLocalityByUserId($userRow->getId());
        $enterpriseRow = $Enterprise
            ->getEnterpriseById($userLocalityRow->getEnterpriseId());
        $presidentRow = $President
            ->getPresidentByEnterpriseId($enterpriseRow->getId());
        $addressEnterpriseRow = $AddressEnterprise
            ->getAddressEnterpriseByEnterpriseId($enterpriseRow->getId());
        
        $arrAnnualRevenue = Vtx_Util_Array::annualRevenue();
        
        $neighborhood = $addressEnterpriseRow->findParentNeighborhood();
        $arrEnterprise = array(
            'Registro OCB'              => (($enterpriseRow->getOcbRegister() != '') ? 
                $enterpriseRow->getOcbRegister() : ''),
            'Razão Social'              => (($enterpriseRow->getSocialName() != '') ? 
                $enterpriseRow->getSocialName() : ''),
            'E-mail'                    => (($enterpriseRow->getEmailDefault() != '') ? 
                $enterpriseRow->getEmailDefault() : ''),
            'Nome Fantasia'             => (($enterpriseRow->getFantasyName() != '') ? 
                $enterpriseRow->getFantasyName() : ''),
            'Ramo de Atividade'         => (($enterpriseRow->getMetierId()) ? 
                DbTable_Metier::getInstance()->getById($enterpriseRow->getMetierId())->getDescription() : ''),
            'Atividade Econômica(CNAE)' => (($enterpriseRow->getCnae() != '') ? 
                $enterpriseRow->getCnae() : ''),
            'CPF/CNPJ'                  => (($enterpriseRow->getCnpj() != '') ? 
                Vtx_Util_Formatting::maskFormat($enterpriseRow->getCnpj(),'##.###.###/####-##') : ''),
            'Porte da Empresa'         => (($enterpriseRow->getAnnualRevenue() != '' && isset($arrAnnualRevenue[$enterpriseRow->getAnnualRevenue()])) ? 
                $arrAnnualRevenue[$enterpriseRow->getAnnualRevenue()] : ''),
            'Número de Colaboradores'   => (($enterpriseRow->getEmployeesQuantity() != '') ? 
                $enterpriseRow->getEmployeesQuantity() : ''),
            'Data de Abertura'          => (($enterpriseRow->getCreationDate() != '') ? 
                Vtx_Util_Date::format_dma($enterpriseRow->getCreationDate()) : ''),
            'Endereço'                  => (is_object($addressEnterpriseRow) ? 
                $addressEnterpriseRow->getStreetNameFull() : ''),
            'Número'                    => (is_object($addressEnterpriseRow) ? 
                $addressEnterpriseRow->getStreetNumber() : ''),
            'Complemento'               => (is_object($addressEnterpriseRow) ? 
                $addressEnterpriseRow->getStreetCompletion() : ''),
            'Bairro'                    => ($neighborhood? 
                $addressEnterpriseRow->findParentNeighborhood()->getName() : ''),
            'Cidade/Estado'             => (is_object($addressEnterpriseRow) ? 
                DbTable_City::getInstance()->getById($addressEnterpriseRow
                    ->getCityId())->getName()."/".DbTable_State::getInstance()
                    ->getById($addressEnterpriseRow->getStateId())->getUf() : ''),
            'CEP'                       => (is_object($addressEnterpriseRow) ? 
                Vtx_Util_Formatting::maskFormat($addressEnterpriseRow->getCep(),'#####-###') : ''),
        );
        
        
        
        $arrContact = array(
            'Nome'          => (is_object($presidentRow) ? $presidentRow->getName() : ''),
            'Cargo'         => (is_object($presidentRow) ? 'Presidente' : ''),
            'Telefone'      => (is_object($presidentRow) ? $presidentRow->getPhone() : ''),
            'E-mail'        => (is_object($presidentRow) ? $presidentRow->getEmail() : ''),
            'Cpf'           => Vtx_Util_Formatting::maskFormat($presidentRow->getCpf(), '###.###.###-##') 
        ); 
            
        $arrIssues = array(
            '0' => array('Q' => '1. É uma Matriz?', 'R' => (($enterpriseRow->getHeadOfficeStatus() == '1') ? 'Sim' : 'Não')),
            '1' => array('Q' => '2. É uma Singular?', 'R' => (($enterpriseRow->getSingularStatus() == '1') ? 'Sim' : 'Não')),
            '2' => array('Q' => '3. A empresa está vinculada a alguma Central?', 'R' => (($enterpriseRow->getCentralName() != '') ? $enterpriseRow->getCentralName() : 'Não')),
            '3' => array('Q' => '4. A empresa está vinculada a alguma Federação?', 'R' => (($enterpriseRow->getFederationName() != '') ? $enterpriseRow->getFederationName() : 'Não')),
            '4' => array('Q' => '5. A empresa está vinculada a alguma Confederação?', 'R' => (($enterpriseRow->getConfederationName() != '') ? $enterpriseRow->getConfederationName() : 'Não'))
        );
            
        return array($arrEnterprise, $arrContact, $arrIssues);
    }
    
	
	public function getArrayVerificacao($login=null)
    {
		if($login ==null) $userId = $this->userAuth->getUserId();
		else{
			$userRow = DbTable_User::getInstance()->fetchRow(array('Login = ?' => $login));
			if($userRow  ==null) return;
			$userId = $userRow->Id;
		}
		
 
		$this->evaluationRow = $this->Appraiser->isCheckerPermit(
			$this->enterpriseRow->getId(), 
			$userId, 
			$this->programId
        );
		

		
        $commentQuestions = $this->Appraiser->getQuestions();
        $evaluationQuestions = DbTable_QuestionChecker::getInstance()->fetchAll('QuestionTypeId = 7', 'Designation');
		
		
		//$this->evaluationRow->getAnswers()
        $V = array(
            'questoes' => $commentQuestions,
            'questionsAvaliacao' => $evaluationQuestions,
            'respostas' => $this->Appraiser->getEnterpriseScoreAppraiserAnwserVerificadorData($this->enterpriseRow->getId(),$this->userAuth->getUserId()),
            'commentAnswers' => $this->Appraiser->getApeEvaluationVerificadorComment($this->enterpriseRow->getId(),$this->userAuth->getUserId()),
            'conclusao' => $this->evaluationRow->getConclusao(),
        );
		// 
		

        return $V;

	}
	
	 public function getArrayReport()
    {	
		 $modelReport = new Model_EnterpriseReport;
 	      $AppraiserModel = new Model_Appraiser();
 		
		
        $V = array(
            'report' => $modelReport->getEnterpriseReportByEnterpriseIdKey($this->enterpriseKey->IdKey),
            'enterprise' => $this->enterpriseRow,
            'president' => $this->enterpriseRow->getPresidentRow(),
            'scores' => $this->Appraiser->getEnterpriseScoreAppraisersData($this->enterpriseRow->getId())
        );
		
	
		
		$data['enterprise_id'] = $this->enterpriseRow->getId();
        $data['user_id'] = $this->userAuth->getUserId();
        $data['programa_id'] = $this->programId;
        $data['tipo'] = 1;
		$data['status'] = "IIII";
		
		$objAppraiser = $AppraiserModel->setCheckerToEnterpriseVerificador($data);
 
		
		
	
	
       return $V;
		
    }
	
	
	 public function getArrayCriterioAvaliacao()
    {
		$this->evaluationRow = $this->Appraiser->isCheckerPermit(
        $this->enterpriseRow->getId(), 
        $this->userAuth->getUserId(), 
        $this->programId
        );
        $commentQuestions = $this->Appraiser->getQuestions();
        $evaluationQuestions = DbTable_QuestionChecker::getInstance()->fetchAll('QuestionTypeId = 7', 'Designation');

        $V = array(
            'questionsAvaliacao' => $evaluationQuestions,
            'respostas' => $this->evaluationRow->getAnswers(),
            'conclusao' => $this->evaluationRow->getConclusao(),
        );



       return $V;
		
	}
    public function getArrayDevolutiveRAA($questionnaireId, $userId, $blockId = null) 
    {   
        try {
            
            $arrDevolutiveRAA = array();
            $arrCriteria = array();
            $arrBlocks = array();
            $arrQuestionnaire = array();
            $arrRadarData = array();
            
            // Definições do Questionário
            $questionnaireDefs = $this->Questionnaire->getQuestionnaireById($questionnaireId);

            $arrQuestionnaire['title'] = $questionnaireDefs->getTitle();
            $arrQuestionnaire['description'] = $questionnaireDefs->getDescription();
            $arrQuestionnaire['long_description'] = $questionnaireDefs->getLongDescription();
            $arrQuestionnaire['operation_beginning'] = Vtx_Util_Date::format_dma($questionnaireDefs->getOperationBeginning());
            $arrQuestionnaire['operation_ending'] = Vtx_Util_Date::format_dma($questionnaireDefs->getOperationEnding());

            // Definições da Questão
            $questionsDefs = $this->Question->getAllByQuestionnaireIdBlockId($questionnaireId, $blockId);
           
			$this->Block = new Model_Block();
 			
            foreach ($questionsDefs as $question_def) {
                
                $idBlock = "";
                $idCriterion = "";
                   
                $questionId = $question_def->getId();
                $question_value = $question_def->getQuestao();
				
				$perguntas = $this->Block->cacheOrModelBlockById(63);


                // Grava a questão no array de devolutiva
                $arrDevolutiveRAA[$questionId]['designation'] = $question_def->getDesignacao();
                $arrDevolutiveRAA[$questionId]['value'] = $question_value;
                $arrDevolutiveRAA[$questionId]['text'] = $question_def->getTexto();
				 $arrDevolutiveRAA[$questionId]['pergunta'] = $perguntas[$questionId]["QuestionSummary"];
                
                // Verifica se existe Bloco válido e grava nos arrays de blocos e devolutiva
                $idBlock = $question_def->getBloco();
                if ($idBlock != "" && $idBlock != 0) {
                    $arrBlocks[$idBlock] = $question_def->getBlocoTitulo();
                    $arrDevolutiveRAA[$questionId]['block'] = $question_def->getBloco();
                }
                
                // Verifica se existe Critério válido e grava nos arrays de critérios e devolutiva
                $idCriterion = $question_def->getCriterio();
                if ($idCriterion != "" && $idCriterion != 0) {
                    $arrCriteria[$idCriterion] =  $question_def->getCriterioTitulo();
                    $arrDevolutiveRAA[$questionId]['criterion'] = $question_def->getCriterio();
                }
                
                $isAnswered = $this->Question->isAnsweredByEnterprise($questionId,$userId);
				
				$isAnsweredVerificador = $this->Question->isAnsweredByVerificador($questionId,$this->userAuth->getUserId(),$userId);
				

				//var_dump(array_key_exists("objAnswered",$isAnsweredVerificador));exit;
//var_dump($userId);
			   /// print_r($this->Questionnaire->getQuestionsAnsweredByUserId($questionnaireId,43005, $blockId));
				//exit;

		
                if($isAnswered['status']) {
                    // Recupera a resposta escrita
                    $answer = $this->Question->getQuestionAnswer($questionId,$userId);
					if(!array_key_exists("objAnswered",$isAnsweredVerificador)){
                    	$alternative_id = $answer['alternative_id'];
						$anwser_value = (isset($answer['answer_value'])) ? $answer['answer_value'] : "";
					}	
					else{
			 
						$alternative_id = $isAnsweredVerificador["objAnswered"]['AlternativeId'];
						$anwser_value = (isset($isAnsweredVerificador["objAnswered"]['AnswerValue'])) ? $isAnsweredVerificador["objAnswered"]['AnswerValue'] : "";
					}
					
					
					
                    $arrDevolutiveRAA[$questionId]['alternative_id'] = $alternative_id;
                    $arrDevolutiveRAA[$questionId]['write_answer'] = $anwser_value;
                    
                    if (count($answer['annual_result']) > 0) {
                        $arrDevolutiveRAA[$questionId]['annual_result'] = $answer['annual_result'];
                        $arrDevolutiveRAA[$questionId]['annual_result_unit'] = $answer['annual_result_unit'];
                    } else {
                        $arrDevolutiveRAA[$questionId]['annual_result'] = "";
                        $arrDevolutiveRAA[$questionId]['annual_result_unit'] = "";
                    }
                    
                    // Recupera o feedback da alternativa escolhida
                    $alternative =  $this->Alternative->getAlternativeById($alternative_id);
                    $arrDevolutiveRAA[$questionId]['alternative_designation'] = $alternative->getDesignation();
                    $arrDevolutiveRAA[$questionId]['alternative_feedback'] = $alternative->getFeedbackDefault();
                    
                    // Recupera o 'Pontos Fortes' do avaliador da resolução da questão 
                    $arrDevolutiveRAA[$questionId]['answer_feedback'] = $this->Question->getAnswerFeedback($isAnswered['objAnswered']->getAnswerId());
                  
                    // Recupera o 'Oportunidades de melhoria' do avaliador da resolução da questão 
                    $arrDevolutiveRAA[$questionId]['answer_feedback_improve'] = $this->Question->getAnswerFeedbackImprove($isAnswered['objAnswered']->getAnswerId());
                                        
                }    
                // Recupera as alternativas da questão
                $alternativesDefs =  $this->Alternative->getAllByQuestionId($questionId);	  
	  
				try {
					foreach ($alternativesDefs as $alternative_def) {
					$arr_alternative[$alternative_def['Designation'] ] = $alternative_def;
					$arrDevolutiveRAA[$questionId]['alternatives'] = $arr_alternative;
				}}catch (Excception $e) {}

            }

            return array($arrDevolutiveRAA, $arrBlocks, $arrCriteria, $arrQuestionnaire);
                
        } catch (Vtx_UserException $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
        exit;
    }
    
    /**
     * Faz calculo de score para devolutiva de autoavaliacao e outras
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @return mixed
     */
    public function makeScoreRAA($questionnaireId, $userId)
    {    
        //retornar Id dos blocos de um questionario
        $arrBlocksResult = $this->Questionnaire->getBlocksAutoavaliacao($questionnaireId);
        
        //necessario retornar 2 blocos: Governanca e Gestao
        if ($arrBlocksResult) {
            
            $governancaBlockId = $arrBlocksResult[0];
            $gestaoBlockId = $arrBlocksResult[1];
            
            // questionario, usuario, primeiro bloco
            $arrDataTab1 = $this->Questionnaire->getQuestionsPunctuationByBlock($questionnaireId, $userId, $governancaBlockId);

            $scorePart1 = 0;
            foreach($arrDataTab1 AS $dataTab1) {
                $scorePart1 = $scorePart1 + $dataTab1->getPontos();
            } 
            
            // questionario, usuario, segundo bloco
            $arrDataTabs2 = $this->Questionnaire->getQuestionsPunctuationByBlock($questionnaireId, $userId, $gestaoBlockId);

            $scorePart2 = 0;
            foreach($arrDataTabs2 AS $dataTabs2) {
                $scorePart2 = $scorePart2 + $dataTabs2->getPontos();
            } 
            
            $finalScore = ($scorePart1*0.25)+($scorePart2*0.75);
            return array($scorePart1, $scorePart2, $finalScore);
        }
        return false;
    }
    
    
    /**
     * checa se devolutiva ja foi gravada
     * 
     * @return type
     */
    public function devolutivaJaExiste()
    {
        //recupera url se arquivo existir e nao altero
        $existsArchive = $this->Execution->getDevolutivePath( $this->getQuestionnaireId(), $this->getUserId() );
        return $existsArchive;
    }

    /**
     * Monta pdf com FPDF
     * 
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @param type $dirName
     * @param type $publicDir
     * @param type $arqName
     * @param type $isRA
     * @return type
     */
    public function makePdfDevolutiveAllBlocks($questionnaireId, $userId, $dirName, $publicDir, $arqName, $isRA = false) 
    {
        list($arrEnterprise, $arrContact, $arrIssues) 
            = $this->getEnterpriseData($userId);
        
        list($arrDevolutive, $arrBlocks, $arrCriteria, $arrQuestionnaire) 
            = $this->getArrayDevolutiveRAA($questionnaireId, $userId);
        
        ($isRA)  
            ? $arrHeader['title'] = 'Relatório de Avaliação'
            : $arrHeader['title'] = 'Questionário de Diagnóstico';
        
        $pdf = new Model_DevolutiveRAA($arrHeader, $isRA);
        
        // Desabilita header e footer
        $pdf->header = 0;
        $pdf->footer = 0;
        
        // Prepara variáveis para paginação
        $pdf->AliasNbPages();
        
        // Capa
        $pdf->FirstPage($arrQuestionnaire);
        
        // Habilita header e footer
        $pdf->header = 1;
        $pdf->AddPage();
        $pdf->footer = 1;
        
        // Mensagem de apresentação
        //$pdf->Presentation();
        
        // Dados Cadastrais do avaliado
        $pdf->EnterpriseData($arrEnterprise, $arrContact, $arrIssues);
        
        $pdf->addPage();
        
        // Comentários gerados a partir das respostas Parte I
        $offSet = $pdf->Devolutive($arrDevolutive, $arrBlocks, $arrCriteria, 0);
        
        // Renderização do arquivo PDF
        $pdf->Output($dirName.$arqName);
        
        // Configura as permissões do arquivo
        chmod($dirName.$arqName,0777);
        
        return $publicDir.$arqName;
    }
    
    /**
     * 
     * Printa pdf usando FPDF
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @param type $dirName
     * @param type $publicDir
     * @param type $arqName
     * @param type $isRA
     * @return type
     */
    public function makePdfDevolutiveAutoAvaliacao($questionnaireId, $userId, $dirName, $publicDir, $arqName, $isRA = false) 
    {
 
        $arrBlocksResult = $this->Questionnaire->getBlocksAutoavaliacao($questionnaireId);
 
        if ($arrBlocksResult) {
            
            $governancaBlockId = $arrBlocksResult[0];
            $gestaoBlockId = 0;
        
            list($arrEnterprise, $arrContact, $arrIssues) 
                = $this->getEnterpriseData($userId);

            list($arrDevolutiveGov, $arrBlocksGov, $arrCriteriaGov, $arrQuestionnaire) 
                = $this->getArrayDevolutiveRAA($questionnaireId, $userId, $governancaBlockId);

            list($arrDevolutiveGes, $arrBlocksGes, $arrCriteriaGes, $arrQuestionnaire) 
                = $this->getArrayDevolutiveRAA($questionnaireId, $userId, $gestaoBlockId);

            list($arrRadarDataGes, $arrTabulationGes, $arrPunctuationGes) 
                = $this->Questionnaire->getRadarData($questionnaireId, $gestaoBlockId, $userId);

            $strPathRadar = $this->makeRadarPlot($arrCriteriaGes, $arrRadarDataGes, $arrTabulationGes, $arrPunctuationGes, $dirName);

            $arrScores = $this->makeScoreRAA($questionnaireId, $userId);
            $scorePart1 = $arrScores[0];
            $scorePart2 = $arrScores[1];

            require_once(APPLICATION_PATH.'/models/DevolutiveRAA.php');

            ($isRA)  
            ? $arrHeader['title'] = 'Relatório de Avaliação'
            : $arrHeader['title'] = 'Questionário de Autoavaliação';
        
            $arrHeader['title'] = "Relatório de Avaliação";

            $pdf = new Model_DevolutiveRAA($arrHeader, $isRA);

            // Desabilita header e footer
            $pdf->header = 0;
            $pdf->footer = 0;

            // Prepara variáveis para paginação
            $pdf->AliasNbPages();

            // Capa
            $pdf->FirstPage($arrQuestionnaire);

            // Habilita header e footer
            $pdf->header = 1;
            $pdf->AddPage();
            $pdf->footer = 1;

            // Mensagem de apresentação
            //$pdf->Presentation();

            // Dados Cadastrais do avaliado
            $pdf->EnterpriseData($arrEnterprise, $arrContact, $arrIssues);

            // Primeira parte da Devolutiva
            $pdf->Model();

            // Primeira parte da Devolutiva
            $pdf->FirstPart($scorePart1);

            // Comentários gerados a partir das respostas Parte I
            $offSet = $pdf->Devolutive($arrDevolutiveGov, $arrBlocksGov, $arrCriteriaGov, 0);

            // Segunda parte da Devolutiva
            $pdf->SecondPart($arrCriteriaGes, $offSet, $strPathRadar, $scorePart1, $scorePart2);

            // Comentários gerados a partir das respostas Parte II
            $pdf->Devolutive($arrDevolutiveGes, $arrBlocksGes, $arrCriteriaGes, $offSet);

            // Renderização do arquivo PDF
            $pdf->Output($dirName.$arqName);

            // Configura as permissões do arquivo
            chmod($dirName.$arqName,0666);

            if ($strPathRadar) {
                // Remove o arquivo temporário do radar
                unlink($dirName.'radarTMP.png');
            }

            // Envia o e-mail com o link da devolutiva para download.
            $userName = $this->modelUser->getUserById($userId)->getName();
            $link = $_SERVER['HTTP_ORIGIN'].''.$publicDir.$arqName;

            $this->createDevolutiveNotification($arrEnterprise['E-mail'], $userName, $link);
        }
        else {
            
            require_once(APPLICATION_PATH.'/models/DevolutiveRAA.php');

            $arrHeader['title'] = "Relatório de Autoavaliação";

            $pdf = new Model_DevolutiveRAA($arrHeader);

            // Desabilita header e footer
            $pdf->header = 0;
            $pdf->footer = 0;

            // Prepara variáveis para paginação
            $pdf->AliasNbPages();

            // Habilita header e footer
            $pdf->header = 1;
            $pdf->AddPage();
            $pdf->footer = 1;

            $pdf->SetFont('Arial','BI',16);
            $pdf->SetTextColor(255,0,0);
            $pdf->MultiCell(190,13,utf8_decode("Faltando o cadastro dos blocos Governança ou Gestão"),0,"C");
        
            // Renderização do arquivo PDF
            $pdf->Output($dirName.$arqName);

            // Configura as permissões do arquivo
            chmod($dirName.$arqName,0666);

        }

        return $publicDir.$arqName;
    }

    private function createDevolutiveNotification($to, $userName, $link){
        $context = 'devolutive_notification';
        $searches = array(':date',':name',':link');
        $replaces = array(date("d/m/Y"), $userName, $link);
        $recipients = array($to);

        return Manager_EmailMessage::createByEmailDefinitionWithRecipients($context, $searches, $replaces, $recipients);
    }
    
    /**
     * 
     * Utiliza lib jpgraph
     * 
     * @param type $arrCriteria
     * @param type $arrRadarData
     * @param type $arrTabulation
     * @param type $arrPunctuation
     * @param type $dirName
     * @return boolean|string
     */
    public function makeRadarPlot($arrCriteria, $arrRadarData, $arrTabulation, $arrPunctuation, $dirName) 
    {
        // content="text/plain; charset=utf-8"       
        require_once (APPLICATION_PATH_LIBS . '/jpgraph/src/jpgraph.php');
        require_once (APPLICATION_PATH_LIBS . '/jpgraph/src/jpgraph_radar.php');
        
        $criterios = array();
        foreach($arrCriteria AS $chave => $valor) {
            $criterios[$chave] = utf8_decode(" ".$chave." - ".$valor);
        }
        
        if(!is_array($arrRadarData)) {
            return false;
        }

        $titles = array_values($criterios);
        $data = array_values($arrRadarData);
            
        $graph = new RadarGraph (635,355); 
        $graph->SetShadow();
        $graph->SetScale('lin', $aYMin=0, $aYMax=100);
        $graph->yscale->ticks->Set(50,10);
        
        $graph->title->Set("Porcentagem de acertos por Critério");
        $graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);

        //$graph->subtitle->Set("Pontuação por Critério em %");
        //$graph->subtitle->SetFont(FF_VERDANA,FS_ITALIC,10);
        
        $graph->SetTitles($titles);
        $graph->SetCenter(0.50,0.54);
        //$graph->HideTickMarks(); 
        $graph->ShowMinorTickMArks();
        $graph->SetColor('white');
        $graph->grid->SetLineStyle('dashed');
        $graph->axis->SetColor('darkgray@0.3'); 
        $graph->grid->SetColor('darkgray@0.3');
        $graph->grid->Show();
        
        $graph->SetGridDepth(DEPTH_BACK);

        $plot = new RadarPlot($data);
        $plot->SetColor('red@0.2');
        $plot->SetLineWeight(3);
        $plot->SetFillColor('skyblue4@0.7');
        $graph->Add($plot);
        
        $radarPath = $dirName."radarTMP.png";
        
        $graph->Stroke($radarPath);
        
        return $radarPath;
        
    }

    public function generateMassDevolutive($limit, $enterpriseProgramaIdMaiorQue, $QUEM_FARA_PROCESSAMENTO)
    {
        echo "<small><br>EnterpriseCategoryAwardCompetitionMaiorQue: ".$enterpriseProgramaIdMaiorQue;
        echo "<br>";

        //join com tb Execution
        $res = $this->$tbUser->getUserByLimitAndIdMaiorJoinExecution($enterpriseProgramaIdMaiorQue, $limit);

        echo "<br><br>";
        foreach ($res as $campo)
        {
            $userId = $campo['UserId'];
            $enterpriseProgramaId = 2014;

            echo "enterpriseProgramaId: " .$enterpriseProgramaId;
            echo " - UserId: " .$userId;
            echo " - ";

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
    }
    

    public function getArqPath() {
        return $this->arqPath;
    }

    public function setArqPath($arqPath) {
        $this->arqPath = $arqPath;
    }

    public function getDirName() {
        return $this->dirName;
    }

    public function setDirName($dirName) {
        $this->dirName = $dirName;
    }

    public function getPublicDir() {
        return $this->publicDir;
    }

    public function setPublicDir($publicDir) {
        $this->publicDir = $publicDir;
    }

    public function setEnterpriseKey($enterpriseKey) {
        $this->enterpriseKey = $enterpriseKey;
    }

    public function getArqName() {
        return $this->arqName;
    }

    public function setArqName($arqName) {
        $this->arqName = $arqName;
    }

    public function getQuestionnaireId() {
        return $this->questionnaireId;
    }

    public function setQuestionnaireId($questionnaireId) {
        $this->questionnaireId = $questionnaireId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getDevolutiveId() {
        return $this->devolutiveId;
    }

    public function setDevolutiveId($devolutiveId) {
        $this->devolutiveId = $devolutiveId;
    }

    public function getIsRA() {
        return $this->isRA;
    }

    public function setIsRA($isRA = false) {
        $this->isRA = $isRA;
    }

    public function getAllDevolutiveTypes() 
    {
        return DbTable_DevolutiveCalc::getInstance()->fetchAll();
    }
    
    public function getBlockIdNegocios() {
        return $this->blockIdNegocios;
    }

    public function setBlockIdNegocios($blockId) {
        $this->blockIdNegocios = $blockId;
    }

    public function getBlockIdEmpreendedorismo() {
        return $this->blockIdEmpreendedorismo;
    }

    public function setBlockIdEmpreendedorismo($blockId) {
        $this->blockIdEmpreendedorismo = $blockId;
        
    }

    public function getArrEnterprise() {
        return $this->arrEnterprise;
    }

    public function setArrEnterprise($arrEnterprise) {
        $this->arrEnterprise = $arrEnterprise;
    }   
    
    public function getArrContact() {
        return $this->arrContact;
    }

    public function setArrContact($arrContact) {
        $this->arrContact = $arrContact;
    }

    public function getArrIssues() {
        return $this->arrIssues;
    }

    public function setArrIssues($arrIssues) {
        $this->arrIssues = $arrIssues;
    }

    public function getArrDevolutiveGov() {
        return $this->arrDevolutiveGov;
    }

    public function setArrDevolutiveGov($arrDevolutiveGov) {
        $this->arrDevolutiveGov = $arrDevolutiveGov;
    }

    public function getArrBlocksGov() {
        return $this->arrBlocksGov;
    }

    public function setArrBlocksGov($arrBlocksGov) {
        $this->arrBlocksGov = $arrBlocksGov;
    }

    public function getArrCriteriaGov() {
        return $this->arrCriteriaGov;
    }

    public function setArrCriteriaGov($arrCriteriaGov) {
        $this->arrCriteriaGov = $arrCriteriaGov;
    }

    public function getArrQuestionnaire() {
        return $this->arrQuestionnaire;
    }

    public function setArrQuestionnaire($arrQuestionnaire) {
        $this->arrQuestionnaire = $arrQuestionnaire;
    }

    public function getArrDevolutiveGes() {
        return $this->arrDevolutiveGes;
    }

    public function setArrDevolutiveGes($arrDevolutiveGes) {
        $this->arrDevolutiveGes = $arrDevolutiveGes;
    }

    public function getArrBlocksGes() {
        return $this->arrBlocksGes;
    }

    public function setArrBlocksGes($arrBlocksGes) {
        $this->arrBlocksGes = $arrBlocksGes;
    }

    public function getArrCriteriaGes() {
        return $this->arrCriteriaGes;
    }

    public function setArrCriteriaGes($arrCriteriaGes) {
        $this->arrCriteriaGes = $arrCriteriaGes;
    }
   
    
    public function getProtocoloIdDevolutiva() {
        return $this->protocoloIdDevolutiva;
    }

    public function setProtocoloIdDevolutiva($protocoloIdDevolutiva) {
        $this->protocoloIdDevolutiva = $protocoloIdDevolutiva;
    }

    public function getProtocoloCreateAt() {
        return $this->protocoloCreateAt;
    }

    public function setProtocoloCreateAt($protocoloCreateAts) {
        $this->protocoloCreateAt = $protocoloCreateAts;
    }

    public function getProtocolo() {
        return $this->protocolo;
    }

    public function setProtocolo($protocolos) {
        $this->protocolo = $protocolos;
    }
	
	public function getUserLogadoGerouDevolutiva() {
        return $this->userLogadoGerouDevolutiva;
    }

    public function setUserLogadoGerouDevolutiva($userLogadoGerouDevolutivas) {
        $this->userLogadoGerouDevolutiva = $userLogadoGerouDevolutivas;
    }

    public function getGravaPontuacaoNegocios() {
        return $this->gravaPontuacaoNegocios;
    }

    public function setGravaPontuacaoNegocios($gravaPontuacaoNegocios) {
        $this->gravaPontuacaoNegocios = $gravaPontuacaoNegocios;
    }

    public function getArrPunctuationNeg() {
        return $this->arrPunctuationNeg;
    }

    public function setArrPunctuationNeg($arrPunctuationNeg) {
        $this->arrPunctuationNeg = $arrPunctuationNeg;
    }

    public function getArrPontuacaoObtida() {
        return $this->arrPontuacaoObtida;
    }

    public function setArrPontuacaoObtida($arrPontuacaoObtida) {
        $this->arrPontuacaoObtida = $arrPontuacaoObtida;
    }

    public function getStartProcessamentoEmMassa() {
        return $this->startProcessamentoEmMassa;
    }

    public function setStartProcessamentoEmMassa($startProcessamentoEmMassa) {
        $this->startProcessamentoEmMassa = $startProcessamentoEmMassa;
    }

        
   
}
   