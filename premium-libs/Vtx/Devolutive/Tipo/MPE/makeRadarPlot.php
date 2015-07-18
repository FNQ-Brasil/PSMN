<?php


/**
 * Description of makeRadarPlot
 *
 * @author vtx
 */
class Vtx_Devolutive_Tipo_MPE_makeRadarPlot {

    
    protected $arrCriteria;
    protected $arrRadarData;
    protected  $arrTabulation;
    protected  $arrPunctuation;

    public function getArrCriteria() {
        return $this->arrCriteria;
    }

    public function setArrCriteria($arrCriteria) {
        $this->arrCriteria = $arrCriteria;
    }

    public function getArrRadarData() {
        return $this->arrRadarData;
    }

    public function setArrRadarData($arrRadarData) {
        $this->arrRadarData = $arrRadarData;
    }

    public function getArrTabulation() {
        return $this->arrTabulation;
    }

    public function setArrTabulation($arrTabulation) {
        $this->arrTabulation = $arrTabulation;
    }

    public function getArrPunctuation() {
        return $this->arrPunctuation;
    }

    public function setArrPunctuation($arrPunctuation) {
        $this->arrPunctuation = $arrPunctuation;
    }

        
    /**
     * 
     * 
     * @param type $arrCriteria
     * @param type $arrRadarData
     * @param type $arrTabulation
     * @param type $arrPunctuation
     * @param type $dirName
     * @return boolean|string
     */
    public function makeRadarPlot($arrCriteria, $arrRadarData, $arrTabulation, $arrPunctuation, $dirName, $strCiclo) 
    {
//        var_dump('arrCriteria: ',$arrCriteria);
//        echo "<br><br>";
        //var_dump('arrRadarData: ',$arrRadarData);
        //echo "<br><br>";
//        var_dump('arrTabulation: ',$arrTabulation);
//        echo "<br><br>";
//        var_dump('arrPunctuation: ',$arrPunctuation);
//        
//        //exit;
        
        // content="text/plain; charset=utf-8"       
        require_once (APPLICATION_PATH_LIBS . '/jpgraph/src/jpgraph.php');
        require_once (APPLICATION_PATH_LIBS . '/jpgraph/src/jpgraph_radar.php');
        
        $criterios = array();

        $criterios[1] = utf8_decode($arrCriteria[1]);

        $criterios[8] = utf8_decode($arrCriteria[8]);

        $criterios[7] = utf8_decode($arrCriteria[7]);

        $criterios[6] = utf8_decode($arrCriteria[6]);

        $criterios[5] = utf8_decode($arrCriteria[5]);

        $criterios[4] = utf8_decode($arrCriteria[4]);

        $criterios[3] = utf8_decode($arrCriteria[3]);

        $criterios[2] = utf8_decode($arrCriteria[2]);

        //var_dump('criterios: ', $criterios);
        //echo "<br><br>";
        
        if(!is_array($arrRadarData)) {
            return false;
        }

        //mudanca de ultima hora para alterar o sentido no print do radar na devolutiva.
        //nao ha um metodo na lib jpgraph que altere o sentido de rotacao ao printar o grafico radar.
        $arrRadarDataMartelada = array();
        $arrRadarDataMartelada[1] = $arrRadarData[1];
        $arrRadarDataMartelada[8] = $arrRadarData[8];
        $arrRadarDataMartelada[7] = $arrRadarData[7];
        $arrRadarDataMartelada[6] = $arrRadarData[6];
        $arrRadarDataMartelada[5] = $arrRadarData[5];
        $arrRadarDataMartelada[4] = $arrRadarData[4];
        $arrRadarDataMartelada[3] = $arrRadarData[3];
        $arrRadarDataMartelada[2] = $arrRadarData[2];
        
        $titles = array_values($criterios);
        
        //$data = array_values($arrRadarData);
        
        //implementa martelada
        $data = array_values($arrRadarDataMartelada);

        //var_dump('arrRadarDataMartelada', $arrRadarDataMartelada);
        //echo "<br><br>";
        //var_dump('data', $data);
        
        $this->setArrCriteria($arrCriteria);
        $this->setArrPunctuation($arrPunctuation);
        $this->setArrRadarData($arrRadarData);
        $this->setArrTabulation($arrTabulation);
        
        
        $graph = new RadarGraph (635,355); 
        $graph->SetShadow();
        $graph->SetScale('lin', $aYMin=0, $aYMax=100);
        $graph->yscale->ticks->Set(50,10);
        
        //$graph->title->Set("Porcentagem de acertos por Critério");       
        $programaTipo = Zend_Registry::get('programaTipo');
        
        $strCiclo = ($programaTipo != 'MpeBrasil')?'':' Ciclo '.$strCiclo;
        
        $tituloCiclo = "Desempenho da Empresa".$strCiclo;
        $graph->title->Set($tituloCiclo);
        
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
    
    /**
     * faz calculo da pontuacao para tabela Radar
     */
    public function dadosTabelaPontuacao()
    {
                
        $arrCriteria= $this->getArrCriteria();
        $arrPunctuation = $this->getArrPunctuation();     
        $arrRadarData = $this->getArrRadarData();
        $arrTabulation = $this->getArrTabulation();
        
        $pontuacaoMaxima = Vtx_Util_Array::pontuacaoMaximaCriteriosGestao();
        #('15,0', '9,0', '9,0', '6,0', '6,0','9,0','16,0','30,0' );
        
        $objPontuacao = new stdClass();
        //Lideranca
        $objPontuacao->Lideranca = new stdClass();
        $objPontuacao->Lideranca->nome = $arrCriteria[1];
        $objPontuacao->Lideranca->pontuacaoMaxima = $pontuacaoMaxima[1];
        $objPontuacao->Lideranca->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[1]);
        $objPontuacao->Lideranca->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[1]);

