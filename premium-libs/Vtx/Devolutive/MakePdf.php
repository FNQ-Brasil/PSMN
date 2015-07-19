<?php

/**
 * Classe responsavel pelo PDF da devolutiva, futuramente outros formatos de arquivos.
 * 
 * - validacao
 * - geracao
 * - gravacao
 * 
 * @TODO
 * - varios
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_MakePdf extends Vtx_FPDI_FPDI   #Vtx_FPDF_FPDF
{
    
    const BASE_PATH = "/devolutives/";
    
    const ARQNAME_EVALUATION = "Evaluation_";
   
    const ARQNAME_DEVOLUTIVE = "Devolutive_";
    
    const EXTENSION_ARQ = ".pdf";    
    
    const PERIODO = "Período de vigência:";
    
    public $locale;
    
    public $headerTitle;
    
    public $root_path;
    
    public $public_path;
    
    public $tbUser;
    
    public $header;
    
    public $footer;
    
    protected $texts;
        
    protected $strPathRadar;
    
    protected $tituloCapa;
    
    protected $subTituloCapa;

    protected $logotipoEmpresa;
    
    protected $logotipoQualidade;
    
    protected $conteudoIni ;

    protected $imagemFooter;

    protected $emissao_data;
    
    protected $txtCadastroEmpresa;
    
    protected $txtContatoEmpresa;
    
    protected $txtResCadastroEmpresa;

    protected $tipoQuestionario;
    
    protected $capaAvaliacao;
    
    protected $capaDiagnostico;
    
    protected $arrQuestionnaireDef;
    
    /**
     *
     * @var Model_Devolutive
     */
    protected $objDevolutive;
    
    protected $offset;
    
    protected $arrDevolutive;
    
    protected $arrBlocks;
    
    protected $arrCriteria;
    
    protected $arquivoPdfDevolutiveParte2;
    
    /**
     * Carrega objetos e seta dados para funcionamento da classe
     * 
     * @param Model_Devolutive $objDevolutive
     */
    public function __construct(Model_Devolutive $objDevolutive) 
    {        
        parent::__construct();

        $this->objDevolutive = $objDevolutive;
        
        $this->loadClassesZend();
        $this->loadClasses();
        
    }
            
    /**
     * load de classes Zend
     */
    private function loadClassesZend()
    {
        $this->root_path = Zend_Registry::get('config')->paths->root;
        $this->public_path = Zend_Registry::get('config')->paths->public;
        $this->locale = new Zend_Locale('de_AT');                
               
    }
    
    /**
     * load de classes Model e Table
     */
    private function loadClasses()
    {
        $this->tbUser = new DbTable_User();        
    }
    
    
    //cabeçalho da devolutiva pdf
    public function Header()
    {  
        if ($this->PageNo() !== 1) { //true
            $header = new Vtx_Devolutive_HeaderPdf($this);
        }
    }

    // Rodapé
    public function Footer()
    {
       if ($this->PageNo() !== 1) {
        
            $footer = new Vtx_Devolutive_FooterPdf($this, $this->objDevolutive);
        }
    }    
    
    
    /**
     * insere arquivo PDF como capa(pagina 1) da devolutiva
     * 
     * @depends FPDI
     */
    public function Capa() 
    {
            
//        var_dump('headerCapa', $this->getHeader());
//        var_dump('headerFooter', $this->getFooter());
//        exit;   
        
        $capa = new Vtx_Devolutive_CapaPdf($this);         
    }
    
    

    
    
    /**
     * monta o texto de apresentacao
     * 
     */
    public function Presentation() 
    {
        $apres = new Vtx_Devolutive_PresentationPdf($this);

    }    
    
    
    /**
     * Monta a segunda capa ou pagina dois
     * 
     * @param type $arrQuestionnaireDef
     */
    public function FirstPage($arrQuestionnaireDef)
    {
        $this->setArrQuestionnaireDef($arrQuestionnaireDef);
        
        $firstpage = new Vtx_Devolutive_FirstPagePdf($this);
        
    }
    

    /**
     * Monta dados do cadastro da empresa
     *  
     */
    public function EnterpriseData()
    {
        $cadastro = new Vtx_Devolutive_DadosEmpresaPdf($this, $this->objDevolutive);
    }
    
    /**
     * Monta conteudo do Bloco do Questionario
     * 
     */
    public function ConteudoBlocoDevolutive($arrDevolutive, $arrBlocks, $arrCriteria)
    {
        $this->setArrDevolutive($arrDevolutive);
        $this->setArrBlocks($arrBlocks);
        $this->setArrCriteria($arrCriteria);
        
        $bloco = new Vtx_Devolutive_ConteudoBlocoQuestionarioPdf( $this, $this->objDevolutive );       
            
    }
    

    /**
     * 
     * 
     * metodo que recebe objeto Devolutive e nomeia arquivo pdf e cria diretorio onde pdf sera gravado
     * 
     * @param Model_Devolutive $objDevolutive
     * 
     * @return boolean
     */
    public function preparaFileSystemParaDevolutiva()
    {
        $result = false;
        
        $arrUser = array('Id = ?' => $this->objDevolutive->getUserId());
        
        $this->userSalt = $this->tbUser->fetchRow($arrUser)->getSalt();
        
        if (!$this->objDevolutive->getIsRA()) {
            $arqName = self::ARQNAME_DEVOLUTIVE.$this->objDevolutive->getQuestionnaireId()."_".date("YmdHis").self::EXTENSION_ARQ;
        } else {
            $arqName = self::ARQNAME_EVALUATION.$this->objDevolutive->getQuestionnaireId()."_".date("YmdHis").self::EXTENSION_ARQ;
        }
              
        $this->objDevolutive->setArqName($arqName);
        
        $basePath = self::BASE_PATH.hash("sha256",$this->objDevolutive->getUserId()."_".$this->userSalt)."/";
        $dirName = $this->public_path.$basePath;
        
        $this->objDevolutive->setDirName($dirName);
              
        $publicDir = Zend_Controller_Front::getInstance()->getBaseUrl().$basePath;
        
        $this->objDevolutive->setPublicDir($publicDir);
        
        $arqPath = $publicDir.$arqName;
        
        $this->objDevolutive->setArqPath($arqPath);
              
        //cria diretorio para pdf
        try {
            
            if (!is_dir($dirName)) {
                mkdir($dirName);
            }

            chmod($dirName,0777);
        
        } catch (Excception $e) {
            
        }
        
        return $result;
        
    } //end function
   
    public function getArrDevolutive() {
        return $this->arrDevolutive;
    }

    public function setArrDevolutive($arrDevolutive) {
        $this->arrDevolutive = $arrDevolutive;
    }

    public function getArrBlocks() {
        return $this->arrBlocks;
    }

    public function setArrBlocks($arrBlocks) {
        $this->arrBlocks = $arrBlocks;
    }

    public function getArrCriteria() {
        return $this->arrCriteria;
    }

    public function setArrCriteria($arrCriteria) {
        $this->arrCriteria = $arrCriteria;
    }
        
    
    public function getTituloCapa() {
        return $this->tituloCapa;
    }

    public function setTituloCapa($tituloCapa) {
        $this->tituloCapa = $tituloCapa;
    }
    
    public function getLogotipoEmpresa() {
        return $this->logotipoEmpresa;
    }

    public function setLogotipoEmpresa($logotipoEmpresa) {
        $this->logotipoEmpresa = $logotipoEmpresa;
    }

    public function getLogotipoQualidade() {
        return $this->logotipoQualidade;
    }

    public function setLogotipoQualidade($logotipoQualidade) {
        $this->logotipoQualidade = $logotipoQualidade;
    }    

    
    public function getArquivoPdfDevolutiveParte2() {
        return $this->arquivoPdfDevolutiveParte2;
    }

    public function setArquivoPdfDevolutiveParte2($arquivoPdfDevolutiveParte2) {
        $this->arquivoPdfDevolutiveParte2 = $arquivoPdfDevolutiveParte2;
    }

    
    public function getSubTituloCapa() {
        return $this->subTituloCapa;
    }

    public function setSubTituloCapa($subTituloCapa) {
        $this->subTituloCapa = $subTituloCapa;
    }

        
    public function getHeaderTitle() {
        return $this->headerTitle;
    }

    public function setHeaderTitle($headerTitle) {
        $this->headerTitle = $headerTitle;
    }
    
    
    public function getArrHeader() {
        return $this->arrHeader;
    }

    public function setArrHeader($arrHeader) {
        $this->arrHeader = $arrHeader;
    }

    public function getIsRA() {
        return $this->isRA;
    }

    public function setIsRA($isRA=false) {
        $this->isRA = $isRA;
    }
 
    /**
     * habilita ou desabilita radar
     * @param type $str
     */
    public function setStrPathRadar($str)
    {
       $this->strPathRadar = $str;
    }

    /**
     * habilita ou desabilita radar
     * @return type
     */
    public function getStrPathRadar()
    {
       return $this->strPathRadar;
    }    
    
    public function getConteudoIni() {
        return $this->conteudoIni;
    }

    public function setConteudoIni($conteudoIni) {
        $this->conteudoIni = $conteudoIni;
    }


    public function getEmissao_data() {
        return $this->emissao_data;
    }

    public function setEmissao_data($emissao_data) {
        $this->emissao_data = $emissao_data;
    }

    public function getTxtCadastroEmpresa() {
        return $this->txtCadastroEmpresa;
    }

    public function setTxtCadastroEmpresa($txtCadastroEmpresa) {
        $this->txtCadastroEmpresa = $txtCadastroEmpresa;
    }

    public function getTxtContatoEmpresa() {
        return $this->txtContatoEmpresa;
    }

    public function setTxtContatoEmpresa($txtContatoEmpresa) {
        $this->txtContatoEmpresa = $txtContatoEmpresa;
    }

    public function getTxtResCadastroEmpresa() {
        return $this->txtResCadastroEmpresa;
    }

    public function setTxtResCadastroEmpresa($txtResCadastroEmpresa) {
        $this->txtResCadastroEmpresa = $txtResCadastroEmpresa;
    }        
    
    public function getTexts() {
        return $this->texts;
    }

    /**
     * Seta caminho arquivo .ini com texts fixos da devolutiva
     * @param type $texts
     */
    public function setTexts($texts=null) {
        $this->texts = new Zend_Config_Ini(
            APPLICATION_PATH_LIBS . $this->getConteudoIni(), APPLICATION_ENV
        );
    }
    
      
    public function getTipoQuestionario() {
        return $this->tipoQuestionario;
    }

    public function setTipoQuestionario($tipoQuestionario) {
        $this->tipoQuestionario = $tipoQuestionario;
    }

    public function getCapaAvaliacao() {
        return $this->capaAvaliacao;
    }

    public function setCapaAvaliacao($capaAvaliacao) {
        $this->capaAvaliacao = $capaAvaliacao;
    }

    public function getCapaDiagnostico() {
        return $this->capaDiagnostico;
    }

    public function setCapaDiagnostico($capaDiagnostico) {
        $this->capaDiagnostico = $capaDiagnostico;
    }    

    public function getArrQuestionnaireDef() {
        return $this->arrQuestionnaireDef;
    }

    public function setArrQuestionnaireDef($arrQuestionnaireDef) {
        $this->arrQuestionnaireDef = $arrQuestionnaireDef;
    }
    
    public function getOffset() {
        return $this->offset;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }
    

    public function getImagemFooter() {
        return $this->imagemFooter;
    }

    public function setImagemFooter($imagemFooter) {
        $this->imagemFooter = $imagemFooter;
    }


    public function getHeader() {
        return $this->header;
    }

    public function setHeader($header) {
        $this->header = $header;
    }

    public function getFooter() {
        return $this->footer;
    }

    public function setFooter($footer) {
        $this->footer = $footer;
    }

    
    
    /**
     * @REFATORAR
     * 
     * 
     * conteudo do pdf - texto introdutorio
     * 
     */
    public function introducaoModel()
    {
        
        $this->AddPage();
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','B',11);
        
        $this->ln(4);
        
        $this->MultiCell(190,10,utf8_decode("Relatório de Autoavaliação"),0,"J");$this->ln(3);
        //$this->MultiCell(190,8,utf8_decode($this->texts->model->s1),0,"C");$this->ln(3);
        
        //$this->SetFont('Arial','B',11);
        //$this->MultiCell(190,10,utf8_decode($this->texts->model->t1),0,"J");
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t1p1),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t1p2),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t1p3),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t1p4),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','B',11);
        $this->MultiCell(190,10,utf8_decode($this->texts->model->t2),0,"J");
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t2p1),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','B',11);
        $this->MultiCell(190,10,utf8_decode($this->texts->model->t3),0,"J");
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p1),0,"J");$this->ln(3);
        $this->SetX(15);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p2),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p3),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p4),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p5),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p6),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p7),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p8),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p9),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p10),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p11),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p12),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p13),0,"J");$this->ln(3);
        $xG = $this->GetX();
        $yG = $this->GetY();
        
        //print grafico Intro se existir caminho da imagem
  
