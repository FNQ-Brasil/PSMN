<?php

/**
 * Classe responsavel pela regras de geração da devolutiva de PSMN.
 * 
 * IMPORTANTE:
 * Informar const TIPO_QUESTIONARIO e COMPETITION_ID
 * 
 * @depends
 * FPDF
 * FPDI
 *
 * refatorado de Model_Devolutive
 * @author esilva
 */
class Vtx_Devolutive_Tipo_PSMN_PSMN {

    const TIPO_QUESTIONARIO = 3; //PSMN

    //const COMPETITION_ID = 2013;

    /** @var Model_Execution $execution * */
    protected $execution;

    /** @var Model_Devolutive $devolutive * */
    protected $devolutive;
    protected $makepdf;
    protected $grade;
    protected $graficoPontuacao;
    protected $competitionId;
    protected $tipoQuestionario;

    protected $modelUser;

    /**
     * faz chamadas obrigatorias para funcionamento e geracao da devolutiva
     * 
     * @param Model_Devolutive $devolutive
     */
    public function __construct(Model_Devolutive $devolutive) {
        $this->devolutive = $devolutive;

        $this->loadClasses();

        $this->setVariaveisMakePdf();

        $this->modelUser = new Model_User();
    }

    /**
     * instancia classes necessarios para funcionamento da geracao devolutiva
     */
    public function loadClasses() {
        $this->execution = new Model_Execution();

        $this->tbUser = new DbTable_User();
        $this->question = new Model_Question();

        $this->alternative = new Model_Alternative();
        $this->questionnaire = new Model_Questionnaire();

        /** @var Vtx_Devolutive_MakePdf makepdf * */
        $this->makepdf = new Vtx_Devolutive_MakePdf($this->devolutive);
    }

    /**
     * seta variaveis necessarios para funcionamento da geracao devolutiva
     */
    public function setVariaveisMakePdf() {
        $competitionId = Zend_Registry::get('configDb')->competitionId;
        $this->setCompetitionId($competitionId);

        $this->setTipoQuestionario(self::TIPO_QUESTIONARIO);
        //importante para Capa do pdf
        $this->makepdf->setTipoQuestionario(self::TIPO_QUESTIONARIO);

        //seta graficoPontuacao
        $this->setGraficoPontuacao(true);

        //desabilita grafico radar
        //legado
        $this->makepdf->setStrPathRadar(false);

        //seta logotipos para devolutiva pdf
        $this->makepdf->setLogotipoEmpresa('/img/logo_psmn.png');
        //$this->makepdf->setLogotipoQualidade('/img/fnq.jpg');
        //conteudo .ini
        $this->makepdf->setConteudoIni('/Vtx/Devolutive/Conteudo/psmn.ini'); //premium-libs
        $this->makepdf->setTexts();

        //rodape(footer)

        $this->makepdf->setImagemFooter('/img/rodape.png');
        $this->makepdf->setEmissao_data("Emissão:  " . date('d/m/Y  H:i:s'));

        //titulo pagina Dados Empresa
        $this->makepdf->setTxtCadastroEmpresa("Dados da Empresa");
        $this->makepdf->setTxtContatoEmpresa("Dados do Contato da Empresa");
        $this->makepdf->setTxtResCadastroEmpresa("Respostas do cadastro da Empresa");

        $this->makepdf->setCapaAvaliacao('capa_devolutiva.pdf');
        $this->makepdf->setCapaDiagnostico('capa_devolutiva.pdf');

        //arquivos pdf parte 2
        $arrP2 = array(
            /*
              'p5' => 'Devolutiva_p5.pdf',
              'p6' => 'Devolutiva_p6.pdf',
              'p7' => 'Devolutiva_p7.pdf',
              'p8' => 'Devolutiva_p8.pdf',
              'p9' => 'Devolutiva_p9.pdf',
             */
            'p10' => 'Devolutiva_p10.pdf'
        );
        $this->makepdf->setArquivoPdfDevolutiveParte2($arrP2);

        $this->makepdf->setIsRA($this->devolutive->getIsRA());

        //titulos e subtitulos para a devolutiva
        $arrHeader = array();

        $tituloCapa = "DEVOLUTIVA AUTOAVALIAÇÃO DO NEGÓCIO";
        $tituloHeader = "";

        $subTituloCapa = "Programa SEBRAE - Mulheres de Negocio";

        $arrHeader['title'] = $tituloHeader;

        $this->makepdf->setTituloCapa($tituloCapa);

        $this->makepdf->setSubTituloCapa($subTituloCapa);

        $this->makepdf->setArrHeader($arrHeader);

        $this->makepdf->setHeaderTitle($arrHeader['title']);
    }

