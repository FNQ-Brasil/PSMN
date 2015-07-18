<?php

/**
 * Classe responsavel pela Capa PDF da devolutiva.
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_Tipo_PSMN_ConteudoBlocoNegociosPdf
{
   
    protected $objMakePdf;
    
    protected $objDevolutive;
    
    protected $devolutivaPontosFortes;
    
    protected $devolutivaOportunidadesMelhoria;

    

    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf, Model_Devolutive $objDevolutive) 
    {                
        /** @var Vtx_Devolutive_MakePdf objMakePdf **/
        $this->objMakePdf = $objMakePdf;       
        /** @var Model_Devolutive $objDevolutive **/
        $this->objDevolutive = $objDevolutive;        
       
        $r = $this->defineBlocoQuestionario();        
        
        return $r;
    }
    
     
    public  function defineBlocoQuestionario()
    {
        $r = $this->montaDevolutivas();
        
//        var_dump('----PontosFortes-------------',$this->getDevolutivaPontosFortes());
//        echo "<br><br>";
//        var_dump('----OportunidadesMelhoria-------------',$this->getDevolutivaOportunidadesMelhoria());
//        exit;

        #bloco imprime
        $this->printBloco();
        
        $this->printPontosFortes();
        
        $this->printOportunidadesMelhoria();
        
        return $r;
    }
    
    
    public function printBloco()
    {
        $arrBlocks = $this->objDevolutive->getArrBlocksGov();
        /**          
          string '--arrBlocks----------' (length=21)
          array (size=1)
            1 => string 'Negócios' (length=9)
          
Seguem abaixo os comentários gerados a partir da autoavaliação do negócio que você respondeu ao se candidatar ao PSMN 2013. 
De acordo com a sua resposta serão apresentados seus Pontos Fortes e/ou suas Oportunidades de Melhoria.          
          
         */
        
        $titulo_parte1 = "Autoavaliação do Negócio"; // Devolutiva Parte I - Devolutiva Parte I - 
        $texto_parte1 = "Seguem abaixo os comentários gerados a partir da autoavaliação do negócio que você respondeu ao se candidatar ao PSMN 2014. ";
        $texto_parte1 .= "De acordo com a sua resposta serão apresentados seus Pontos Fortes e/ou suas Oportunidades de Melhoria. ";
        
        $this->objMakePdf->AddPage();
        
        //$this->objMakePdf->SetDrawColor(218,223,227);
        //$this->objMakePdf->SetLineWidth(0.4);
        //$this->objMakePdf->Rect(10,23.5,190,8.5);
        
        //$this->objMakePdf->SetXY(12,25);        
        
        //$this->objMakePdf->SetFillColor(214,232,237);

        //$blk_block = utf8_decode($arrBlocks[1]);
            
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont('Arial','B',12);
        //$this->objMakePdf->MultiCell(190,8,utf8_decode("  Bloco ").$blk_block,0,"J",true);
        $this->objMakePdf->Ln(3);

        /*
         * 
          $this->objMakePdf->MultiCell(190,5,$texto2,0,"J");
        $this->objMakePdf->ln(5); 
         * 
         */
        
        
        $this->objMakePdf->MultiCell(190,8,utf8_decode($titulo_parte1),0,"J");
        $this->objMakePdf->Ln(3);
        $this->objMakePdf->SetFont('Arial','B',10);
        $this->objMakePdf->MultiCell(190,8,utf8_decode($texto_parte1),0,"J");
            
        //$this->objMakePdf->SetFillColor(252,219,143);        
    }
    
    
    public function montaDevolutivas()
    {
        $qst_number = 0;// $this->objMakePdf->getOffset();
        $arrDevolutive = $this->objDevolutive->getArrDevolutiveGov();
        $arrBlocks = $this->objDevolutive->getArrBlocksGov();
        $arrCriteria = $this->objDevolutive->getArrCriteriaGov();
        
        //var_dump($arrDevolutive); exit('----------------fim--------');
        
        $blk_control = "";
        $crt_control = "";

        $question_number = 0;
        $total_questions = count($arrDevolutive);
        
        $devolutivaPontosFortes = array();
        $devolutivaOportunidadesMelhoria = array();
                
        /*****
        LOOP PARA QUESTOES
        ****/
        foreach($arrDevolutive AS $chave => $question) {
            $question_number++;
            
            $qst_designation = utf8_decode($question['designation']);
            
            //2o enunciado
            $enunciado = $question['text'];
            
            //dissertativa texto escrita pelo usuario
            $dissertativa = (isset($question['write_answer']) and $question['write_answer'])?$question['write_answer']:'';
            
            if (isset($question['alternative_id'])) {
                
                $alt_alternative_designation = $question['alternative_designation'];
                $alt_alternative_feedback = utf8_decode($question['alternative_feedback']);
                
                //campo Pontos Fortes
                /**
                 *  se o Designation da alternativa é 1,2,3 ou 4
                 * 
                 */
                 if ($alt_alternative_designation == '5') {
                      
                     $devolutivaPontosFortes[] = $alt_alternative_feedback;
                                           
                  }
                
                //campo 'Oportunidades de melhoria'
                /**
                 * se o Designation da alternativa é 5
                 * 
                 * 
                 */                
 
                  if ($alt_alternative_designation == '1' ||
                      $alt_alternative_designation == '2' ||
                      $alt_alternative_designation == '3' ||
                      $alt_alternative_designation == '4'                          
                      ) {                      
                      
                      $devolutivaOportunidadesMelhoria[] = $alt_alternative_feedback;
                  }
       
            } //fim alternativas
            
            
            $qst_number++;
            
        } //fim questoes
        
        
        //seta o array de OportunidadesMelhoria
        $this->setDevolutivaOportunidadesMelhoria($devolutivaOportunidadesMelhoria);
        
        //seta o array de PontosFortes
        $this->setDevolutivaPontosFortes($devolutivaPontosFortes);
        
        
//        var_dump('PF: ', $this->getDevolutivaPontosFortes());
//        echo "<br><BR>";
//        var_dump('OM: ', $this->getDevolutivaOportunidadesMelhoria());
//        
//        exit;
        
        
        return $qst_number;
        
    }//fim funcao montaDevolutivas()
    
    
    /**
     * printa ou não, Ponto Fortes na devolutiva
     */
    public function printPontosFortes()
    {
        // printa Pontos Fortes somente se ha PF
        if ($this->countArrayBoolean($this->getDevolutivaPontosFortes())) {
        
            //titulo Pontos Fortes
            $tituloPF = "Pontos Fortes";
            $this->objMakePdf->AddPage();
            $this->objMakePdf->SetTextColor(51,51,51);
            $this->objMakePdf->SetFont('Arial','B',14);
            $this->objMakePdf->MultiCell(190,8,utf8_decode($tituloPF),0,"J");
            $this->objMakePdf->Ln(5);

            $this->objMakePdf->SetFont('Arial','B',10);    
            foreach ( $this->getDevolutivaPontosFortes() as $campo ) {            

               $this->objMakePdf->MultiCell(173,6,"- " .$campo,0,"J");
               $this->objMakePdf->Ln(2);

            } //end foreach
        
        } //end if
                
    } //end function       
   
    /**
     * Printa ou nao, Oportunidades de Melhoria na devolutiva
     */
    public function printOportunidadesMelhoria()
    {
        //se retornar true, printa OM na devolutiva
        if ($this->countArrayBoolean($this->getDevolutivaOportunidadesMelhoria())) {
        
            //titulo OportunidadesMelhoria
            $this->objMakePdf->AddPage();
            $tituloOM = "Oportunidades de Melhoria";
            $this->objMakePdf->SetTextColor(51,51,51);
            $this->objMakePdf->SetFont('Arial','B',14);
            $this->objMakePdf->MultiCell(190,8,utf8_decode($tituloOM),0,"J");
            $this->objMakePdf->Ln(5);

            //$this->objMakePdf->MultiCell(173,6,$this->getDevolutivaOportunidadesMelhoria(),0,"J");
            $this->objMakePdf->SetFont('Arial','B',10);    
            foreach ( $this->getDevolutivaOportunidadesMelhoria() as $campo ) {            

               $this->objMakePdf->MultiCell(173,6,"- " .$campo,0,"J");
               $this->objMakePdf->Ln(2);

            } //end foreach
        } //end if
    } //end function

    /**
     * valida se Array possui valores
     * 
     * importante para printar ou nao algumas paginas dentrp da devolutiva
     * 
     * @param array $arr
     * @return boolean
     */
    public function countArrayBoolean(Array $arr)
    {
        $boolean = false;
        
        if (count($arr) > 0) {
            $boolean = true;
        }
        
        return $boolean;
    }


    public function getDevolutivaPontosFortes() {
        
        return  $this->devolutivaPontosFortes; //implode(",", $this->devolutivaOportunidadesMelhoria);
    }

    public function setDevolutivaPontosFortes($devolutivaPontosFortes) {
        $this->devolutivaPontosFortes = $devolutivaPontosFortes;
    }

    public function getDevolutivaOportunidadesMelhoria() {
        
        return $this->devolutivaOportunidadesMelhoria;//implode(",", $this->devolutivaOportunidadesMelhoria);
    }

    public function setDevolutivaOportunidadesMelhoria($devolutivaOportunidadesMelhoria) {
        $this->devolutivaOportunidadesMelhoria = $devolutivaOportunidadesMelhoria;
    }

    
    
    
    
    
    
    

    /**
     * @deprecated
     * 
     * 
     * monta bloco do questionario, com as questoes, criterios e respostas com alternativass
     * 
     * metodo refatorado a partir de model devolutive.php
     * 
     */
    //public function devolutiveBlocoQuestionario($arrDevolutive, $arrBlocks, $arrCriteria) 
    public function devolutiveBlocoQuestionario() 
    {
        $qst_number = $this->objMakePdf->getOffset();
        $arrDevolutive = $this->objMakePdf->getArrDevolutive();
        $arrBlocks = $this->objMakePdf->getArrBlocks();
        $arrCriteria = $this->objMakePdf->getArrCriteria();

        $blk_control = "";
        $crt_control = "";
        
        $this->objMakePdf->AddPage();
        
        $this->objMakePdf->SetDrawColor(218,223,227);
        $this->objMakePdf->SetLineWidth(0.4);
        $this->objMakePdf->Rect(10,23.5,190,8.5);
        
        $this->objMakePdf->SetXY(12,25);
        
        //monta a legenda da devolutiva
        $this->constroiLegenda();

        $question_number = 0;
        $total_questions = count($arrDevolutive);
        
        
        
        /*****
        LOOP PARA QUESTOES
        ****///
        foreach($arrDevolutive AS $chave => $question) {

            $question_number++;
            
            $qst_designation = utf8_decode($question['designation']);
            $qst_question = utf8_decode($question['value']);
            $qst_text = utf8_decode($question['text']);

            $blk_block_id = $question['block'];
            $blk_block = utf8_decode($arrBlocks[$blk_block_id]);

            $crt_criterion_id = $question['criterion'];
            $crt_criterion = utf8_decode($arrCriteria[$crt_criterion_id]);

            
                $alt_alternative_designation = "";
                $alt_alternative_feedback = "";
                $alt_answer_feedback = "";
                $alt_answer_feedback_improve = "";            
            
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
            } 
            
            $ans_write_answer = "";
            if (isset($question['write_answer'])) {
                $ans_write_answer = utf8_decode($question['write_answer']);
            } 
            
//            if (isset($question['annual_result'])) {
//                $ans_annual_result = $question['annual_result'];
//                $ans_annual_result_unit = $question['annual_result_unit'];
//            }
            
            $this->objMakePdf->SetFillColor(214,232,237);
            
            if (($blk_control != $blk_block) && ($blk_block != "")) {
            
                $this->objMakePdf->SetTextColor(51,51,51);
                $this->objMakePdf->SetFont('Arial','BI',10);
                $this->objMakePdf->MultiCell(190,8,utf8_decode("  Bloco ").$blk_block,0,"J",true);
                $this->objMakePdf->Ln(3);
                $blk_control = $blk_block;
            }
            
            //$this->objMakePdf->SetFillColor(252,219,143);
            
//            if (($crt_control != $crt_criterion) && ($blk_control == $blk_block) && ($crt_criterion != "")) {
//                $this->objMakePdf->SetTextColor(51,51,51);
//                $this->objMakePdf->SetFont('Arial','BI',10);
//                $this->objMakePdf->MultiCell(190,8,utf8_decode("  Critério ").$crt_criterion,0,"J",true);
//                $this->objMakePdf->Ln(3);
//                $crt_control = $crt_criterion;
//            }
            
//            $this->objMakePdf->SetFillColor(220,220,220);
//            
//            $qst_number++;
//            $this->objMakePdf->SetTextColor(51,51,51);
//            $this->objMakePdf->SetFont('Arial','B',30);
//            $this->objMakePdf->Cell(13,11,$qst_number,0,0,"C");
//            $this->objMakePdf->SetX(27);
//            //$this->SetFont('Arial','B',10);
//            //$this->MultiCell(173,4,$qst_question,0,"J");
//            $this->objMakePdf->SetFont('Arial','B',11);
//            $this->objMakePdf->MultiCell(173,5,$qst_question,0,"J");
//            $this->objMakePdf->Ln(2);
//            $this->objMakePdf->SetX(27);
//            $this->objMakePdf->SetTextColor(123,123,123);

            
//            $this->objMakePdf->SetFont('Arial','',9);
//            $this->objMakePdf->MultiCell(173,5,$qst_text,0,"J");
//            $this->objMakePdf->Ln(5);
            
//            $alt_text = "";
//            $alt_answ = "";
//            $this->objMakePdf->SetTextColor(51,138,158);
//            $this->objMakePdf->SetFont('Arial','B',16);
//            $this->objMakePdf->Cell(15,6,'R',0,0,"C");
//             
//            //gambiarra desativada e refatorada para metodo aliasParaAlternativaRespostaDaQuestao().
//            //$arrAlfa = array('1'=>'A','2'=>'B','3'=>'C','4'=>'D', '5' => 'E');
//            $arrAlfa = $this->aliasParaAlternativaRespostaDaQuestao();
            
            
            if (isset($question['alternatives'])) {
                foreach ($question['alternatives'] AS $alt_designation => $alt_value) {
                    
                    $alt_text = $arrAlfa[$alt_designation]." - ".$alt_value; 
                    $cY = $this->objMakePdf->GetY();
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->objMakePdf->SetTextColor(51,51,51);
                        //$this->SetFont('Arial','B',10);
                        $this->objMakePdf->SetFont('Arial','B',11);
                    } else {
                        $this->objMakePdf->SetTextColor(123,123,123);
                        //$this->SetFont('Arial','',8);
                        $this->objMakePdf->SetFont('Arial','',9);
                    }
                    $this->objMakePdf->SetX(35);
                    //$this->MultiCell(165,5,utf8_decode($alt_text),0,"J");
                    $this->objMakePdf->MultiCell(165,6,utf8_decode($alt_text),0,"J");

                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->objMakePdf->SetX(30);
                        $iX = $this->objMakePdf->GetX();
                        $iY = $this->objMakePdf->GetY()-5;
                        $iY = ($cY > $iY) ? $iY : $cY;
                        $this->objMakePdf->Image($this->objMakePdf->public_path.'/img/check.jpg',$iX,$iY,5);
                    } 
                    
                    //$this->Ln(1);
                    $this->objMakePdf->Ln(2);
                } //fim foreach
            } else {
               $this->objMakePdf->SetX(30);
               $this->objMakePdf->Ln(6);       
            }
            
