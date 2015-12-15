<?php


/**
 * Description of DevolutiveParte1MPE
 *
 * @author vtx
 */
class Vtx_Devolutive_Tipo_MPE_DevolutiveParte1MPE {

    protected $objMakePdf;
    
    protected $estilos;


    public function __construct ($objMakePdf)
    {
        $this->objMakePdf = $objMakePdf;
        
        $this->estilos = new Vtx_Devolutive_Tipo_MPE_Estilos($objMakePdf);
    }
    
    public function paginasIntroducao()
    {
        $res = false;
        
        //try { 
            $pags = array ( 
                            'GESTAO-CAPA.pdf', 
                            'p3_Parte1_GestaoDaEmpresa.pdf', 
                            'p4_Parte1_Intro.pdf',
                            'p5_Parte1_MEG1.pdf',
                            'p6_Parte1_MEG2.pdf',
                            'p7_Parte1_MEG3.pdf',
                            'p8_Parte1_MEG4.pdf'
                           );
            $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags,  $this->objMakePdf, true);  
            
            $res = true;
            
      //  } catch (Exception $e) {
      //      throw  new Exception("Erro na geracao do pdf da devolutiva");
      //  }
        
        return $res ;
        
    }
    
    
    public function paginasInterpretacaoRadar()
    {
        $res = false;
        $pags = array ( 
                     'interpretacao-padrao-do-grafico-radar.pdf', 
                      );
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags,  $this->objMakePdf, true);  
            
        $res = true;
        
        return $res ;
        
    }    
    
    /**
     * 
     * 
     * @param type $arrDevolutive
     * @param type $arrBlocks
     * @param type $arrCriteria
     * @param type $offSet
     * @return type
     */
    public function DevolutiveParte1MPE($arrDevolutive, $arrBlocks, $arrCriteria, $offSet = 0, $isRA = false) 
    {
        //printa capa e contracapa e introducao textual
        //$this->paginasIntroducao();
        $qst_number = $offSet;
        $blk_control = "";
        $crt_control = "";
        
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
                //$this->SetTextColor(51,51,51);
                //$this->SetFont('Arial','BI',10);
                //$this->MultiCell(190,8,utf8_decode("  Bloco ").$blk_block,0,"J",true);
                //$this->Ln(3);
                $blk_control = $blk_block;
            }
            
            $this->objMakePdf->SetFillColor(252,219,143);
            
            if (($crt_control != $crt_criterion) && ($blk_control == $blk_block) && ($crt_criterion != "")) {
                $this->objMakePdf->SetTextColor(51,51,51);
                $this->objMakePdf->SetFont('Myriad','BI',10);
                
                #######################################
                # CRITERIO
                #######################################
                
                //reescrever
                //adicionar numero pagina correto
                $npage = 0;

                //insere fundo PDF design devolutiva
                $this->objMakePdf->AddPage();
                
                switch($crt_criterion_id) {
                    case '1':
                        $pags = array('1_Lideranca.pdf');
                    break;
                    case '2':
                        $pags = array('7_Estrategias.pdf');
                    break;
                    case '3': //Clientes
                        $pags = array('11_Clientes.pdf');
                    break;
                    case '4':
                        $pags = array('16_Sociedade.pdf');
                    break;
                    case '5':
                        $pags = array('19_Informacoes.pdf');
                    break;
                    case '6':
                        $pags = array('23_Pessoas.pdf');
                    break;
                    case '7': //Processos
                        $pags = array('28_Processos.pdf');
                    break;
                    case '8':
                        $pags = array('32_Resultados.pdf');
                    break;                
                    default:
                        $pags = array('null.pdf');
                    break;   
                }
                
                
                $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);            
                
                //$this->objMakePdf->MultiCell(190,8,utf8_decode("  Critério ").$crt_criterion,0,"J",true);
                
                $this->objMakePdf->Ln(3);
                $crt_control = $crt_criterion;
            }
            
            
            
            #######
            # INSERE FUNDO PDF para QUESTAO/RESPOSTA
            #######
            $npage = $this->objMakePdf->pagina;
            
            $page = $npage.".pdf";
            /**
             * @TODO - tratar possivel erro
             * caso onde numero da questao é maior que numero do pdf de fundo que existe na pasta /capa
             * portanto nao ha pdf de fundo para inserir, entao insere o null.pdf
             * Pode ocorrer caso seja cadastrada uma questao de ultima hora e nao eh criado o pdf de fundo,
             */      
            $limite_pdf_pasta_capa = 37;
            if ($npage > $limite_pdf_pasta_capa) {
                $page = 'null.pdf';
            }
            $this->objMakePdf->AddPage(); //nova pagina
            $pags = array($page);
            $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);  
                
            $this->objMakePdf->pagina++;
                        

            $this->objMakePdf->SetFillColor(220,220,220);
            
            #####################
            # printa o numero da questao
            #####################
            
            $qst_number++;

            $this->objMakePdf->SetTextColor(51,51,51);
            $this->objMakePdf->SetFont('Myriad','B',30);
            //$this->objMakePdf->Cell(13,11,$qst_number,0,0,"C");
            $this->objMakePdf->SetX(27);

            $this->objMakePdf->SetFont('Myriad','B',11); 
            
            
            ###
            ### desativa print do enunciado questao
            //$this->MultiCell(173,5,$qst_question,0,"J");
            $this->objMakePdf->Ln(2);
            //$this->objMakePdf->SetX(27);
            $this->objMakePdf->SetTextColor(123,123,123);
            //$this->SetFont('Arial','',8);
            //$this->MultiCell(173,5,$qst_text,0,"J");
            
            
            $alt_text = "";
            $alt_answ = "";            
            
            
            ###
            ### desativa print do enunciado questao            
            $this->objMakePdf->SetFont('Myriad','',9);
            //$this->MultiCell(173,5,$qst_text,0,"J");
            
            
            #####################################################
            # configura texto entre enunciado da Questao e Resposta as alternativas.
            #
            $this->objMakePdf->Ln(2); #$this->Ln(5);
            $this->objMakePdf->SetTextColor(51,138,158);

            $cell_width_resposta = 41;
            $cell_height_resposta = 6;
            $cell_border_resposta = 0;
            $cell_align_texto_resposta = "R";
            $cell_texto_resposta = "Resposta";
            
            $this->objMakePdf->SetFont('Myriad','B',14);
            
            ###
            ### desativa print do texto entre Questao e Resposta as alternativas.    
            
