<?php


/**
 * Responsavel/controller da geracao devolutive MPE
 * 
 * Description of MakeAutoAvaliacao
 *
 * @author vtx
 */
class Vtx_Devolutive_Tipo_MPE_MPE extends Vtx_Devolutive_MakePdf
{
  
    const OK_CAPA = true;
    const OK_DADOS_CADASTRO = true;
    const OK_PARTE_1 = true;
    const OK_PARTE_2 = true;
    const OK_PARTE_3 = true;
    const OK_PARTE_4 = true;
    const IMG_CHECK = "/img/check.png";

    public $dirCapa;
    protected $programaCurrent;
    protected $Questionnaire;
    protected $strCiclo;
    
    public $pagina;
    public $isRA;
    
        
    public function __construct(Model_Devolutive $objDevolutive) 
    {
        /** @var $objDevolutive Model_Devolutive **/
        parent::__construct($objDevolutive);
        
        // Seta Programa Current 
        // MpeBrasil, SebraeMais, MpeDiagnostico
        $this->setProgramaCurrent(Zend_Registry::get('programaTipo'));
                
        //echo "Programa Atual: ".$this->getProgramaCurrent();        
        
        //seta diretorio para recuperar PDFs prontos da devolutiva
        $this->diretorioPdfsProntosParaGeracaoDevolutiva();
        
        $this->Questionnaire = new Model_Questionnaire();        
        
        $this->headerTittle = 'Relatorio de Autoavaliação';

        $this->adicionaFonteNova();
        
    }
    
    /**
     * diretorios com pdfs estaticos da devolutiva
     */
    private function diretorioPdfsProntosParaGeracaoDevolutiva()
    {
        switch ($this->getProgramaCurrent()) {
            case 'MpeDiagnostico':
                $this->setDirCapa('/capa/mpediagnostico/');
                break;
            case 'SebraeMais':
                $this->setDirCapa('/capa/sebraemais/');               
                break;
            case 'MpeBrasil':
                $this->setDirCapa('/capa/mpebrasil/');               
                break;
            default: //mpebrasil
                $this->setDirCapa('/capa/mpebrasil/');               
                break;           
        }        

     }
    
    /**
     * carrega fonte nova
     *  
     * referencia:
     * Adding new fonts and encoding support
     * http://www.fpdf.org/
     */
    public function adicionaFonteNova()
    {
       $this->AddFont('Myriad', '', "myriad-web-pro.php"); 
       $this->AddFont('Myriad', 'B', "myriadwebpro-bold.php"); 
       $this->AddFont('Myriad', 'I', "Myriad-Italic.php"); 
       $this->AddFont('Myriad', 'BI', "AdobeCorporateIDMyriadBold_Italic.php"); 
    }