//            if (isset($ans_write_answer) && ($ans_write_answer != "")) { 
//                
//                $this->objMakePdf->ln(2);
//                $this->objMakePdf->SetX(35);
//                $this->objMakePdf->SetTextColor(51,51,51);
//                //$this->SetFont('Arial','',8);
//                $this->objMakePdf->SetFont('Arial','',9);
//                $alt_answ = $ans_write_answer; 
//                //$this->MultiCell(165,5,"RESPOSTA ESCRITA: ".$alt_answ,0,"J");
//                $this->objMakePdf->MultiCell(165,6,"RESPOSTA ESCRITA: ".$alt_answ,0,"J");
//            } 
            
            $this->objMakePdf->ln(3);
            
//            if (isset($ans_annual_result) && (count($ans_annual_result) == 3)) {
//                
//                $this->objMakePdf->ln(1);
//                $this->objMakePdf->SetX(35);
//                $this->objMakePdf->SetTextColor(51,51,51);
//                //$this->SetFont('Arial','',8);
//                //$this->MultiCell(165,5,"RESULTADO ANUAL: ".$alt_answ,0,"J");
//                $this->objMakePdf->SetFont('Arial','',9);
//                $this->objMakePdf->MultiCell(165,6,"RESULTADO ANUAL: ".$alt_answ,0,"J");
//                $this->objMakePdf->ln(1);
//                $this->objMakePdf->SetX(35);
//                
//                foreach ($ans_annual_result AS $aar_year => $aar_value) {
//                    //$this->SetFont('Arial','B',8);
//                    //$this->Cell(20,5,utf8_decode($aar_year).": ",0,0,"R");
//                    $this->objMakePdf->SetFont('Arial','B',9);
//                    $this->objMakePdf->Cell(20,6,utf8_decode($aar_year).": ",0,0,"R");
//                    //$this->SetFont('Arial','',8);
//                    $this->objMakePdf->SetFont('Arial','',9);
//                    $aar_unit = "";
//                    if ($ans_annual_result_unit != "") {
//                        $aar_unit = "(".utf8_decode($ans_annual_result_unit).")";
//                    } else {
//                        $aar_unit = utf8_decode($ans_annual_result_unit);
//                    }
//                    //$this->Cell(35,5,($aar_value != "") ? utf8_decode($aar_value)." ".$aar_unit : "",0,0,"L");
//                    $this->objMakePdf->Cell(35,6,($aar_value != "") ? utf8_decode($aar_value)." ".$aar_unit : "",0,0,"L");
//                }
//                
//                $this->objMakePdf->Ln(5);
//            }
                        
            $this->objMakePdf->Ln(2);
            