    // Rodapé
    public function Footer() {
        if ($this->PageNo() !== 1) {

            //print abaixo eh somente para pag. 3 da devolutiva
            if (($this->PageNo() == 2)) {

                $this->SetFont('Arial', 'BI', 10);
                //printa num. pagina a esquerda da pagina
                $this->SetXY(9, -17);

                $numProtocolo = $this->devolutive->getProtocolo();
                $protocoloCreateAt = $this->devolutive->getProtocoloCreateAt();

                $textoProtocoloDevolutiva = "(PROTOCOLO " . $numProtocolo . utf8_decode(" às ") . $protocoloCreateAt . ")";

                $this->Cell(8, 10, $textoProtocoloDevolutiva, 0, 0, 'L');
            } // only page 2            
            else {

                $footer = new Vtx_Devolutive_Tipo_PSMN_FooterPdf($this->makepdf);
            }
        }
    }

    /**
     * Devolutiva PSMN
     * 
     * DevolutiveCalcId 3 => Tipo de questionario PSMN
     *  
     * Tipo 3 foi baseado no questionario autoavaliacao (tipo 2).
     */
    public function initTipo() {
        $result = false;
        
        //cria diretorio no filesystem, para gravar pdf
        $this->makepdf->preparaFileSystemParaDevolutiva();

        //regra desnecessaria para geracao devolutiva de PSMN
        //$arrScore = $this->devolutive->makeScoreRAA( $this->devolutive->getQuestionnaireId(), $this->devolutive->getUserId() );
        //if ( $arrScore ) {
        //grava dados em Execution
        $this->execution->finishExecution($this->devolutive->getQuestionnaireId(), $this->devolutive->getUserId(), $this->devolutive->getArqPath(),
                //$arrScore[2], 
                null, $this->devolutive->getIsRA()
        );

        //faz geracao do pdf
        $result = true;
        
        $result = $this->makePdfDevolutivePSMN();

        return $result;
    }

    /**
     * regras para geracao do pdf da devolutiva
     * 
     * 
     * anteriormente:
     * //public function makePdfDevolutivePSMN ($questionnaireId, $userId, $dirName, $publicDir, $arqName, $isRA = false) 
     * 
     * @return string (url da devolutiva gerada)
     */
    public function makePdfDevolutivePSMN() {
        //carrega dados para utilizar na devolutiva/evalutation PDF
        $this->arrayListTodosDadosParaGeracaoDevolutiva();

        //////classe com lib FPDF///////////
        // Prepara variáveis para paginação
        $this->makepdf->AliasNbPages();

        //nova capa
        $this->makepdf->Capa(); # tipoQuestionario            
        // First page , apos capa
        //$this->makepdf->FirstPage( $this->getArrQuestionnaire() );
        // Mensagem de apresentação
        //$this->makepdf->Presentation();
        //apresentacao PSMN
        new Vtx_Devolutive_Tipo_PSMN_ApresentacaoPsmnPdf($this->makepdf, $this->devolutive);
        
        // Dados Cadastrais do avaliado
        //$this->makepdf->EnterpriseData();        
        //// Primeira parte
        ////Somente texto - psmn.ini
        //$pdf->introducaoModel();
        //// Primeira parte da Devolutiva
        //$pdf->introducaoTextoFirstPart($scorePart1=0);
        // negocios
        $this->makepdf->setOffset(0);

        // Comentários gerados a partir das respostas Parte I, bloco 1 do questionario
        //$offSet = $this->makepdf->ConteudoBlocoDevolutive($arr1, $arr2, $arr3); 
        // negocios
        $offSet = new Vtx_Devolutive_Tipo_PSMN_ConteudoBlocoNegociosPdf($this->makepdf, $this->devolutive);
        
        //// Segunda parte da Devolutiva, Conteudo do bloco 2, do questionario
        //$pdf->SecondPart($arrCriteriaGes, $offSet, $this->makepdf->getStrPathRadar() , $scorePart1=0, $scorePart2=0);
        //empreendedorismo
        //$this->makepdf->setOffset(0);
//        $arr1 = $this->devolutive->getArrDevolutiveGes();
//        $arr2 = $this->devolutive->getArrBlocksGes();
//        $arr3 = $this->devolutive->getArrCriteriaGes();        
//        // Comentários gerados a partir das respostas Parte II
//        $this->makepdf->ConteudoBlocoDevolutive($arr1, $arr2, $arr3); 
        //devolutiva parte 2
        //empreendedorismo
        // new Vtx_Devolutive_Tipo_PSMN_ConteudoBlocoEmpreendedorismoPdf($this->makepdf, $this->devolutive);
        ####################################################    
        //Pontuacao de Caracteristica empreendedora    
        //retirado empreendedorismo
        //$pontuacao = new Vtx_Devolutive_Tipo_PSMN_PontuacaoCaracteristicaEmpreendedoraPdf($this->makepdf, $this->devolutive);    
        ####################################################    
        //retirado empreendedorismo
        $parte2 = new Vtx_Devolutive_Tipo_PSMN_DevolutiveParte2Pdf($this->makepdf);
        
        //faz renderizacao do arquivo PDF atraves da lib FPDF e FPDI
        $urldevolutiva = $this->renderizaPdf();

        $this->createDevolutiveEmail();

        //retorno do metodo -> URL final da devolutiva
        return $urldevolutiva;
    }