    /**
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @param type $dirName
     * @param type $publicDir
     * @param type $arqName
     * @param type $isRA
     * @return type
     */
    public function makePdfDevolutiveAutoAvaliacaoExecuta($questionnaireId, $userId,$programaId, $dirName, $publicDir, $arqName, $isRA = false, $conclusao = false) 
    {
        $modelEnterprise = new Model_Enterprise();
        $modelUser = new Model_User();
        $isRAWithConclusion = $conclusao;
        $objUser = $modelUser->getUserById($userId);
        $objEnterprise = $modelEnterprise->getEnterpriseByUserId($userId);
        
        $programaTipo = Zend_Registry::get('programaTipo');
        $isSebraeMais = ($programaTipo == 'SebraeMais')?TRUE:FALSE;
        
        if ($arrBlocksResult = $this->Questionnaire->getBlocksAutoavaliacao($questionnaireId)) 
        {            
            //$governancaBlockId = $arrBlocksResult[0];
            
            //Pegar ID do Zend Registry - setar em DevolutiveController - indexAction
            
            //Parte 1 - Gestao da Empresa
            $gestaoBlockId = $this->objDevolutive->getBlockIdGestaoEmpresa();//50; 
            
            //Parte 2 - Comportamento Empreendededor - igual parte 2 do PSMN
            $comportamentoBlockId = $this->objDevolutive->getBlockIdEmpreendedorismo(); //51
            
            //Parte 3 - Responsabilidade Social
            $responsabilidadeSocialBlockId = $this->objDevolutive->getBlockIdResponsabilidadeSocial(); //52
            
            //Parte 4 - Praticas Inovacao
            $praticasInovacaoBlockId = $this->objDevolutive->getBlockIdPraticasInovacao(); //53
                    
            $enterpriseData = new Vtx_Devolutive_Tipo_MPE_getEnterpriseData();
            
            $arrayDevolutive = new Vtx_Devolutive_Tipo_MPE_getArrayDevolutive();
            //$arrayDevolutiveRAA->getArrayDevolutiveRAA($questionnaireId, $userId, $blockId);
            
       if (self::OK_DADOS_CADASTRO)
       {   
            list($arrEnterprise, $arrContact, $arrIssues) 
                = $enterpriseData->getEnterpriseData($userId);
       }
       if (self::OK_PARTE_1) 
       {
            //Bloco Gestao da Empresa
            list($arrDevolutiveGes, $arrBlocksGes, $arrCriteriaGes, $arrQuestionnaire) 
                = $arrayDevolutive->getArrayDevolutiveReturn($questionnaireId, $userId,$programaId, $gestaoBlockId);
       }
       if (self::OK_PARTE_2) 
       {            
            //Bloco Comportamento Empreendedor
            list($arrDevolutiveBlockId2, $arrBlocksBlockId2, $arrCriteriaBlockId2, $arrQuestionnaire) 
                = $arrayDevolutive->getArrayDevolutiveReturn($questionnaireId, $userId,$programaId, $comportamentoBlockId);            
        }   
        
       if (self::OK_PARTE_3) 
       {             
            //Bloco Responsabilidade Social
            list($arrDevolutiveBlockId3, $arrBlocksBlockId3, $arrCriteriaBlockId3, $arrQuestionnaire) 
                = $arrayDevolutive->getArrayDevolutiveReturn($questionnaireId, $userId,$programaId, $responsabilidadeSocialBlockId);          

            //var_dump ('arrDevolutiveBlockId3: ', $arrDevolutiveBlockId3);                
            //echo "<br>";            
       }
       
       if (self::OK_PARTE_4) 
       {          
            //Bloco Praticas Inovacao
            list($arrDevolutiveBlockId4, $arrBlocksBlockId4, $arrCriteriaBlockId4, $arrQuestionnaire) 
                = $arrayDevolutive->getArrayDevolutiveReturn($questionnaireId, $userId,$programaId, $praticasInovacaoBlockId);            
       }
       
       if (self::OK_PARTE_1) 
       {     
            //dados para Grafico Radar
            list($arrRadarDataGes, $arrTabulationGes, $arrPunctuationGes) 
                = $this->Questionnaire->getRadarData($questionnaireId, $gestaoBlockId, $userId, $programaId);
            
       }
   
       $strPathRadar = false;
       
       if (self::OK_PARTE_1) 
       {         
            //ano ciclo
            $qto_operation_beginning = utf8_decode($arrQuestionnaire['operation_beginning']);
            $dta_tmp = explode("/",$qto_operation_beginning);
            $str_ciclo = $dta_tmp[2];

            $this->setStrCiclo($str_ciclo);
                   
            $makeRadarPlot = new Vtx_Devolutive_Tipo_MPE_makeRadarPlot();            
            $strPathRadar = $makeRadarPlot->makeRadarPlot($arrCriteriaGes, $arrRadarDataGes, $arrTabulationGes, $arrPunctuationGes, $dirName, $str_ciclo);

            //obj StdClass com dados completos para a tabela de pontuacao
            $objDadosPontuacao = $makeRadarPlot->dadosTabelaPontuacao();

            $makeScoreRAA = new Vtx_Devolutive_Tipo_MPE_makeScoreRAA();
            //$makeScoreRAA->makeScoreRAA($questionnaireId, $userId);
            
            //seta variaveis para pontuacao do bloco Gestao Empresa
            //em model devolutive tem o metodo que grava esta pontuacao na tb ExecutionPontuacao
            $this->objDevolutive->setGravaPontuacaoGestaoEmpresa(true); //$gravaPontuacaoGestaoEmpresa
            $this->objDevolutive->setArrPunctuationGes($arrPunctuationGes);//$arrPunctuationGes                 
            
            
            $arrScores = $makeScoreRAA->makeScoreRAA($questionnaireId, $userId, $programaId);
            $scorePart1 = $arrScores['IsgcScore'];
            $scorePart2 = $arrScores['IsgScore'];

        }
   
            // Desabilita header e footer            
            //$pdf->header = 0;
            //$pdf->footer = 0;
            $this->header = 0;
            $this->footer = 0;
       
            // Prepara variáveis para paginação
            $this->AliasNbPages();
           
            //insere pdfs da devolutiva
            $pagina = $this->pagina = 1;

            $offSet = 0;

            // Habilita header e footer
            $this->header = 1;
            // Anterior [-] $this->AddPage();
            $this->footer = 1;
            
           ######## nova CAPA #############################################
            
        if (self::OK_CAPA)
        {
            $this->CapaMPE($isRA); 
        }   
           ####################################################################
 
           #################################################################
            ## Dados Cadastrais do avaliado            
        if (self::OK_DADOS_CADASTRO)
        {
            $emp = new Vtx_Devolutive_Tipo_MPE_EnterpriseDataMPE($this);
            $emp->dadosEmpresa($arrEnterprise, $arrContact, $arrIssues);   
        }            
           ########## Fim dados cadastro
                   
           #### comeco Parte 1 da devolutiva
            /**
             * parte 1 eh o minimo exigido para printar a devolutiva
             * 
             */
       if (self::OK_PARTE_1) 
       {
            $devolutiveParte1 = new Vtx_Devolutive_Tipo_MPE_DevolutiveParte1MPE($this);
            $devolutiveParte1->paginasIntroducao();
            
            ## Cria a imagem RADAR e a capa para a Parte 1
            $this->RadarMPE($arrCriteriaGes, $offSet, $strPathRadar, $scorePart1, $scorePart2);
            
            //monta tabela de pontuacao para Parte 1/Bloco 1
            $tabelaPontuacao = new Vtx_Devolutive_Tipo_MPE_TabelaPontuacao($this, $objDadosPontuacao);
   
            //pagina interpretacao radar
            $devolutiveParte1->paginasInterpretacaoRadar();
            
            //criterios e questoes
            $devolutiveParte1->DevolutiveParte1MPE($arrDevolutiveGes, $arrBlocksGes, $arrCriteriaGes, $offSet, $isRA);
       }
           ######################### Fim parte 1 da devolutiva
            
       if (self::OK_PARTE_2) 
       {
            // Parte 2
           $getPreenchidoBlockIdEmpreendedorismo = $this->objDevolutive->getPreenchidoBlockIdEmpreendedorismo();
            if ($getPreenchidoBlockIdEmpreendedorismo and !$isSebraeMais) { //checa se respondeu questoes e NÃO é FGA
                //$this->DevolutiveParte2MPE($arrDevolutiveBlockId2, $arrBlocksBlockId2, $arrCriteriaBlockId2, $offSet);            
                $devolutiveParte2 = new Vtx_Devolutive_Tipo_MPE_DevolutiveParte2MPE($this);
                $devolutiveParte2->DevolutiveParte2MPE ($arrDevolutiveBlockId2, $arrBlocksBlockId2, $arrCriteriaBlockId2, $offSet);

                //Tabela pontuacao Comportamento Empreendedor
                new Vtx_Devolutive_Tipo_MPE_PontuacaoCaracteristicaEmpreendedoraPdf($this, $this->objDevolutive);
                
                //insere pdfs de Analise Combinacoes
                $devolutiveParte2->analiseCombinacoes();
            }
       }
            
       if (self::OK_PARTE_3)
       {
            // Parte 3           
            if ($this->objDevolutive->getPreenchidoBlockIdResponsabilidadeSocial() and !$isSebraeMais) { //checa se respondeu questoes e NÃO é FGA
            
                $devolutiveParte3 = new Vtx_Devolutive_Tipo_MPE_DevolutiveParte3MPE($this);
                $devolutiveParte3->DevolutiveParte3MPE ($arrDevolutiveBlockId3, $arrBlocksBlockId3, $arrCriteriaBlockId3, $offSet, $isRA);        
            }
       }
       
       if (self::OK_PARTE_4)
       {
            // Parte 4           
            if ($this->objDevolutive->getPreenchidoBlockIdPraticasInovacao() and !$isSebraeMais) { //checa se respondeu questoes e NÃO é FGA
            
                $devolutiveParte4 = new Vtx_Devolutive_Tipo_MPE_DevolutiveParte4MPE($this);
                $devolutiveParte4->DevolutiveParte4MPE ($arrDevolutiveBlockId4, $arrBlocksBlockId4, $arrCriteriaBlockId4, $offSet, $isRA);
            
            }
       }
       
       
       //$thisWithConclusion = serialize($this);
       //$thisOk = unserialize(serialize($this));
       
            // Parte 5 - Conclusão do avaliador
        if ($isRAWithConclusion) {
            $configDb = Zend_Registry::get('configDb');
            
            $enterpriseId = $objEnterprise->getId();
            $devolutiveParte5 = new Vtx_Devolutive_Tipo_MPE_DevolutiveParte5MPE($this);
            $devolutiveParte5->DevolutiveParte5MPE( 
                                $enterpriseId, 
                                $configDb->programaIdAvaliador
                            );
            //$this->view->conclusion = $questionnaireConclusionRow->getConclusion();
            /*
            $thisWithConclusion->Output($dirName.'xxx'.$arqName,'F');
            $thisWithConclusion = unserialize($thisWithConclusion);
            // Configura as permissões do arquivo
            chmod($dirName.'xxx'.$arqName,0666);
            */
            $arqName = 'Conclusao_'.$arqName;// pegar a ultima devolutiva.
        }
            
       // Renderização do arquivo PDF
       $this->Output($dirName.$arqName,'F');
       
        // Configura as permissões do arquivo
        chmod($dirName.$arqName,0666);
        
            if ($strPathRadar) {
                if (file_exists($dirName.'radarTMP.png')) {
                    // Remove o arquivo temporário do radar
                    unlink($dirName.'radarTMP.png');
                }
            }
 
            //del image_bar.png - Questionario Caracteristica Empreendedor
            if (file_exists($dirName.'image_bar.png')) {
                // Remove o arquivo temporário do radar
                unlink($dirName.'image_bar.png');
            }
            
            
            if (self::OK_DADOS_CADASTRO and !$isRA)
            { 
                 //envia email personalizado para usuario
                 $this->enviaEmailAposGeracaoDevolutiva($arrEnterprise, $userId, $publicDir.$arqName);
            }  
            
        }
        else {
            
            throw new Exception("Faltando o cadastro dos blocos de questões para Questionario MPE.");
        }

        return $publicDir.$arqName;
    }   

    
    