//            if ($alt_alternative_feedback != "" && $alt_alternative_feedback != '0') {
//                $alt_feed = "";
//                $this->objMakePdf->SetTextColor(0,168,71);
//                $this->objMakePdf->SetFont('Arial','B',16);
//                $this->objMakePdf->Cell(15,6,'D',0,0,"C");
//                $this->objMakePdf->SetX(27);
//                $this->objMakePdf->SetTextColor(51,51,51);
//                //$this->SetFont('Arial','BI',9);
//                $this->objMakePdf->SetFont('Arial','BI',10);
//                if (isset($alt_alternative_feedback)) { $alt_feed = $alt_alternative_feedback; }
//                //$this->MultiCell(173,5,$alt_feed,0,"J");
//                $this->objMakePdf->MultiCell(173,6,$alt_feed,0,"J");
//            }
            
            //campo Pontos Fortes
            if ($alt_answer_feedback != "" && $alt_answer_feedback != '0' && $this->objMakePdf->isRA) {
                $this->objMakePdf->Ln(3);
                $ans_feed = "";
                $this->objMakePdf->SetTextColor(226,127,61);
                $this->objMakePdf->SetFont('Arial','B',16);
                $this->objMakePdf->Cell(15,6,'PF',0,0,"C");
                $this->objMakePdf->SetX(27);
                $this->objMakePdf->SetTextColor(51,51,51);
                //$this->SetFont('Arial','BI',9);
                $this->objMakePdf->SetFont('Arial','BI',10);
                if (isset($alt_answer_feedback)) { $ans_feed = $alt_answer_feedback; }
                //$this->MultiCell(173,5,$ans_feed,0,"J");
                $this->objMakePdf->MultiCell(173,6,$ans_feed,0,"J");
            }
            
            
            //campo Oportuni$arrAlfadades de melhoria
            if ($alt_answer_feedback_improve != "" && $alt_answer_feedback_improve != '0' && $this->objMakePdf->isRA) {
                $this->objMakePdf->Ln(3);
                $ans_feed = "";
                $this->objMakePdf->SetTextColor(226,127,61);
                $this->objMakePdf->SetFont('Arial','B',16);
                $this->objMakePdf->Cell(15,6,'OM',0,0,"C");
                $this->objMakePdf->SetX(27);
                $this->objMakePdf->SetTextColor(51,51,51);
                
                $this->objMakePdf->SetFont('Arial','BI',10);
                if (isset($alt_answer_feedback_improve)) { $ans_feed = $alt_answer_feedback_improve; }
                
                //grava campo no PDF
                $this->objMakePdf->MultiCell(173,6,$ans_feed,0,"J");
            }
            
            
            $this->objMakePdf->Ln(5);
            
            if ($question_number != $total_questions) {
                $this->objMakePdf->AddPage();
            }
        
        } //fim foreach em $arrDevolutive
    
        return $qst_number;
    }
    
    /**
     * @deprecated
     * 
     * 
     * texto alias para a alternativa da questao
     * 
     * @return array
     */
    public function aliasParaAlternativaRespostaDaQuestao($questionType=1)
    {
        switch($questionType){

            default:
            $alias = array( '1'=>'A',
                            '2'=>'B',
                            '3'=>'C',
                            '4'=>'D',
                            '5'=>'E'
                     );        
            
        }
        return $alias;
    }
    
    /**
     * @deprecated since version number
     * 
     * metodo que cria Leganda no pdf da Devolutiva - 
     * 
     * @depends FPDF
     * 
     */
    public function constroiLegenda()
    {
        ////
        $this->objMakePdf->SetTextColor(120,120,120);
        $this->objMakePdf->SetFont('Arial','BI',10);
        $this->objMakePdf->Cell(20,6,'Legenda',0,0,"L");
        
        //$this->SetXY(30,25);
        
        //$this->SetTextColor(51,51,51);
        //$this->SetFont('Arial','B',14);
        //$this->Cell(6,6,utf8_decode('Nº'),0,0,"C");
        //$this->SetFont('Arial','',10);
        //$this->Cell(30,6,utf8_decode('= Questão'),0,0,"L");
        
        //$this->SetXY(80,25);
        $this->objMakePdf->SetXY(30,25);
        
        $this->objMakePdf->SetTextColor(51,138,158);
        $this->objMakePdf->SetFont('Arial','B',14);
        $this->objMakePdf->Cell(5,6,'R',0,0,"C");
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont('Arial','',10);
        $this->objMakePdf->Cell(30,6,utf8_decode('= Resposta'),0,0,"L");
        
        //$this->SetXY(120,25);
        $this->objMakePdf->SetXY(60,25);
        
        $this->objMakePdf->SetTextColor(0,168,71);
        $this->objMakePdf->SetFont('Arial','B',14);
        $this->objMakePdf->Cell(5,6,'D',0,0,"C");
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont('Arial','',10);
        $this->objMakePdf->Cell(30,6,utf8_decode('= Devolutiva'),0,0,"L");
        
        if ($this->objMakePdf->getIsRA()) {
        
            $this->objMakePdf->SetXY(95,25);

            $this->objMakePdf->SetTextColor(226,127,61);
            $this->objMakePdf->SetFont('Arial','B',14);
            $this->objMakePdf->Cell(5,6,'PF',0,0,"C");
            $this->objMakePdf->SetTextColor(51,51,51);
            $this->objMakePdf->SetFont('Arial','',10);
            $this->objMakePdf->Cell(30,6,utf8_decode('= Pontos Fortes'),0,0,"L");

            $this->objMakePdf->SetXY(135,25);
            
            $this->objMakePdf->SetTextColor(226,127,61);
            $this->objMakePdf->SetFont('Arial','B',14);
            $this->objMakePdf->Cell(5,6,'OM',0,0,"C");
            $this->objMakePdf->SetTextColor(51,51,51);
            $this->objMakePdf->SetFont('Arial','',10);
            $this->objMakePdf->Cell(30,6,utf8_decode(' = Oportunidades de melhoria'),0,0,"L");            
            
        }
        
        $this->objMakePdf->SetXY(10,35);        
    }
    

    
        
    
    
} //end class

?>