    public function createDevolutiveEmail(){
        $arrEnterprise = $this->devolutive->getArrEnterprise();
        $to = $arrEnterprise['E-mail'];

        if($to != null && $to != ''){
            $userName = $this->modelUser->getUserById($this->devolutive->getUserId())->getFirstName();
            $link = $_SERVER['HTTP_HOST'].'/'.$this->devolutive->getPublicDir().$this->devolutive->getArqName();
            $context = 'devolutive_notification';
            $searches = array(':date',':name',':link');
            $replaces = array(date('d/m/Y'),$userName,$link);
            $recipients = array($to);
            
            Manager_EmailMessage::createByEmailDefinitionWithRecipients($context,$searches,$replaces,$recipients);
        }
    }

    /**
     * carrega dados/array list de:
     * 
     * - questionario
     * - blocos de questionario
     * - contato da empresa
     * - criterios do questionario
     * 
     */
    protected function arrayListTodosDadosParaGeracaoDevolutiva() {
        /** PARTE 1 * */
        //bloco 1 do questionario
        $blockId_1 = $this->devolutive->getBlockIdNegocios();

        //bloco 2 do questionario
        $blockId_2 = $this->devolutive->getBlockIdEmpreendedorismo();

        //recupera todos os dados do usuario Empresa
        list($arrEnterprise, $arrContact, $arrIssues) = $this->devolutive->getEnterpriseData($this->devolutive->getUserId());

        // bloco 1
        //recupera dados completos do questionario, blocos, questoes, criterios e respostas
        list($arrDevolutiveGov, $arrBlocksGov, $arrCriteriaGov, $arrQuestionnaire) = $this->getArrayQuestionsAndAlternatives($this->devolutive->getQuestionnaireId(), $this->devolutive->getUserId(), $blockId_1);
        //getArrayDevolutiveRAA
        // bloco 2
        //recupera dados completos do questionario, blocos, questoes, criterios e respostas            
        list($arrDevolutiveGes, $arrBlocksGes, $arrCriteriaGes, $arrQuestionnaire) = $this->getArrayQuestionsAndAlternatives($this->devolutive->getQuestionnaireId(), $this->devolutive->getUserId(), $blockId_2);

        //define calculo do grafico de radar com base no IdBloco informado
        //list($arrRadarDataGes, $arrTabulationGes, $arrPunctuationGes) 
        //= $this->questionnaire->getRadarData($this->devolutive->getQuestionnaireId(), $gestaoBlockId, $this->devolutive->getUserId());

        /**
          //Porcentagem de acertos por criterio
          //nao utilizado no PSMN
          //$strPathRadar = $this->devolutive->makeRadarPlot($arrCriteriaGes, $arrRadarDataGes, $arrTabulationGes, $arrPunctuationGes, $this->devolutive->getDirName());

          //nao utilizado no PSMN
          //calcula scores de autoavaliacao -> PSMN nao usa
          $arrScores = $this->makeScoreRAA($questionnaireId, $userId);
          $scorePart1 = $arrScores[0];
          $scorePart2 = $arrScores[1];
         * */
        $this->devolutive->setArrEnterprise($arrEnterprise);
        $this->devolutive->setArrContact($arrContact);
        $this->devolutive->setArrIssues($arrIssues);
        $this->devolutive->setArrDevolutiveGov($arrDevolutiveGov);
        $this->devolutive->setArrBlocksGov($arrBlocksGov);
        $this->devolutive->setArrCriteriaGov($arrCriteriaGov);
        $this->devolutive->setArrQuestionnaire($arrQuestionnaire);
        $this->devolutive->setArrDevolutiveGes($arrDevolutiveGes);
        $this->devolutive->setArrBlocksGes($arrBlocksGes);
        $this->devolutive->setArrCriteriaGes($arrCriteriaGes);
    }