    /**
     * insere e mensagems capa.pdf na devolutiva
     * 
     */
    public function CapaMPE($isRA) 
    {   $capa = ($isRA)?'capa-avaliacao.pdf':'capa-devolutiva.pdf';
        $pags = array($capa, 'p1_mensagem.pdf');
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this, true);            
    }
        
    
    // Rodapé
    public function Footer()
    {
        /**
         * @TODO
         * Algumas paginas nao serao impressas
         */
        if ($this->footer == 1) 
        {
            $this->SetFont('Myriad','B',7);
            
            //print abaixo eh somente para pag. 3 da devolutiva
            if (($this->PageNo() == 3)) {
                
                $this->SetFont('Myriad','B',10);
                //printa num. pagina a esquerda da pagina
                $this->SetXY(9,-17); 
                
                $numProtocolo = $this->objDevolutive->getProtocoloMPE();
                
                $textoProtocoloDevolutiva = "(PROTOCOLO ".$numProtocolo.utf8_decode(" às ") .$this->objDevolutive->getProtocoloCreateAt().")";
                
                $this->Cell(8,10,$textoProtocoloDevolutiva,0,0,'L');                            
            
            } // only page 3
            
            if (($this->PageNo() > 1)) {
                            
                $this->SetFont('Myriad','B',7);
                //printa num. pagina a esquerda da pagina
                //$this->SetXY(9,-17); 
                
                //aprinta num. pagina a direita da pagina
                $this->SetXY(185,-17); 
                
//                $textoProtocoloDevolutiva = "(PROTOCOLO ".$this->objDevolutive->getProtocoloIdDevolutiva(). utf8_decode(" às ") .$this->objDevolutive->getProtocoloCreateAt().")";
                $textoNumPagina = $this->PageNo();
                
                $textoFooter = $textoNumPagina;
                //$textoFooter = $textoNumPagina;
                
                $this->Cell(8,10,$textoFooter.'/{nb}',0,0,'L');            
            }
        }
        
    }
    
    
    /**
     * 
     */
    public function Header()
    {
        //if ($this->header == 1)
       
        //nao imprimi header na pagina 1 (capa)
        if ($this->PageNo() !== 1) 
        {
             //printa o tipo do Programa na capa da devolutiva gerada
             //$this->Cell(8,10,'ProgramaType: '.$this->getProgramaCurrent(),0,0,'L');            
        }
           
    }

    
    /**
     * 
     * @param type $arrCriteriaGes
     * @param type $offSet
     * @param type $strPathRadar
     * @param type $scorePart1
     * @param type $scorePart2
     */
    public function RadarMPE($arrCriteriaGes,$offSet,$strPathRadar,$scorePart1,$scorePart2)
    {
                
        if($strPathRadar) {
            
            ############### PAGINA DE FUNDO PARA RADAR
            $this->AddPage();
            
            $pags = array('fundo-mpe.pdf');
            $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this);     
            
            //$this->MultiCell(190,8,utf8_decode("Radar Gestão da Empresa"),0,"J",true);
            
            #######################################################
            
            $this->SetY(40);
            $xG = $this->GetX();
            $yG = $this->GetY();
            $this->Image($strPathRadar,$xG+25,$yG+3,140,80);
            $this->SetY($yG+90);
        
            $this->SetFont('Myriad','B',12);
            
            $tmp1 = str_replace('.',',',sprintf("%01.4f", $scorePart1));
            $tmp2 = str_replace('.',',',sprintf("%01.4f", $scorePart2));
            
            $scoreFinal = round((($scorePart1 * 0.25) + ($scorePart2 * 0.75)),4);
            $tmp3 = str_replace('.',',',sprintf("%01.4f", $scoreFinal));
            
        } else {
            $this->SetTextColor(255,0,0);
            $this->MultiCell(190,5,utf8_decode("Faltam dados para geração do gráfico radar.
                Informe a pontuação de cada alternativa no cadastro de questões."),0,"C");$this->ln(3);
            $this->SetTextColor(51,51,51);
        }
        
    }
       
    /**
     * envia msg de email apos geracao da devolutiva
     * 
     * @param type $arrEnterprise
     * @param type $userId
     * @param type $caminhoDevolutiva
     */
    public function enviaEmailAposGeracaoDevolutiva($arrEnterprise, $userId, $caminhoDevolutiva = '')
    {
        $modelEnterprise = new Model_Enterprise();
        
        $objEnterprise = $modelEnterprise->getEnterpriseByUserId($userId);
        
        // Envia o e-mail com o link da devolutiva para download.

        $to = $arrEnterprise['E-mail'];
            
        $r = $this->msgMpeSendMailDevolutive($caminhoDevolutiva);
        
        $eQueue = new Model_EmailQueue(false);//para não rodar select no statusQueue
        $eQueue->setEmailQueue($to, $r['from'], $r['subject'], $r['message'], '', 'ESPERA');
        // Email 6
        //  $modelEnterprise->sendMailToGestor($objEnterprise->getId(), 'email6');        
    }
    
    /**
     * msg email para os 3 programas
     * 
     * @param type $caminhoDevolutiva
     * @return string
     */
    public function msgMpeSendMailDevolutive($caminhoDevolutiva = '')
    {
        switch ($this->getProgramaCurrent()) 
        {
            case 'MpeDiagnostico':
                $from = Zend_Registry::get('config')->util->emailMpeDiagnostico; 
                $subject = 'Obrigado por participar do MPE Diagnostico';

                $message = 'Caro(a),
                            <br /><br />
                            Obrigado por participar do MPE Diagnóstico.
                            <br /><br />
                            O download da devolutiva pode ser acessado via o link:<br />
                            http://'.$_SERVER['HTTP_HOST'].''.$caminhoDevolutiva.'
                            <br /><br />
                            Atenciosamente,
                            <br /><br />
                            Equipe MPE Diagnostico';

                break;
            case 'SebraeMais':
                $from = Zend_Registry::get('config')->util->emailSebraeMais;
                $subject = 'Obrigado por participar do Sebrae Mais';

                $message = 'Caro(a),
                            <br /><br />
                            Obrigado por participar do Sebrae Mais.
                            <br /><br />
                            Sua empresa está em avaliação. 
                            <br /><br />
                            O download da devolutiva pode ser acessado via o link:<br />
                            http://'.$_SERVER['HTTP_HOST'].''.$caminhoDevolutiva.'
                            <br /><br />
                            Atenciosamente,
                            <br /><br />
                            Equipe Sebrae Mais';


                break;
            case 'MpeBrasil':
            default: //mpebrasil
                $from = Zend_Registry::get('config')->util->emailMpe; 
                $subject = 'Obrigado por participar do MPE Brasil 2013';

                $message = 'Caro(a),
                            <br /><br />
                            Obrigado por participar do MPE Brasil 2013. Segue anexo o Relatório apontando
                            os pontos fortes e oportunidades de melhoria com base na autoavaliação de sua empresa.  
                            <br /><br />
                            Sua empresa está em avaliação. Caso ela se destaque, você receberá o
                            contato do gestor do Prêmio de seu estado. 

                            <br /><br />
                            O download da devolutiva pode ser acessado via o link:<br />
                            http://'.$_SERVER['HTTP_HOST'].''.$caminhoDevolutiva.'
                            <br /><br />
                            Atenciosamente,
                            <br /><br />
                            Equipe MPE Brasil';

                break;           
        }                
        
        $resultMsg = array (
                         'from' => $from,
                         'subject' => $subject,
                         'message' => $message
                     );
        

        return $resultMsg;
        
    }


    public function getStrCiclo() {
        return $this->strCiclo;
    }

    public function setStrCiclo($strCiclo) {
        $this->strCiclo = $strCiclo;
    }

    /**
     * recupera string do Programa Atual
     * @return string
     */
    public function getProgramaCurrent() {
        return $this->programaCurrent;
    }

    /**
     * seta string do Programa Atual (corrente)
     * 
     * @param string $programaCurrent
     */
    public function setProgramaCurrent($programaCurrent) {
        $this->programaCurrent = $programaCurrent;
    }

    /**
     * diretorio dos pdfs prontos que montam a devolutiva PDF
     * @return string
     */
    public function getDirCapa() {
        return $this->dirCapa;
    }

    /**
     * diretorio dos pdfs prontos que montam a devolutiva PDF
     * @param string $dirCapa
     */
    public function setDirCapa($dirCapa) {
        $this->dirCapa = $dirCapa;
    }

    

}

