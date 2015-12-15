<?php


/**
 * Description of DevolutiveParte2MPE
 *
 * @author esilva
 */
class Vtx_Devolutive_Tipo_MPE_DevolutiveParte2MPE {

    protected $objMakePdf;
    
    public function __construct ($objMakePdf)
    {
        $this->objMakePdf = $objMakePdf;
        
        $this->introducaoParte2();
    }
    

    public function introducaoParte2()
    {
        ##pagina 1 da Parte 2

        //adicionar numero pagina correto
        $npage = 0;
        
        $pags = array ( 
                      'CARACTERISTICAS-CAPA.pdf', 
                      'parte2_pg1.pdf', 
                      'parte2_pg2.pdf',
                       );
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags,  $this->objMakePdf, true);  
                
        ######### fim pagina 1 da Parte 2               
    }
    
    
    public function DevolutiveParte2MPE($arrDevolutive, $arrBlocks, $arrCriteria, $offSet = 0) 
    {
        
        $qst_number = $offSet;
        $blk_control = "";
        $crt_control = "";
        
        
        #####################################
        # configura abcissa Y para respostas das questoes que sera CHECADAS no pdf gerado
        #####################################
        $base_y_aumenta = 41;
        //sera incrementado no loop de questoes
        $y_aumenta = $base_y_aumenta;
        
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
            
            $this->objMakePdf->SetFillColor(214,232,237);
            
            if (($blk_control != $blk_block) && ($blk_block != "")) {
            
                ###
                ### NAO PRECISA PRINTAR BLOCO
                #
                //$this->objMakePdf->SetTextColor(51,51,51);
                //$this->objMakePdf->SetFont('Arial','BI',10);
                //$this->objMakePdf->MultiCell(190,8,utf8_decode("  Bloco ").$blk_block,0,"J",true);
                //$this->objMakePdf->Ln(3);
                $blk_control = $blk_block;
            }
            
            $this->objMakePdf->SetFillColor(252,219,143);
            
            #######
            # INSERE FUNDO PDF para QUESTAO/RESPOSTA
            #######
            if( $question_number == 1 || $question_number == 16 )
            {
                
                $npage = $this->objMakePdf->pagina;
                if ($npage > 30) {
                    $npage = 1;
                }
                $this->objMakePdf->AddPage(); //nova pagina
                
                switch ($question_number) {
                    case '1':
                        //parte2_question1a15
                        
                        //altura em relacao ao topo da pagina pdf
                        $y_aumenta = $base_y_aumenta;
                        $pags = array('parte2_question1a15.pdf');
                        $ondeEstou = "parte2_question1a15";
                        break;
                    case '16':
                        //parte2_question16a30
                        
                        //altura em relacao ao topo da pagina pdf
                        $y_aumenta = $base_y_aumenta-2;
                        $pags = array('parte2_question16a30.pdf');
                        $ondeEstou = "parte2_question16a30";
                        break;
                }

                $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);  

                $this->objMakePdf->pagina++;
            }
            ###############################################
                        

            $this->objMakePdf->SetFillColor(220,220,220);
            
            #####################
            # printa o numero da questao
            #####################
            
            $qst_number++;

            $this->objMakePdf->SetTextColor(51,51,51);
            $this->objMakePdf->SetFont('Myriad','B',30);
            
            ####################################################
            ### print numero da questao
            ####################################################
            //$this->objMakePdf->Cell(13,11,$qst_number,0,0,"C");
            
            $this->objMakePdf->SetX(27);

            $this->objMakePdf->SetFont('Myriad','B',11); 
            
            
            ###
            ### desativa print do enunciado questao
            //$this->objMakePdf->MultiCell(173,5,$qst_question,0,"J");
            $this->objMakePdf->Ln(2);
            $this->objMakePdf->SetX(27);
            $this->objMakePdf->SetTextColor(123,123,123);
            //$this->objMakePdf->SetFont('Arial','',8);
            //$this->objMakePdf->MultiCell(173,5,$qst_text,0,"J");
            
            
            $alt_text = "";
            $alt_answ = "";            
            
            
            ###
            ### desativa print do enunciado questao            
            $this->objMakePdf->SetFont('Myriad','',9);
            //$this->objMakePdf->MultiCell(173,5,$qst_text,0,"J");
            
            
            $arrAlfa = array('1'=>'A','2'=>'B','3'=>'C','4'=>'D');
            
            if (isset($question['alternatives'])) {
                foreach ($question['alternatives'] AS $alt_designation => $alt_value) {
                    
                    $alt_text = $arrAlfa[$alt_designation]." - ".$alt_value; 
                    
                    $alt_text_letra = $arrAlfa[$alt_designation]; 
                    $cY = $this->objMakePdf->GetY();
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->objMakePdf->SetTextColor(51,51,51);
                        //$this->objMakePdf->SetFont('Arial','B',10);
                        $this->objMakePdf->SetFont('Myriad','B',11);
                    } else {
                        $this->objMakePdf->SetTextColor(123,123,123);
                        //$this->objMakePdf->SetFont('Arial','',8);
                        $this->objMakePdf->SetFont('Myriad','',9);
                    }

                    
                    ###
                    ### COMENTANDO A RESPOSTA DA ALTERNAtiva
                    //$this->objMakePdf->MultiCell(165,6,utf8_decode($alt_text),0,"J");

                    /**
                     * printa check para a resposta selecionada
                     */
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {

                        $y_aumenta = $y_aumenta + 15 ;
                        $this->objMakePdf->SetY($y_aumenta);
                        
                        switch ($ondeEstou) {
                            case 'parte2_question1a15':
                                $abcissaX_respostaA = 156;
                                $abcissaX_respostaB = 167;
                                $abcissaX_respostaC = 178;    
                            break;
                            
                            case 'parte2_question16a30':
                                $abcissaX_respostaA = 155;
                                $abcissaX_respostaB = 165;
                                $abcissaX_respostaC = 176;                                    
                            break;
                        }
                        
                        
                        switch ($alt_text_letra) {
                            case 'A':                                
                                $this->objMakePdf->SetX($abcissaX_respostaA);
                                break;
                            case 'B':
                                $this->objMakePdf->SetX($abcissaX_respostaB);
                                break;
                            case 'C':
                                $this->objMakePdf->SetX($abcissaX_respostaC);
                                break;
//                            case 'D':
//                                $this->objMakePdf->SetX(162);
//                                break;     
                        }
                        
                        $iX = $this->objMakePdf->GetX();
                        $iY = $this->objMakePdf->GetY();
                        
                        $num_qst_number = $qst_number." - ";
                        $abcissas = "{ X: ".$iX. " - Y: ".$iY." }";
                       
                        $numero_questao = $num_qst_number.$alt_text_letra.$abcissas;
                        $numero_questao = "";
                        
                        $this->objMakePdf->Image($this->objMakePdf->public_path.Vtx_Devolutive_Tipo_MPE_MPE::IMG_CHECK,$iX,$iY,4);
                        $this->objMakePdf->MultiCell(165,6,utf8_decode($numero_questao),0,"J"); //resposta a alternativa
                      
                        
                    } 
                    
                    //$this->objMakePdf->Ln(1);
                    $this->objMakePdf->Ln(2);
                }
            } else {
               $this->objMakePdf->SetX(30);
               $this->objMakePdf->Ln(6);       
            }
            
            # coordenada Y
            # para printar Resposta Escrita, Comentario e Resposta anual
            $this->objMakePdf->SetY(120);
            

            $this->objMakePdf->ln(3);
            
        
        }
    
        return $qst_number;
    }
    
    /**
     * adiciona pdfs de textos referente as analise de combinacoes
     * para a tabela de pontuacao das Caracteristicas empreendedoras
     */
    public function analiseCombinacoes()
    {
        $pags = array ( 
                      'parte2_analise_pg1.pdf', 
                      'parte2_analise_pg2.pdf',
                      'parte2_analise_pg3.pdf',
                      'parte2_analise_pg4.pdf',
                      'parte2_analise_pg5.pdf',
                      'parte2_analise_pg6.pdf',
                      'parte2_analise_pg7.pdf',
                      'parte2_analise_pg8.pdf',
                       );
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags,  $this->objMakePdf, true);  
                
        ######### fim pagina 1 da Parte 2               
    }    
    
    
    
}