    /**
     * renderiza pdf e envia email para usuario
     * 
     * @return string $urldevolutiva
     */
    public function renderizaPdf() {
        $urlDevolutiva = $this->devolutive->getPublicDir() . $this->devolutive->getArqName();

        //echo "urldevolutiva: ".$urlDevolutiva; exit;
        // Renderização do arquivo PDF
        $this->makepdf->Output($this->devolutive->getDirName() . $this->devolutive->getArqName());

        // Configura as permissões do arquivo
        chmod($this->devolutive->getDirName() . $this->devolutive->getArqName(), 0666);

        //disparo email com URL da devolutiva gerada
        $this->emailClienteSobreDevolutiva();



        return $urlDevolutiva;
    }

    /**
     * Envia email apos a geracao do pdf (devolutive, evaluation)
     * 
     */
    public function emailClienteSobreDevolutiva() {
        // Envia o e-mail com o link da devolutiva para download.
        //$to = $arrEnterprise['E-mail'];

        $to = $this->devolutive->getArrEnterprise();
        $to = $to['E-mail'];
        $from = 'psmn@vorttex.com.br';
        $subject = 'Obrigado por participar do Questionário';
        $message = 'Caro(a) ,
                        <br /><br />
                        Obrigado por participar do Questionário.
                        <br /><br />
                        Sua empresa est� em avalia��o. 
                        <br /><br />
                        O download da devolutiva pode ser acessado via o link:<br />
                        http://' . $_SERVER['SERVER_NAME'] . '' . $this->devolutive->getPublicDir() . $this->devolutive->getArqName() . '
                        <br /><br />
                        Atenciosamente,
                        <br /><br />
                        Equipe PSMN';

        //Vtx_Util_Mail::send($to,$from,$subject,$message);
        //insere email na fila de disparo de mensagens de email
        $eQueue = new Model_EmailQueue();
        //$eQueue->setEmailQueue($to, $from, $subject, $message);
    }

    public function getGraficoPontuacao() {
        return $this->graficoPontuacao;
    }

    public function setGraficoPontuacao($graficoPontuacao) {
        $this->graficoPontuacao = $graficoPontuacao;
    }

    public function getCompetitionId() {
        return $this->competitionId;
    }

    public function setCompetitionId($competitionId) {
        $this->competitionId = $competitionId;
    }

    public function getTipoQuestionario() {
        return $this->tipoQuestionario;
    }

    public function setTipoQuestionario($tipoQuestionario) {
        $this->tipoQuestionario = $tipoQuestionario;
    }

