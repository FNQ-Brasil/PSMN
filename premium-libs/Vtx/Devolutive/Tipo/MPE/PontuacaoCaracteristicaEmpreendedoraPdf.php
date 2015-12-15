<?php

/**
 * Classe responsavel pela  PontuacaoCaracteristicaEmpreendedora do PDF da devolutiva.
 * 
 * - constroi tabela com dados Pontuacao
 * - constroi imagem com grafico da pontuacao
 * 
 * @depends JPGRAPH
 * @depends FPDF
 * 
 * @author esilva
 */
class Vtx_Devolutive_Tipo_MPE_PontuacaoCaracteristicaEmpreendedoraPdf
{
    
    const JPGRAPH_LIB = '/jpgraph/src/jpgraph.php';
    const JPGRAPH_LIB_BAR = '/jpgraph/src/jpgraph_bar.php';
    
    protected $objMakePdf;
    
    protected $objDevolutive;

    protected $arrPontuacao;

    protected $pathImagem;

    const HABILITA_TABELA_PONTUACAO = true;
    
    const HABILITA_GRAFICO_PONTUACAO = true;
    
    const IMAGE_JPGRAPH_DEVOLUTIVE = "image_bar.png";
    

    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf, Model_Devolutive $objDevolutive) 
    {                
        /** @var Vtx_Devolutive_MakePdf objMakePdf **/
        $this->objMakePdf = $objMakePdf;       
        /** @var Model_Devolutive $objDevolutive **/
        $this->objDevolutive = $objDevolutive;

        $this->loadClasses();
        
        $this->textoParaTabela_e_Grafico();
        
        if (self::HABILITA_TABELA_PONTUACAO) {
            $this->geraPontuacaoCaracteristicaEmpreendedora() ;
        }
        if (self::HABILITA_GRAFICO_PONTUACAO) {
            $this->geraGraficoPontuacao(); //geraGraficoPontuacao()
        }
    }
    
    /**
     * classes para lib jpgraph
     */
    private function loadClasses()
    {
        require_once (APPLICATION_PATH_LIBS . self::JPGRAPH_LIB);
        require_once (APPLICATION_PATH_LIBS . self::JPGRAPH_LIB_BAR);        
    }
    
    /**
     * Recupera dados da Pontuacao
     * 
     * tabela BlockGradeEnterpreneur
     * 
     * Todos os dados referente a pontuacao de um Usuario sao gerados na execucao do
     * controller DevolutivaController, que executa a procedure p_pontuacao_grade.sql
     * 
     * @return
     */
    public function getDadosGrade()
    {        
        $grade = new Model_BlockEnterpreneurGrade();
        $userId = $this->objDevolutive->getUserId();
        $questionnaireId = $this->objDevolutive->getQuestionnaireId();
        $blockIdEmpreendedorismo = $this->objDevolutive->getBlockIdEmpreendedorismo();
        $programaId = $this->objDevolutive->getProgramaId();
        
        $objPontuacao = $grade->getAll( array('UserId = ?' => $userId,
                                              'QuestionnaireId = ?' => $questionnaireId,
                                              'BlockId = ?'=> $blockIdEmpreendedorismo,
                                              'CompetitionId = ?'=> $programaId
                                              ), null, null, null
                                       );
   
        $this->setArrPontuacao( $objPontuacao->toArray() );

     }
     
    /**
     * Insere PontuacaoCaracteristicaEmpreendedora na devolutiva
     * 
     * @depends FPDF
     */
    public function geraPontuacaoCaracteristicaEmpreendedora() 
    {
        $this->getDadosGrade();
               
        $this->renderizaTabela(); 
    }
    
    /**
     * tabela com Caracteristicas Empreendedoras
     * 
     * @depends FPDF
     * 
     */
    public function renderizaTabela()
    {       
        $fonte = 'Myriad';
        
        //$pdf = new PDF();
        // Column headings
        $header = array(utf8_decode('Caracteristica Empreendedora'), utf8_decode('Pontuação Obtida'), utf8_decode('Pontuação Máxima'));
        // Data loading
        //$data = $this->objMakePdf->LoadData('countries.txt');
        $data = $this->getArrPontuacao();
        $this->objMakePdf->SetFont($fonte,'',12);

        //$this->objMakePdf->AddPage();
        $this->FancyTable($header,$data);
        //$pdf->Output();           
    }
    
    /**
     * texto antes do print da tabela e grafico
     */
    protected function textoParaTabela_e_Grafico()
    {
               
        ##pagina com fundo pagina pdf
        //adicionar numero pagina correto
        $npage = 0;

        //insere fundo PDF design devolutiva
        $this->objMakePdf->AddPage();
        $page_fundo = "fundo-mpe.pdf";
        $pags = array($page_fundo);
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);

        //$this->objMakePdf->MultiCell(190,8,utf8_decode("  Tabela Pontuacao Comportamento Empreendedor "),0,"J",true);

        $this->objMakePdf->Ln(3);

        ######### fim pagina 1 da Parte 2          
        //$texto = "Ao interpretar a sua pontuação no perfil empreendedor, você deve considerar que a escala pontua o percentual de cada uma das características de comportamento empreendedor.";
        //$tituloPontuacao = "Pontuação Obtida";
        //$this->objMakePdf->Ln(1);        
        $this->objMakePdf->SetTextColor(51, 51, 51);
        $this->objMakePdf->SetFont('Myriad', 'B', 10);

        //$this->objMakePdf->MultiCell(190,8,utf8_decode($texto),0,"J");          
        //$this->objMakePdf->Ln(1);        
        //$this->objMakePdf->MultiCell(190,8,utf8_decode($tituloPontuacao),0,"J");          
        $this->objMakePdf->Ln(15);
    }

    
    /**
     * // Colored table
     * 
     * Codigo from:
     * http://www.fpdf.org/en/tutorial/tuto5.htm
     * @depends FPDF
     * @param type $header
     * @param type $data
     */
    public function FancyTable($header, $data)
    {
        $fonte = 'Myriad';
      
        $this->objMakePdf->SetX(30);
        
        // Colors, line width and bold font
        $this->objMakePdf->SetFillColor(168,186,233);    #SetFillColor(255,0,0);
        $this->objMakePdf->SetTextColor(0);   #SetTextColor(255); branco #SetTextColor(0); preto
        $this->objMakePdf->SetDrawColor(128,0,0);
        $this->objMakePdf->SetLineWidth(.3);
        $this->objMakePdf->SetFont($fonte,'','10');
        // Header
        //$w = array(80, 35, 40, 45);
        $w = array(80, 35, 40);
        for($i=0;$i<count($header);$i++)
            $this->objMakePdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->objMakePdf->Ln();
        // Color and font restoration
        $this->objMakePdf->SetFillColor(224,235,255);
        $this->objMakePdf->SetTextColor(0);
        $this->objMakePdf->SetFont($fonte,'','10');
        // Data
        $fill = false;
        $total = 0;
        foreach($data as $row)
        {
            $this->objMakePdf->SetX(30);
            $description = utf8_decode($row['EnterpreneurFeatureId'].". ".$row['Description']);
            $points = $this->pointsPorcentagem( $row['Points'] );
            $coluna3 = "100%";
            
            $total = $total + $points;
            $this->objMakePdf->Cell($w[0],6,$description,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$points."%",'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,isset($coluna3)?$coluna3:'','LR',0,'C',$fill);
            //$this->objMakePdf->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;
        }
            $this->objMakePdf->SetX(30);
            //media total
            $media_total = $total/10;
            $this->objMakePdf->Cell($w[0],6,'Total','LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[1],6,$media_total."%",'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,isset($coluna3)?$coluna3:'','LR',0,'C',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;      
            
            $this->objMakePdf->SetX(30);
            // Closing line
            $this->objMakePdf->Cell(array_sum($w),0,'','T');
            $this->objMakePdf->Ln(1);
    }    
    /**
     * 
     * @param type $points
     * @param string $tipo
     * @return type
     */
    protected function pointsPorcentagem($points,$tipo=null)
    {
        $p = $points * 4;
        
        $result = $p . "%";
        if ($tipo = 'double') {
            $result = $p;
        }

        return $result;
    }
    
    
    /****************************************************************************
     * grafico Jpgraph
     * 
     * **************************************************************************
     */
    
    
    /**
     * 
     */
    public function geraGraficoPontuacao()
    {
        $this->gravaImagemGraficaParaPontuacao();
        $this->leituraGraficoPontuacao();
    }



    
    /**
     * @depends Jpgraph
     * 
     * Cria jpgraf e grava jpg
     * 
     * retorna URL da imagem jpgraph
     * 
     * @return boolean|string
     * 
     */
    public function gravaImagemGraficaParaPontuacao()
    {  
        
        $dirName = $this->objDevolutive->getDirName();
        $data = $this->getArrPontuacao();
        
        $total = "100%";
        $points = array();
        foreach ($data as $c) {
            $item = $c['EnterpreneurFeatureId'];
            $points[] = $this->pointsPorcentagem( $c['Points'], 'double');
            
        }
        
        $titulo_do_grafico = "";
        
        $titulo_medida_Y = "PORCENTAGEM ( % )";
        $titulo_medida_X = "Caracteristica Empreendedora";
        
        //$datay=array(12,8,19,3,10,5);
        $datay = array_values($points);

        // Create the graph. These two calls are always required
        $graph = new Graph(600,500);
        //$graph->SetScale('intlin');
        $graph->SetScale('textlin');

        
        // Add a drop shadow
        $graph->SetShadow(true, 5);

        // Adjust the margin a bit to make more room for titles
        $graph->SetMargin(50,50,40,50);

        // Create a bar pot
        $bplot = new BarPlot($datay);
        $bplot->SetLegend($titulo_medida_X);
        

        // Adjust fill color
        $bplot->SetFillColor('orange');
        $graph->Add($bplot);

        // Setup the titles
        $graph->title->Set($titulo_do_grafico);
        $graph->xaxis->title->Set('');
        $graph->yaxis->title->Set($titulo_medida_Y);
        $graph->yscale->SetAutoMax(100);

        $graph->title->SetFont(FF_FONT1,FS_BOLD);
        $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD,11);
        $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

        $pathImagem = $dirName.self::IMAGE_JPGRAPH_DEVOLUTIVE;
        
        //echo 'pathImagem: '.$pathImagem; exit;
        
        // Display the graph
        $graph->Stroke($pathImagem);        
        
        $this->setPathImagem($pathImagem);
        
        
        //-------
//        $criterios = array();
//        foreach($arrCriteria AS $chave => $valor) {
//            $criterios[$chave] = utf8_decode(" ".$chave." - ".$valor);
//        }
//        
//        if(!is_array($arrRadarData)) {
//            return false;
//        }
//
//        $titles = array_values($criterios);
//        $data = array_values($arrRadarData);
//            
//        $graph = new RadarGraph (635,355); 
//        $graph->SetShadow();
//        $graph->SetScale('lin', $aYMin=0, $aYMax=100);
//        $graph->yscale->ticks->Set(50,10);
//        
//        $graph->title->Set("Porcentagem de acertos por Critério");
//        $graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);
//
//        //$graph->subtitle->Set("Pontuação por Critério em %");
//        //$graph->subtitle->SetFont(FF_VERDANA,FS_ITALIC,10);
//        
//        $graph->SetTitles($titles);
//        $graph->SetCenter(0.50,0.54);
//        //$graph->HideTickMarks(); 
//        $graph->ShowMinorTickMArks();
//        $graph->SetColor('white');
//        $graph->grid->SetLineStyle('dashed');
//        $graph->axis->SetColor('darkgray@0.3'); 
//        $graph->grid->SetColor('darkgray@0.3');
//        $graph->grid->Show();
//        
//        $graph->SetGridDepth(DEPTH_BACK);
//
//        $plot = new RadarPlot($data);
//        $plot->SetColor('red@0.2');
//        $plot->SetLineWeight(3);
//        $plot->SetFillColor('skyblue4@0.7');
//        $graph->Add($plot);
//        
//        $radarPath = $dirName."radarTMP.png";
//        
//        $graph->Stroke($radarPath);
//        
//        return $radarPath;
        
    }
    
    /**
     * @depends Jpgraph
     * @param type $strPathRadar
     */
    public function leituraGraficoPontuacao()
    {
        $strPathImagem = $this->getPathImagem();
        
        $xG = $this->objMakePdf->GetX();
        $yG = $this->objMakePdf->GetY();
        
        $width ='' ;#140;
        $height ='' ; #80;
        
        //$this->objMakePdf->Ln(3);
        
        $this->objMakePdf->Image($strPathImagem, $xG+25, $yG+3, $width, $height );

        //$this->objMakePdf->Ln(3);        
        
//        //caso graficoPontuacao esta habilitado
//        if($this->objPsmn->getGraficoPontuacao()) {
//            $xG = $this->objMakePdf->GetX();
//            $yG = $this->objMakePdf->GetY();
//            $this->objMakePdf->Image($strPathRadar,$xG+25,$yG+3,140,80);
//
//            $this->objMakePdf->Ln(5);
//
//        } else {
//            $this->objMakePdf->SetTextColor(255,0,0);
//            $this->objMakePdf->MultiCell(190,5,utf8_decode("Faltam dados para geração do gráfico radar.
//                Informe a pontuação de cada alternativa no cadastro de questões."),0,"C");$this->ln(3);
//            $this->objMakePdf->SetTextColor(51,51,51);
//        }        
     }    
    
    /**
     * 
     * @deprecated
     * 
     * @return boolean|string
     */
    public function oldfunction()
    {
        // content="text/plain; charset=utf-8"       
        
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
    
    public function getArrPontuacao() {
        return $this->arrPontuacao;
    }

    public function setArrPontuacao($arrPontuacao) {
              
        $this->arrPontuacao = $arrPontuacao;
    }

    public function getPathImagem() {
        return $this->pathImagem;
    }

    public function setPathImagem($pathImagem) {
        $this->pathImagem = $pathImagem;
    }

        
    
    
} //end class