        //Estrategias e Planos
        $objPontuacao->Estrategias = new stdClass();
        $objPontuacao->Estrategias->nome = $arrCriteria[2];
        $objPontuacao->Estrategias->pontuacaoMaxima = $pontuacaoMaxima[2];
        $objPontuacao->Estrategias->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[2]);
        $objPontuacao->Estrategias->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[2]);

        //Clientes
        $objPontuacao->Clientes = new stdClass();
        $objPontuacao->Clientes->nome = $arrCriteria[3];
        $objPontuacao->Clientes->pontuacaoMaxima = $pontuacaoMaxima[3];
        $objPontuacao->Clientes->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[3]);
        $objPontuacao->Clientes->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[3]);
        
        
        //Sociedade
        $objPontuacao->Sociedade = new stdClass();
        $objPontuacao->Sociedade->nome = $arrCriteria[4];
        $objPontuacao->Sociedade->pontuacaoMaxima = $pontuacaoMaxima[4];
        $objPontuacao->Sociedade->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[4]);
        $objPontuacao->Sociedade->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[4]);        
        
        
        //Informações e Conhecimento
        $objPontuacao->Informacoes = new stdClass();
        $objPontuacao->Informacoes->nome = $arrCriteria[5];
        $objPontuacao->Informacoes->pontuacaoMaxima = $pontuacaoMaxima[5];
        $objPontuacao->Informacoes->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[5]);
        $objPontuacao->Informacoes->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[5]);
        
        //Pessoas
        $objPontuacao->Pessoas = new stdClass();
        $objPontuacao->Pessoas->nome = $arrCriteria[6];
        $objPontuacao->Pessoas->pontuacaoMaxima = $pontuacaoMaxima[6];
        $objPontuacao->Pessoas->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[6]);
        $objPontuacao->Pessoas->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[6]);        
        
        
        //Processos
        $objPontuacao->Processos = new stdClass();
        $objPontuacao->Processos->nome = $arrCriteria[7];
        $objPontuacao->Processos->pontuacaoMaxima = $pontuacaoMaxima[7];
        $objPontuacao->Processos->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[7]);
        $objPontuacao->Processos->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[7]);
        
        
        //Resultados
        $objPontuacao->Resultados = new stdClass();
        $objPontuacao->Resultados->nome = $arrCriteria[8];
        $objPontuacao->Resultados->pontuacaoMaxima = $pontuacaoMaxima[8];
        $objPontuacao->Resultados->pontuacaoObtida = Vtx_Util_Formatting::roundAndDouble($arrPunctuation[8]);
        $objPontuacao->Resultados->porcentagem = Vtx_Util_Formatting::roundAndDouble($arrRadarData[8]);        
                
        
        return $objPontuacao;
        
    }

    
}