    /**
     * @REFATORAR
     * 
     * @param type $blockId
     * @return type
     * @throws Exception
     */
    public function getArrayQuestionsAndAlternatives($questionnaireId, $userId, $blockId = null) {

        try {

            $arrDevolutiveRAA = array();
            $arrCriteria = array();
            $arrBlocks = array();
            $arrQuestionnaire = array();
            $arrRadarData = array();

            // Definições do Questionário
            $questionnaireDefs = $this->questionnaire->getQuestionnaireById($questionnaireId);

            $arrQuestionnaire['title'] = $questionnaireDefs->getTitle();
            $arrQuestionnaire['description'] = $questionnaireDefs->getDescription();
            $arrQuestionnaire['long_description'] = $questionnaireDefs->getLongDescription();
            $arrQuestionnaire['operation_beginning'] = Vtx_Util_Date::format_dma($questionnaireDefs->getOperationBeginning());
            $arrQuestionnaire['operation_ending'] = Vtx_Util_Date::format_dma($questionnaireDefs->getOperationEnding());

            // Recupera Dados do Questionario, Bloco e cada um dos enunciados das questoes vinculadas ao Bloco do questionario
            $questionsDefs = $this->question->getAllByQuestionnaireIdBlockId($questionnaireId, $blockId);


            //loop em cada QuestionId do QuestionarioId
            foreach ($questionsDefs as $question_def) {

                $idBlock = "";
                $idCriterion = "";

                $questionId = $question_def->getId();
                $question_value = $question_def->getQuestao();

                //recupera dados da QuestaoId
                // Grava a questão no array de devolutiva
                $arrDevolutiveRAA[$questionId]['designation'] = $question_def->getDesignacao();
                $arrDevolutiveRAA[$questionId]['value'] = $question_value;
                $arrDevolutiveRAA[$questionId]['text'] = $question_def->getTexto();

                // Verifica se existe Bloco válido e grava nos arrays de blocos e devolutiva
                $idBlock = $question_def->getBloco();
                if ($idBlock != "" && $idBlock != 0) {
                    $arrBlocks[$idBlock] = $question_def->getBlocoTitulo();
                    $arrDevolutiveRAA[$questionId]['block'] = $question_def->getBloco();
                }

                // Verifica se existe Critério válido e grava nos arrays de critérios e devolutiva
                $idCriterion = $question_def->getCriterio();
                if ($idCriterion != "" && $idCriterion != 0) {
                    $arrCriteria[$idCriterion] = $question_def->getCriterioTitulo();
                    $arrDevolutiveRAA[$questionId]['criterion'] = $question_def->getCriterio();
                }

                // Verifica se um determinada Empresa respondeu uma questao
                $isAnswered = $this->question->isAnsweredByEnterprise($questionId, $userId);

                //Se empresa respondeu a questaoId verificada, entao entra no condicional abaixo
                if ($isAnswered['status']) {
                    // Recupera a resposta escrita
                    $answer = $this->question->getQuestionAnswer($questionId, $userId);

                    $alternative_id = $answer['alternative_id'];
                    $arrDevolutiveRAA[$questionId]['alternative_id'] = $alternative_id;
                    $arrDevolutiveRAA[$questionId]['write_answer'] = (isset($answer['answer_value'])) ? $answer['answer_value'] : "";

                    if (count($answer['annual_result']) > 0) {
                        $arrDevolutiveRAA[$questionId]['annual_result'] = $answer['annual_result'];
                        $arrDevolutiveRAA[$questionId]['annual_result_unit'] = $answer['annual_result_unit'];
                    } else {
                        $arrDevolutiveRAA[$questionId]['annual_result'] = "";
                        $arrDevolutiveRAA[$questionId]['annual_result_unit'] = "";
                    }

                    // Recupera o feedback da alternativa escolhida
                    $alternative = $this->alternative->getAlternativeById($alternative_id);
                    $arrDevolutiveRAA[$questionId]['alternative_designation'] = $alternative->getDesignation();
                    $arrDevolutiveRAA[$questionId]['alternative_feedback'] = $alternative->getFeedbackDefault();

                    // Recupera o 'Pontos Fortes' do avaliador da resolução da questão 
                    $arrDevolutiveRAA[$questionId]['answer_feedback'] = $this->question->getAnswerFeedback($isAnswered['objAnswered']->getAnswerId());

                    // Recupera o 'Oportunidades de melhoria' do avaliador da resolução da questão 
                    $arrDevolutiveRAA[$questionId]['answer_feedback_improve'] = $this->question->getAnswerFeedbackImprove($isAnswered['objAnswered']->getAnswerId());
                }
                // Recupera os dados das alternativas da QuestionId
                $alternativesDefs = $this->alternative->getAllByQuestionId($questionId, false, 'object');
                foreach ($alternativesDefs as $alternative_def) {
                    $arr_alternative[$alternative_def->getDesignation()] = $alternative_def->getValue();
                }
                $arrDevolutiveRAA[$questionId]['alternatives'] = $arr_alternative;
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
    }

    /**
     * 
     * @deprecated 
     * 
     * validacao de blocos refatorada para DEvolutiveController
     * 
     * versao do pdf devolutiva caso Blocos do questionario nao existam
     * 
     * @param string $urldevolutiva
     */
    public function printAvisoPdfDevolutivaCasoNaoHajaBlocoQuestionario($arrBlocksResult) {
        require_once(APPLICATION_PATH . '/models/DevolutiveRAA.php');

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

        $pdf->SetFont('Arial', 'BI', 16);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->MultiCell(190, 13, utf8_decode("Faltando o cadastro dos blocos do Questionario"), 0, "C");

        // Renderização do arquivo PDF
        $pdf->Output($this->devolutive->getDirName() . $this->devolutive->getArqName());

        // Configura as permissões do arquivo
        chmod($this->devolutive->getDirName() . $this->devolutive->getArqName(), 0666);

        return $this->devolutive->getPublicDir() . $this->devolutive->getArqName();
    }

}

//end class
?>