//            $this->Cell( $cell_width_resposta,
//                         $cell_height_resposta,
//                         $cell_texto_resposta,
//                         $cell_border_resposta,
//                         0,
//                         $cell_align_texto_resposta
//                        );     #$this->Cell(15,6,'R',0,0,"C");  
//            $this->Ln(8);  #nao tinha essa linha
            #####################################################
            
            $arrAlfa = array('1'=>'A','2'=>'B','3'=>'C','4'=>'D');
            
            if (isset($question['alternatives'])) {
                foreach ($question['alternatives'] AS $alt_designation => $alt_value) {
                    
                    $alt_text = $arrAlfa[$alt_designation]." - ".$alt_value; 
                    
                    $alt_text_letra = $arrAlfa[$alt_designation]; 
                    $cY = $this->objMakePdf->GetY();
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) {
                        $this->objMakePdf->SetTextColor(51,51,51);
                        //$this->SetFont('Arial','B',10);
                        $this->objMakePdf->SetFont('Myriad','B',11);
                    } else {
                        $this->objMakePdf->SetTextColor(123,123,123);
                        //$this->SetFont('Arial','',8);
                        $this->objMakePdf->SetFont('Myriad','',9);
                    }

                    
                    ###
                    ### COMENTANDO A RESPOSTA DA ALTERNAtiva
                    //$this->MultiCell(165,6,utf8_decode($alt_text),0,"J");

                    /**
                     * printa check para a resposta selecionada
                     */
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) 
                    {
                        
                        switch ($alt_text_letra) {
                            case 'A':
                                $this->objMakePdf->SetXY(25,89);
                                break;
                            case 'B':
                                $this->objMakePdf->SetXY(25,110);
                                break;
                            case 'C':
                                $this->objMakePdf->SetXY(25,131);
                                break;
                            case 'D':
                                $this->objMakePdf->SetXY(25,154);
                                break;
     
                        }
                        $iX = $this->objMakePdf->GetX();
                        $iY = $this->objMakePdf->GetY();
                        
                        $abcissas = "{ X: ".$iX. " - Y: ".$iY." }";
                        $print_info_abcissas = $alt_text_letra.$abcissas;
                        $print_info_abcissas = "";
                        
                        $this->objMakePdf->Image($this->objMakePdf->public_path.Vtx_Devolutive_Tipo_MPE_MPE::IMG_CHECK,$iX,$iY,5);
                        $this->objMakePdf->MultiCell(165,6,utf8_decode($print_info_abcissas),0,"J"); //resposta a alternativa
                        
                        
                    } 
                    
                    //$this->Ln(1);
                    $this->objMakePdf->Ln(2);
                }
            } else {
               $this->objMakePdf->SetX(30);
               $this->objMakePdf->Ln(6);       
            }
            
            # coordenada Y
            # para printar Resposta Escrita, Comentario e Resposta anual
            $this->objMakePdf->SetY(178);
            
            $this->objMakePdf->Ln(2);
            
            if (isset($ans_write_answer) && ($ans_write_answer != "")) { 
                

                ####################
                # resposta escrita
                $this->estilos->respostaEscrita("RESPOSTA ESCRITA",$ans_write_answer);
                ######## fim Resposta Escrita
            } 
            
            //$test = "question_number: ".$question_number." - total_questions: ".$total_questions;
            //$this->MultiCell(165,6,"RESPOSTA ESCRITA: ".$test,0,"J");
            
            
            if (isset($ans_annual_result) && (count($ans_annual_result) == 3)) {
                
                ####### print Resultado Anual
                $this->estilos->resultadoAnual('RESULTADO ANUAL', $ans_annual_result, $ans_annual_result_unit);
                ####### fim print Resultado Anual
                
            }
            
            
            if ($alt_alternative_feedback != "" && $alt_alternative_feedback != '0') 
            {
                #########escreve texto Comentario (Devolutiva)
                $this->estilos->comentario("COMENTÁRIO", $alt_alternative_feedback);
                #########fim texto comentario
            }
            
            //campo Pontos Fortes
            if ($alt_answer_feedback != "" && $alt_answer_feedback != '0' && $isRA) {
                $this->objMakePdf->Ln(3);
                $ans_feed = "";
                $this->objMakePdf->SetTextColor(226,127,61);
                $this->objMakePdf->SetFont('Myriad','B',14);
             
                $cell_width_PF = 51;
                $cell_height_PF = 6;
                $cell_border_PF = 0;
                $cell_align_texto_PF = "R";
                $cell_texto_PF = "Pontos Fortes";                      
                
                $this->objMakePdf->Cell($cell_width_PF,$cell_height_PF,$cell_texto_PF,$cell_border_PF,0,$cell_align_texto_PF);
                
                $this->objMakePdf->Ln(8);//pula linha
                
                $this->objMakePdf->SetX(27);
                $this->objMakePdf->SetTextColor(51,51,51);
                //$this->SetFont('Arial','BI',9);
                $this->objMakePdf->SetFont('Myriad','BI',10);
                if (isset($alt_answer_feedback)) { $ans_feed = $alt_answer_feedback; }
                //$this->MultiCell(173,5,$ans_feed,0,"J");
                $this->objMakePdf->MultiCell(173,6,$ans_feed,0,"J");
            }
            
            
            //campo Oportunidades de melhoria
            if ($alt_answer_feedback_improve != "" && $alt_answer_feedback_improve != '0' && $isRA) {
                $this->objMakePdf->Ln(8);
                $ans_feed = "";
                $this->objMakePdf->SetTextColor(226,127,61);
                
                
                ######## Texto Oportunidades de melhoria
                $this->objMakePdf->SetFont('Myriad','B',14);
               
                $cell_width_OM = 83;
                $cell_height_OM = 6;
                $cell_border_OM = 0;
                $cell_align_texto_OM = "R";
                $cell_texto_OM = "Oportunidades de Melhoria";                                           
                
                $this->objMakePdf->Cell($cell_width_OM,$cell_height_OM,$cell_texto_OM,$cell_border_OM,0,$cell_align_texto_OM);
                
                $this->objMakePdf->Ln(8); //pula linhas
                
                $this->objMakePdf->SetX(27);
                $this->objMakePdf->SetTextColor(51,51,51);
                
               
                $this->objMakePdf->SetFont('Myriad','BI',10);
                if (isset($alt_answer_feedback_improve)) { $ans_feed = $alt_answer_feedback_improve; }
                
                //grava campo no PDF
                $this->objMakePdf->MultiCell(173,6,$ans_feed,0,"J");
            }
            
            
            //this->objMakePdf->Ln(5);
            
//            if ($question_number != $total_questions) {
//                
//                $npage = $this->pagina;
//                if ($npage > 30) {
//                    $npage = 30;
//                }
//                $this->AddPage(); //nova pagina
//                $pags = array('pag_'.$npage.'.pdf');
//                $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this);  
//                
//                $this->pagina++;
//            }
        
        }
    
        return $qst_number;
    }
    
    
    
    
}
