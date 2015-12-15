<?php

/**
 * Classe responsavel pelo questionario Empreendedorismo
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_Tipo_PSMN_ConteudoBlocoEmpreendedorismoPdf
{
   
    protected $objMakePdf;
    
    protected $objDevolutive;
    

    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf, Model_Devolutive $objDevolutive) 
    {                
        /** @var Vtx_Devolutive_MakePdf objMakePdf **/
        $this->objMakePdf = $objMakePdf;       
        /** @var Model_Devolutive $objDevolutive **/
        $this->objDevolutive = $objDevolutive;      
        
        $r = $this->defineBlocoQuestionario();        
        
        return $r;
    }
    
     
    public function defineBlocoQuestionario()
    {
        
        $r = $this->devolutiveBlocoQuestionario();
        
        return $r;
    }
    
    /**
     * 
     */
    protected function criaEnunciado()
    {
        $this->objMakePdf->AddPage();
        
        $titulo_parte1 = "Devolutiva - Características do Comportamento Empreendedor"; // Devolutiva Parte II -  
        $texto_parte1 = " ";
            
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont('Arial','B',12);
        //$this->objMakePdf->MultiCell(190,8,utf8_decode("  Bloco ").$blk_block,0,"J",true);
        //$this->objMakePdf->Ln(1);
        
        $this->objMakePdf->MultiCell(190,8,utf8_decode($titulo_parte1),0,"J");
        //$this->objMakePdf->Ln(1);
        $this->objMakePdf->SetFont('Arial','B',10);
        $this->objMakePdf->MultiCell(190,8,utf8_decode($texto_parte1),0,"J");        
                        
/**  LEGENDA
        //$this->objMakePdf->SetDrawColor(218,223,227);
        //$this->objMakePdf->SetLineWidth(0.4);
        //$this->objMakePdf->Rect(10,23.5,190,8.5);
        
        //$this->objMakePdf->SetXY(12,25);
        
        //monta a legenda da devolutiva
        //$this->constroiLegenda();
***/        
    }


    /**
     * monta bloco do questionario, com as questoes, criterios e respostas com alternativass
     * 
     * metodo refatorado a partir de model devolutive.php
     * 
     */
    //public function devolutiveBlocoQuestionario($arrDevolutive, $arrBlocks, $arrCriteria) 
    public function devolutiveBlocoQuestionario() 
    {
        $qst_number = $this->objMakePdf->getOffset();
        $arrDevolutive = $this->objDevolutive->getArrDevolutiveGes();
        $arrBlocks = $this->objDevolutive->getArrBlocksGes();
        $arrCriteria = $this->objDevolutive->getArrCriteriaGes();
        
        $this->criaEnunciado();

        $blk_control = "";
        $crt_control = "";
        
//       var_dump('ARRdEVOLUTIVE',$arrDevolutive);
//       exit;
        
        $question_number = 0;
        $total_questions = count($arrDevolutive);
        
        // loop para listagem todas as questoes
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
            
            $qst_number++;

            
            $this->objMakePdf->SetTextColor(51,51,51);
            
            
            //print numero da questao
            $this->objMakePdf->SetFont('Arial','B',10);
            
            //print numero da questao
            $borda1 = 0;
            $this->objMakePdf->Cell(7,7,$qst_number,$borda1,0,"C");//13,11
            $this->objMakePdf->SetX(20);//27
            //$this->SetFont('Arial','B',10);
            //$this->MultiCell(173,4,$qst_question,0,"J");
            $this->objMakePdf->SetFont('Arial','',11);
            
            //print enunciado da questao
            $borda2 = 0;
            $this->objMakePdf->MultiCell(173,5,$qst_question,$borda2,"J");
            
            //$this->objMakePdf->Ln(2);
            $this->objMakePdf->SetX(20);//27
            $this->objMakePdf->SetTextColor(123,123,123);
         //   $this->objMakePdf->SetFont('Arial','',9);
              //print comentario do enunciado da questao
         //   $this->objMakePdf->MultiCell(165,5,$qst_text,1,"J");//173
            //$this->Ln(2);
            //$this->objMakePdf->Ln(1);
            
            $alt_text = "";
            $alt_answ = "";
            $this->objMakePdf->SetTextColor(51,138,158);
            
            //tamanho da fonte 
            $this->objMakePdf->SetFont('Arial','',10);
            //$this->objMakePdf->Cell(15,6,'R',0,0,"C");
             
            //gambiarra desativada e refatorada para metodo aliasParaAlternativaRespostaDaQuestao().
            //$arrAlfa = array('1'=>'A','2'=>'B','3'=>'C','4'=>'D', '5' => 'E');
            $arrAlfa = $this->aliasParaAlternativaRespostaDaQuestao();
            
            
            if (isset($question['alternatives'])) {
                foreach ($question['alternatives'] AS $alt_designation => $alt_value) {
                    
                    //$alt_text = $arrAlfa[$alt_designation]." - ".$alt_value; 
                    $alt_text = $alt_value;
                    
                    $cY = $this->objMakePdf->GetY();
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->objMakePdf->SetTextColor(51,51,51);
                        //$this->SetFont('Arial','B',10);
                        $this->objMakePdf->SetFont('Arial','',10);
                    } else {
                        $this->objMakePdf->SetTextColor(123,123,123);
                        //$this->SetFont('Arial','',8);
                        $this->objMakePdf->SetFont('Arial','',9);
                    }
                    $this->objMakePdf->SetX(35);
                    //$this->MultiCell(165,5,utf8_decode($alt_text),0,"J");
                    
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->objMakePdf->MultiCell(165,6,utf8_decode($alt_text),0,"J");
                        $this->objMakePdf->SetX(30);
                        $iX = $this->objMakePdf->GetX();
                        $iY = $this->objMakePdf->GetY()-5;
                        $iY = ($cY > $iY) ? $iY : $cY;
                        $this->objMakePdf->Image($this->objMakePdf->public_path.'/img/check.jpg',$iX,$iY,5);
                    } 
                    
                    //$this->Ln(1);
                    $this->objMakePdf->Ln(1);
                } //fim foreach
            } else {
               $this->objMakePdf->SetX(30);
               $this->objMakePdf->Ln(3);       
            }
            
            
            $this->objMakePdf->ln(3);
            
            //quantidade de questoes por paginas
            if ($question_number == 10 || $question_number == 20 || $question_number == 30) {
                $this->objMakePdf->AddPage();
            }
            
//            
//            if ($question_number != $total_questions) {
//                $this->objMakePdf->AddPage();
//            }
        
        } //fim foreach em $arrDevolutive
    
        return $qst_number;
    }
    
    /**
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
