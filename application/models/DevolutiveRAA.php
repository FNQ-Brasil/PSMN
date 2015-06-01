<?php

class Model_DevolutiveRAA extends Vtx_FPDF_FPDF
{
    
    public $locale;
    protected $texts;
    
    public function __construct($arrHeader, $isRA = false)
    {
        parent::__construct();
        $this->root_path = Zend_Registry::get('config')->paths->root;
        $this->public_path = Zend_Registry::get('config')->paths->public;
        $this->headerTittle = $arrHeader['title'];

        //define('FPDF_FONTPATH', APPLICATION_PATH_LIBS . '/Fpdf/font/'); 

        $devolutiveTexts = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/devolutive.ini', APPLICATION_ENV
        );
        $this->isRA = $isRA;
        $this->texts = $devolutiveTexts;
        $this->locale = new Zend_Locale('de_AT');
    }
    
    public function Header()
    {
        if ($this->header == 1)
        {
            $this->SetTextColor(51,51,51);
            $this->SetDrawColor(51,51,51);
            $this->SetLineWidth(0.2);
            $this->Image($this->public_path.'/img/logo2.png',13,9,33);
            $this->Image($this->public_path.'/img/fnq.jpg',52,10,25);
            $this->SetFont('Arial','B',11);
            $tmpTxt = substr($this->headerTittle,0.110);
            //$this->Cell(80,10,utf8_decode($tmpTxt),0,0,'R');
            $this->setXY(80,10);
            $this->MultiCell(120,5,utf8_decode($tmpTxt),0,'R');
            $this->Ln(15);
        }
    }

    // Rodapé
    public function Footer()
    {
        if ($this->footer == 1)
        {
            $this->SetTextColor(51,51,51);
            $this->SetDrawColor(51,51,51);
            $this->SetLineWidth(0.2);
            $this->line(10,280,200,280);
            $this->SetXY(10,-15);
            $this->SetFont('Arial','BI',7);
            $this->Cell(13,3,utf8_decode("SESCOOP"),0,0,'R');
            $this->SetFont('Arial','I',7);
            $this->Cell(65,3,utf8_decode(" Serviço Nacional de Aprendizagem do Cooperativismo"),0,1);
            $this->SetFont('Arial','BI',7);
            $this->Cell(13,3,utf8_decode("FNQ"),0,0,'R');
            $this->SetFont('Arial','I',7);
            $this->Cell(65,3,utf8_decode(" Fundação Nacional da Qualidade"),0,1);
            $this->SetFont('Arial','BI',8);
            $this->SetXY(120,-17);
            $this->Cell(40,10,utf8_decode("Emissão:  ").date('d/m/Y  H:i:s'),0,0,'C');
            $this->SetXY(188,-17);
            $this->Cell(20,10,$this->PageNo().'/{nb}',0,0,'C');
        }
    }
    
    // Questões respondidas
    public function FirstPage($arrQuestionnaireDef)
    {
        
        $qto_title = utf8_decode($arrQuestionnaireDef['title']);
        $qto_description = utf8_decode($arrQuestionnaireDef['description']);
        $qto_long_description = utf8_decode($arrQuestionnaireDef['long_description']);
        $qto_operation_beginning = utf8_decode($arrQuestionnaireDef['operation_beginning']);
        $qto_operation_ending = utf8_decode($arrQuestionnaireDef['operation_ending']);
        $dta_tmp = explode("/",$qto_operation_beginning);
        $str_ciclo = $dta_tmp[2];
        
        $this->AddPage();
        $this->SetFillColor(214,232,237);
        $this->Rect(10,10,190,277,'F');
        $this->SetFillColor(255,255,255);
        $this->Rect(15,15,180,25,'F');
        $this->Image($this->public_path.'/img/logo2.png',26,18,50);
        $this->Image($this->public_path.'/img/fnq.jpg',140,22,32);
        $this->SetFillColor(255,255,255);
        $this->SetXY(10,50);
        $this->SetFont('Arial','BI',24);
        $this->SetTextColor(31,118,138);
        if ($this->isRA) {
            $this->MultiCell(190,13,utf8_decode("Relatório de Avaliação"),0,"C");
        } else {
            $this->MultiCell(190,13,utf8_decode("Devolutiva de Questionário"),0,"C");
        }
        $this->SetLineWidth(1.2);
        $this->SetDrawColor(255,255,255);
        $this->Line(15,65,195,65);
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','BI',15.3);
        $this->SetXY(10,68);
        $this->MultiCell(190,10,utf8_decode("Programa de Desenvolvimento da Gestão das Empresas - PDGC"),0,"C");
        $this->MultiCell(190,10,$qto_description,0,"C");
        $this->MultiCell(190,10,"Ciclo ".$str_ciclo,0,"C");
        $this->SetXY(145,253);
        $this->SetLineWidth(0.5);
        $this->SetFont('Arial','B',8);
        $this->MultiCell(45,7,utf8_decode("Período de vigência:"),1,"C");
        $this->SetFont('Arial','B',16);
        $this->SetTextColor(31,118,138);
        $this->SetX(145);
        $this->MultiCell(45,8,$qto_operation_beginning,1,"C");
        $this->SetX(145);
        $this->MultiCell(45,8,$qto_operation_ending,1,"C");
    }
    
    // Apresentação
    public function Presentation() 
    {
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','B',10);
        $this->ln(4);
        $this->MultiCell(190,5,utf8_decode($this->texts->presentation->texto1),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->presentation->texto2),0,"J");$this->ln(5);
        $this->SetFont('Arial','',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->presentation->texto3),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->presentation->texto4),0,"J");$this->ln(8);
        $this->SetFont('Arial','B',9);
        $this->MultiCell(190,5,utf8_decode($this->texts->presentation->texto5),0,"J");$this->ln(3);
        $this->MultiCell(190,5,utf8_decode($this->texts->presentation->texto6),0,"J");
    }
    
    public function EnterpriseData($arrEnterprise,$arrContact = null,$arrIssues = null)
    {
        //$this->AddPage();
        
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','B',12);
        $texto1 = utf8_decode("Dados da Empresa");
        
        $this->MultiCell(190,8,$texto1,0,"L");
        $this->ln(2);
        
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(51,51,51);
        $this->SetLineWidth(.3);
        
        $wid1 = array('0' => '55', '1' => '135');
        $this->Cell(array_sum($wid1),0,'','B');
        $this->ln();
        
        $fill = false;
        foreach($arrEnterprise as $key => $row)
        {
            $this->SetFont('Arial','B','9');
            $this->Cell($wid1['0'],6,utf8_decode(" ".$key),'L',0,'L',$fill);
            $this->SetFont('Arial','','9');
            $this->Cell($wid1['1'],6,utf8_decode(" ".$row),'R',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($wid1),0,'','T');
        $this->ln(6);
        
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','B',12);
        $texto1 = utf8_decode("Dados do Contato da Empresa");
        
        $this->MultiCell(190,8,$texto1,0,"L");
        $this->ln(2);
        
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(51,51,51);
        $this->SetLineWidth(.3);
        
        $wid2 = array('0' => '55', '1' => '135');
        $this->Cell(array_sum($wid2),0,'','B');
        $this->ln();
        
        $fill = false;
        foreach($arrContact as $key => $row)
        {
            $this->SetFont('Arial','B','9');
            $this->Cell($wid2['0'],6,utf8_decode(" ".$key),'L',0,'L',$fill);
            $this->SetFont('Arial','','9');
            $this->Cell($wid2['1'],6,utf8_decode(" ".$row),'R',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($wid2),0,'','T');
        $this->ln(6);
        
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','B',12);
        $texto1 = utf8_decode("Respostas do cadastro da Empresa");
        
        $this->MultiCell(190,8,$texto1,0,"L");
        $this->ln(2);
        
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(51,51,51);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial','','9');
        
        $wid3 = array('0' => '90', '1' => '100');
        $this->Cell(array_sum($wid3),0,'','B');
        $this->ln();
        
        $fill = false;
        foreach($arrIssues as $row)
        {
            $this->Cell($wid3['0'],6,utf8_decode(" ".$row['Q']),'L',0,'L',$fill);
            $this->Cell($wid3['1'],6,utf8_decode($row['R']),'R',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($wid3),0,'','T');
        
    }
    
    public function Model()
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
        $this->Image($this->public_path.'/img/grafico.jpg',$xG+55,$yG+3,80,80);
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
    
    public function FirstPart($scorePart1)
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
    
    public function FirstTable($scorePart1) {
        
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
    
    public function Radar()
    {
        $this->AddPage();
    }
    
    public function SecondPart($arrCriteriaGes,$offSet,$strPathRadar,$scorePart1,$scorePart2)
    {
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
    
    public function Devolutive($arrDevolutive, $arrBlocks, $arrCriteria, $offSet = 0) 
    {
        $qst_number = $offSet;
        $blk_control = "";
        $crt_control = "";
        
        $this->SetDrawColor(218,223,227);
        $this->SetLineWidth(0.4);
        $this->Rect(10,23.5,190,8.5);
        
        $this->SetXY(12,25);
        
        $this->SetTextColor(120,120,120);
        $this->SetFont('Arial','BI',10);
        $this->Cell(20,6,'Legenda',0,0,"L");
        
        //$this->SetXY(30,25);
        
        //$this->SetTextColor(51,51,51);
        //$this->SetFont('Arial','B',14);
        //$this->Cell(6,6,utf8_decode('Nº'),0,0,"C");
        //$this->SetFont('Arial','',10);
        //$this->Cell(30,6,utf8_decode('= Questão'),0,0,"L");
        
        //$this->SetXY(80,25);
        $this->SetXY(30,25);
        
        $this->SetTextColor(51,138,158);
        $this->SetFont('Arial','B',14);
        $this->Cell(5,6,'R',0,0,"C");
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','',10);
        $this->Cell(30,6,utf8_decode('= Resposta'),0,0,"L");
        
        //$this->SetXY(120,25);
        $this->SetXY(60,25);
        
        $this->SetTextColor(0,168,71);
        $this->SetFont('Arial','B',14);
        $this->Cell(5,6,'D',0,0,"C");
        $this->SetTextColor(51,51,51);
        $this->SetFont('Arial','',10);
        $this->Cell(30,6,utf8_decode('= Devolutiva'),0,0,"L");
        
        if ($this->isRA) {
        
            $this->SetXY(95,25);

            $this->SetTextColor(226,127,61);
            $this->SetFont('Arial','B',14);
            $this->Cell(5,6,'PF',0,0,"C");
            $this->SetTextColor(51,51,51);
            $this->SetFont('Arial','',10);
            $this->Cell(30,6,utf8_decode('= Pontos Fortes'),0,0,"L");

            $this->SetXY(135,25);
            
            $this->SetTextColor(226,127,61);
            $this->SetFont('Arial','B',14);
            $this->Cell(5,6,'OM',0,0,"C");
            $this->SetTextColor(51,51,51);
            $this->SetFont('Arial','',10);
            $this->Cell(30,6,utf8_decode(' = Oportunidades de melhoria'),0,0,"L");            
            
        }
        
        $this->SetXY(10,35);
        
        $question_number = 0;
        $total_questions = count($arrDevolutive);
        
        foreach($arrDevolutive AS $chave => $question) {

            $question_number++;
            
            $qst_designation = utf8_decode($question['designation']);
            $qst_question = utf8_decode($question['value']);
            $qst_text = utf8_decode($question['text']);

            $blk_block_id = $question['block'];
            $blk_block = utf8_decode($arrBlocks[$blk_block_id]);

            $crt_criterion_id = $question['criterion'];
            $crt_criterion = utf8_decode($arrCriteria[$crt_criterion_id]);

            if (isset($question['alternative_id'])) {
                $alt_alternative_designation = utf8_decode($question['alternative_designation']);
                $alt_alternative_feedback = utf8_decode($question['alternative_feedback']);
                
                //campo Pontos Fortes
                if (isset($question['answer_feedback'])) {
                    $alt_answer_feedback = utf8_decode($question['answer_feedback']);
                } else {
                    $alt_answer_feedback = "";
                }
                
                //campo 'Oportunidades de melhoria'
                if (isset($question['answer_feedback_improve'])) {
                    $alt_answer_feedback_improve = utf8_decode($question['answer_feedback_improve']);
                } else {
                    $alt_answer_feedback_improve = "";
                }                
                
            } else {
                $alt_alternative_designation = "";
                $alt_alternative_feedback = "";
                $alt_answer_feedback = "";
                $alt_answer_feedback_improve = "";
            }
            
            if (isset($question['write_answer'])) {
                $ans_write_answer = utf8_decode($question['write_answer']);
            } else {
                $ans_write_answer = "";
            }
            
            if (isset($question['annual_result'])) {
                $ans_annual_result = $question['annual_result'];
                $ans_annual_result_unit = $question['annual_result_unit'];
            }
            
            $this->SetFillColor(214,232,237);
            
            if (($blk_control != $blk_block) && ($blk_block != "")) {
            
                $this->SetTextColor(51,51,51);
                $this->SetFont('Arial','BI',10);
                $this->MultiCell(190,8,utf8_decode("  Bloco ").$blk_block,0,"J",true);
                $this->Ln(3);
                $blk_control = $blk_block;
            }
            
            $this->SetFillColor(252,219,143);
            
            if (($crt_control != $crt_criterion) && ($blk_control == $blk_block) && ($crt_criterion != "")) {
                $this->SetTextColor(51,51,51);
                $this->SetFont('Arial','BI',10);
                $this->MultiCell(190,8,utf8_decode("  Critério ").$crt_criterion,0,"J",true);
                $this->Ln(3);
                $crt_control = $crt_criterion;
            }
            
            $this->SetFillColor(220,220,220);
            
            $qst_number++;
            $this->SetTextColor(51,51,51);
            $this->SetFont('Arial','B',30);
            $this->Cell(13,11,$qst_number,0,0,"C");
            $this->SetX(27);
            //$this->SetFont('Arial','B',10);
            //$this->MultiCell(173,4,$qst_question,0,"J");
            $this->SetFont('Arial','B',11);
            $this->MultiCell(173,5,$qst_question,0,"J");
            $this->Ln(2);
            $this->SetX(27);
            $this->SetTextColor(123,123,123);
            //$this->SetFont('Arial','',8);
            //$this->MultiCell(173,5,$qst_text,0,"J");
            $this->SetFont('Arial','',9);
            $this->MultiCell(173,5,$qst_text,0,"J");
            //$this->Ln(2);
            $this->Ln(5);
            
            $alt_text = "";
            $alt_answ = "";
            $this->SetTextColor(51,138,158);
            $this->SetFont('Arial','B',16);
            $this->Cell(15,6,'R',0,0,"C");
                        
            $arrAlfa = array('1'=>'A','2'=>'B','3'=>'C','4'=>'D');
            
            if (isset($question['alternatives'])) {
                foreach ($question['alternatives'] AS $alt_designation => $alt_value) {
                    
                    $alt_text = $arrAlfa[$alt_designation]." - ".$alt_value; 
                    $cY = $this->GetY();
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->SetTextColor(51,51,51);
                        //$this->SetFont('Arial','B',10);
                        $this->SetFont('Arial','B',11);
                    } else {
                        $this->SetTextColor(123,123,123);
                        //$this->SetFont('Arial','',8);
                        $this->SetFont('Arial','',9);
                    }
                    $this->SetX(35);
                    //$this->MultiCell(165,5,utf8_decode($alt_text),0,"J");
                    $this->MultiCell(165,6,utf8_decode($alt_text),0,"J");

                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->SetX(30);
                        $iX = $this->GetX();
                        $iY = $this->GetY()-5;
                        $iY = ($cY > $iY) ? $iY : $cY;
                        $this->Image($this->public_path.'/img/check.jpg',$iX,$iY,5);
                    } 
                    
                    //$this->Ln(1);
                    $this->Ln(2);
                }
            } else {
               $this->SetX(30);
               $this->Ln(6);       
            }
            
            if (isset($ans_write_answer) && ($ans_write_answer != "")) { 
                
                $this->ln(2);
                $this->SetX(35);
                $this->SetTextColor(51,51,51);
                //$this->SetFont('Arial','',8);
                $this->SetFont('Arial','',9);
                $alt_answ = $ans_write_answer; 
                //$this->MultiCell(165,5,"RESPOSTA ESCRITA: ".$alt_answ,0,"J");
                $this->MultiCell(165,6,"RESPOSTA ESCRITA: ".$alt_answ,0,"J");
            } 
            
            $this->ln(3);
            
            if (isset($ans_annual_result) && (count($ans_annual_result) == 3)) {
                
                $this->ln(1);
                $this->SetX(35);
                $this->SetTextColor(51,51,51);
                //$this->SetFont('Arial','',8);
                //$this->MultiCell(165,5,"RESULTADO ANUAL: ".$alt_answ,0,"J");
                $this->SetFont('Arial','',9);
                $this->MultiCell(165,6,"RESULTADO ANUAL: ".$alt_answ,0,"J");
                $this->ln(1);
                $this->SetX(35);
                
                foreach ($ans_annual_result AS $aar_year => $aar_value) {
                    //$this->SetFont('Arial','B',8);
                    //$this->Cell(20,5,utf8_decode($aar_year).": ",0,0,"R");
                    $this->SetFont('Arial','B',9);
                    $this->Cell(20,6,utf8_decode($aar_year).": ",0,0,"R");
                    //$this->SetFont('Arial','',8);
                    $this->SetFont('Arial','',9);
                    $aar_unit = "";
                    if ($ans_annual_result_unit != "") {
                        $aar_unit = "(".utf8_decode($ans_annual_result_unit).")";
                    } else {
                        $aar_unit = utf8_decode($ans_annual_result_unit);
                    }
                    //$this->Cell(35,5,($aar_value != "") ? utf8_decode($aar_value)." ".$aar_unit : "",0,0,"L");
                    $this->Cell(35,6,($aar_value != "") ? utf8_decode($aar_value)." ".$aar_unit : "",0,0,"L");
                }
                
                $this->Ln(5);
            }
                        
            $this->Ln(2);
            
            if ($alt_alternative_feedback != "" && $alt_alternative_feedback != '0') {
                $alt_feed = "";
                $this->SetTextColor(0,168,71);
                $this->SetFont('Arial','B',16);
                $this->Cell(15,6,'D',0,0,"C");
                $this->SetX(27);
                $this->SetTextColor(51,51,51);
                //$this->SetFont('Arial','BI',9);
                $this->SetFont('Arial','BI',10);
                if (isset($alt_alternative_feedback)) { $alt_feed = $alt_alternative_feedback; }
                //$this->MultiCell(173,5,$alt_feed,0,"J");
                $this->MultiCell(173,6,$alt_feed,0,"J");
            }
            
            //campo Pontos Fortes
            if ($alt_answer_feedback != "" && $alt_answer_feedback != '0' && $this->isRA) {
                $this->Ln(3);
                $ans_feed = "";
                $this->SetTextColor(226,127,61);
                $this->SetFont('Arial','B',16);
                $this->Cell(15,6,'PF',0,0,"C");
                $this->SetX(27);
                $this->SetTextColor(51,51,51);
                //$this->SetFont('Arial','BI',9);
                $this->SetFont('Arial','BI',10);
                if (isset($alt_answer_feedback)) { $ans_feed = $alt_answer_feedback; }
                //$this->MultiCell(173,5,$ans_feed,0,"J");
                $this->MultiCell(173,6,$ans_feed,0,"J");
            }
            
            
            //campo Oportunidades de melhoria
            if ($alt_answer_feedback_improve != "" && $alt_answer_feedback_improve != '0' && $this->isRA) {
                $this->Ln(3);
                $ans_feed = "";
                $this->SetTextColor(226,127,61);
                $this->SetFont('Arial','B',16);
                $this->Cell(15,6,'OM',0,0,"C");
                $this->SetX(27);
                $this->SetTextColor(51,51,51);
                
                $this->SetFont('Arial','BI',10);
                if (isset($alt_answer_feedback_improve)) { $ans_feed = $alt_answer_feedback_improve; }
                
                //grava campo no PDF
                $this->MultiCell(173,6,$ans_feed,0,"J");
            }
            
            
            $this->Ln(5);
            
            if ($question_number != $total_questions) {
                $this->AddPage();
            }
        
        }
    
        return $qst_number;
    }
    
}