//        if ($this->getGraficoIntro()) {
//            $this->Image($this->public_path.$this->getGraficoIntro(),$xG+55,$yG+3,80,80);
//        }
        //$this->Image($this->public_path.'/img/grafico.jpg',$xG+55,$yG+3,80,80);
        
        $this->SetY($yG+88);
        $this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p14),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p15),0,"J");$this->ln(3);
        $this->SetX(15);
        $this->MultiCell(185,5,utf8_decode($this->texts->model->t3p16),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p17),0,"J");$this->ln(3);
        $this->SetX(15);
        $this->MultiCell(185,5,utf8_decode($this->texts->model->t3p18),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p19),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','B',10);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p20N),0,"J");$this->ln(3);
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p21),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p22),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','B',10);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p23N),0,"J");$this->ln(3);
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p24),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','B',10);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p25N),0,"J");$this->ln(3);
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p26),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','B',10);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p27N),0,"J");$this->ln(3);
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p28),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->model->t3p29),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','',9);
        
    }

    /**
     * @REFATORAR
     * 
     * @param type $scorePart1
     * 
     */
    public function introducaoTextoFirstPart($scorePart1)
    {
        
        $this->AddPage();
        $this->SetTextColor(51,51,51);
        $this->ln(4);
        $this->SetFont('Arial','B',11);
        $this->MultiCell(190,10,utf8_decode($this->texts->firstPart->t1),0,"J");
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p1),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p2),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p3),0,"J");$this->ln(3);
        $this->SetX(15);
        $this->MultiCell(185,5,utf8_decode($this->texts->firstPart->t1p4),0,"J");$this->ln(3);
        
        $this->ln(5);
        $this->SetFont('Arial','B',12);
        $tmp = str_replace('.',',',sprintf("%01.4f", $scorePart1));
        $this->MultiCell(190,8,utf8_decode($this->texts->firstPart->t3p1B . $tmp . " %"),0,"C");
        $this->Ln(5);
        
        $this->SetFont('Arial','BI',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p5BI),0,"C");$this->ln(3);
        $this->SetFont('Arial','I',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p6I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p7I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p8I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p9I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p10I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t1p11I),0,"J");$this->ln(3);
        $this->AddPage();
        $this->SetFont('Arial','B',10);
        $this->ln(4);
        $this->MultiCell(190,5,utf8_decode($this->texts->firstPart->t2),0,"J");$this->ln(4);
    }
    
    /**
     * @REFATORAR
     * 
     * @param type $scorePart1
     */
    public function FirstTable($scorePart1) 
    {
        
        $this->SetTextColor(51,51,51);
        $this->SetDrawColor(51,51,51);
        $this->SetLineWidth(.1);
        $this->SetFont('Arial','B','8');
        
        $heig1 = "5";
        $margin = 7.5;
        $widt1 = array( '0' => '30', 
                        '1' => '20',
                        '2' => '15',
                        '3' => '15',
                        '4' => '15',
                        '5' => '15',
                        '6' => '32.5',
                        '7' => '32.5'
                      );
        
        $xMG = $this->GetX();
        $this->SetX($xMG+$margin);
        $this->Cell($widt1['0'],(2*$heig1),utf8_decode(" Bloco"),1,0,'C');
        $xI = $this->getX();
        $this->Cell($widt1['1'],(2*$heig1),utf8_decode(" Questão"),1,0,'C');
        $this->Cell(($widt1['2']+$widt1['3']+$widt1['4']+$widt1['5']),$heig1,utf8_decode(" Alternativas"),1,0,'C');
        $yM = $this->GetY();
        $this->Cell($widt1['6'],(2*$heig1),utf8_decode('Total da Questão'),1,0,'C');
        $xF = $this->getX();
        $this->Cell($widt1['7'],(2*$heig1),utf8_decode('Total do Bloco'),1,0,'C');
        $this->setXY($xI+$widt1['1'],($yM+$heig1));
        $this->Cell($widt1['2'],$heig1,utf8_decode("A"),1,0,'C');
        $this->Cell($widt1['3'],$heig1,utf8_decode("B"),1,0,'C');
        $this->Cell($widt1['4'],$heig1,utf8_decode("C"),1,0,'C');
        $this->Cell($widt1['5'],$heig1,utf8_decode("D"),1,0,'C');
        $this->ln();
        $xT0 = $this->GetX();
        $yT0 = $this->GetY();
        $yF = $this->getY();
        $this->SetFont('Arial','','8');
        
        $qtdLinhas = $arrDataTab1->count();
        $i = 1;
        
        foreach($arrDataTab1 AS $dataTab1) {
           
            if($i == 1) {
                $this->SetX($xMG+$margin);
                $this->Cell($widt1['0'],($qtdLinhas*$heig1),utf8_decode("Governança"),1,0,'C');
            }
            else {
                $this->SetX($xI);
            }
            
            $this->Cell($widt1['1'],$heig1,utf8_decode($i),1,0,'C');
            $this->Cell($widt1['2'],$heig1,utf8_decode("0,0"),1,0,'C');
            $this->Cell($widt1['3'],$heig1,utf8_decode("3,0"),1,0,'C');
            $this->Cell($widt1['4'],$heig1,utf8_decode("7,5"),1,0,'C');
            $this->Cell($widt1['5'],$heig1,utf8_decode("10,0"),1,0,'C');
            $tmpScore = Zend_Locale_Format::toNumber($dataTab1->getPontos(), array('locale' => $this->locale, 'precision' => 1));
            $this->Cell($widt1['6'],$heig1,utf8_decode($tmpScore),1,0,'C');
            
            if($i == $qtdLinhas) {
                $this->SetXY($xF,$yF);
                $tmpScoreGov = Zend_Locale_Format::toNumber($scoreGov, array('locale' => $this->locale, 'precision' => 1));
                $this->Cell($widt1['7'],($qtdLinhas*$heig1),utf8_decode($tmpScoreGov),1,0,'C');
            }
            
            $this->ln();
            $i++;
        }
        
    }

    /**
     * @REFATORAR
     * 
     * 
     * @param type $arrCriteriaGes
     * @param type $offSet
     * @param type $strPathRadar
     * @param type $scorePart1
     * @param type $scorePart2
     */
    public function SecondPart($arrCriteriaGes,$offSet,$strPathRadar,$scorePart1,$scorePart2)
    {
        
        $strPathRadar = $this->getStrPathRadar();
        
        $this->AddPage();
        $this->SetTextColor(51,51,51);
        $this->ln(4);
        $this->SetFont('Arial','B',11);
        $this->MultiCell(190,10,utf8_decode($this->texts->secondPart->t1),0,"J");
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p1),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p2),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p3),0,"J");$this->ln(3);
        $this->SetX(15);
        $this->MultiCell(185,5,utf8_decode($this->texts->secondPart->t1p4),0,"J");$this->ln(3);
        
        $this->SetFont('Arial','B',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p5BI),0,"J");$this->ln(3);
        
        if($strPathRadar) {
            $xG = $this->GetX();
            $yG = $this->GetY();
            $this->Image($strPathRadar,$xG+25,$yG+3,140,80);
            $this->SetY($yG+90);
        
            $this->SetFont('Arial','B',12);
            
            $tmp1 = str_replace('.',',',sprintf("%01.4f", $scorePart1));
            $tmp2 = str_replace('.',',',sprintf("%01.4f", $scorePart2));
            
            $scoreFinal = round((($scorePart1 * 0.25) + ($scorePart2 * 0.75)),4);
            $tmp3 = str_replace('.',',',sprintf("%01.4f", $scoreFinal));
            
            $this->MultiCell(190,8,utf8_decode($this->texts->secondPart->sc1) . $tmp1 . " %",0,"C");
            $this->MultiCell(190,8,utf8_decode($this->texts->secondPart->sc2) . $tmp2 . " %",0,"C");
            $this->Ln(5);
            $this->MultiCell(190,8,utf8_decode($this->texts->secondPart->sc3) . $tmp3 . " %",0,"C");
            $this->Ln(5);

        } else {
            $this->SetTextColor(255,0,0);
            $this->MultiCell(190,5,utf8_decode("Faltam dados para geração do gráfico radar.
                Informe a pontuação de cada alternativa no cadastro de questões."),0,"C");$this->ln(3);
            $this->SetTextColor(51,51,51);
        }
        
        $this->SetFont('Arial','I',9);
        $this->SetFont('Arial','BI',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p6BI),0,"C");$this->ln(3);
        $this->SetFont('Arial','I',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p7I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p8I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p9I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p10I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p11I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p12I),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t1p13I),0,"J");$this->ln(3);
        $this->AddPage();
        $this->SetFont('Arial','B',10);
        $this->ln(4);
        $this->MultiCell(190,5,utf8_decode($this->texts->secondPart->t2),0,"J");$this->ln(4);
    }
    
    /**
     * @REFATORAR
     * 
     * 
     * @param type $arrDataTabs2
     * @param type $arrCriteriaGes
     * @param type $offSet
     */
    public function SecondTables($arrDataTabs2,$arrCriteriaGes,$offSet)
    {
        $this->SetTextColor(51,51,51);
        //$this->SetFillColor(224,235,255);
        $this->SetDrawColor(51,51,51);
        $this->SetLineWidth(.1);
        $arrQtdLinhasCriterio = array();
        
        foreach($arrDataTabs2 AS $dataTabs2) {
            $indice = $dataTabs2->getCD();
            if(!isset($arrQtdLinhasCriterio[$indice])) {
                $arrQtdLinhasCriterio[$indice] = 1;
            } else {
                $arrQtdLinhasCriterio[$indice]++;
            }
        }
        
        $heig1 = "5";
        $margin = 7.5;
        $widt1 = array( 
            '0' => '52', 
            '1' => '17',
            '2' => '13',
            '3' => '13',
            '4' => '13',
            '5' => '13',
            '6' => '29',
            '7' => '25'
            );

        $criterioAtual = "";
        
        foreach($arrDataTabs2 AS $dataTabs2) {

            if($criterioAtual != $dataTabs2->getCD()) {
                
                $criterioAtual = $dataTabs2->getCD();

                $this->SetFont('Arial','B','8');
                $xMG = $this->GetX();
                $this->SetX($xMG+$margin);
                $this->Cell($widt1['0'],(2*$heig1),utf8_decode("Critério"),1,0,'C');
                $xI = $this->getX();
                $this->Cell($widt1['1'],(2*$heig1),utf8_decode(" Questão"),1,0,'C');
                $this->Cell(($widt1['2']+$widt1['3']+$widt1['4']+$widt1['5']),$heig1,utf8_decode(" Alternativas"),1,0,'C');
                $yM = $this->GetY();
                $this->Cell($widt1['6'],(2*$heig1),utf8_decode('Total da Questão'),1,0,'C');
                $xF = $this->getX();
                $this->Cell($widt1['7'],(2*$heig1),utf8_decode('Total do Bloco'),1,0,'C');
                $this->setXY($xI+$widt1['1'],($yM+$heig1));
                $this->Cell($widt1['2'],$heig1,utf8_decode("A"),1,0,'C');
                $this->Cell($widt1['3'],$heig1,utf8_decode("B"),1,0,'C');
                $this->Cell($widt1['4'],$heig1,utf8_decode("C"),1,0,'C');
                $this->Cell($widt1['5'],$heig1,utf8_decode("D"),1,0,'C');
                $this->ln();
                $xT0 = $this->GetX();
                $yT0 = $this->GetY();
                $yF = $this->getY();
                $this->SetFont('Arial','','8');

                //$qtdLinhas = $arrDataTab1->count();
                $scoreGov = 0;
                $i = 1;

            }

            $scoreGov = ($scoreGov + $dataTabs2->getPontos());
            $offSet++;
            
            if($i == 1) {
                $this->SetX($xMG+$margin);
                $this->Cell($widt1['0'],($arrQtdLinhasCriterio[$criterioAtual]*$heig1),utf8_decode($arrCriteriaGes[$criterioAtual]),1,0,'C');
            }
            else {
                $this->SetX($xI);
            }
            
            $this->Cell($widt1['1'],$heig1,utf8_decode($offSet),1,0,'C');
            $this->Cell($widt1['2'],$heig1,utf8_decode("0,0"),1,0,'C');
            $this->Cell($widt1['3'],$heig1,utf8_decode("3,0"),1,0,'C');
            $this->Cell($widt1['4'],$heig1,utf8_decode("7,5"),1,0,'C');
            $this->Cell($widt1['5'],$heig1,utf8_decode("10,0"),1,0,'C');
            $tmpScore = Zend_Locale_Format::toNumber($dataTabs2->getPontos(), array('locale' => $this->locale, 'precision' => 1));
            $this->Cell($widt1['6'],$heig1,utf8_decode($tmpScore),1,0,'C');
            
            if($i == $arrQtdLinhasCriterio[$criterioAtual]) {
                $this->SetXY($xF,$yF);
                $tmpScoreGov = Zend_Locale_Format::toNumber($scoreGov, array('locale' => $this->locale, 'precision' => 1));
                $this->Cell($widt1['7'],($arrQtdLinhasCriterio[$criterioAtual]*$heig1),utf8_decode($tmpScoreGov),1,0,'C');
                $this->ln(10);
            }
            
            $this->ln();
            $i++;
            
        }
        
        $this->ln(5);
    }
    
    
    
} //end class

?>
