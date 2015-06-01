
<?php


/**
 * Classe responsavel pela Parte 3 da devolutiva MPE
 *
 * @author esilva
 */
class Vtx_Devolutive_Tipo_MPE_DevolutiveParte3MPE {

    protected $objMakePdf;
    
    protected $estilos;
    
    public function __construct ($objMakePdf)
    {
        $this->objMakePdf = $objMakePdf;
        
        $this->estilos = new Vtx_Devolutive_Tipo_MPE_Estilos($objMakePdf);
    }
    
    
    public function introducaoParte3()
    {
        ####################################
        //capa do bloco
        $this->objMakePdf->AddPage();

        $page_parte3 = 'RESPONSABILIDADE-CAPA.pdf';
        $pags = array($page_parte3);
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);         

        //contracapa do Bloco
         $this->objMakePdf->AddPage();

        $page_parte3 = 'Parte3_RespSocial.pdf'; 
        $pags = array($page_parte3);
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);   
        
        //$this->objMakePdf->MultiCell(190,8,utf8_decode("contra capa da Devolutiva "),0,"J",true);
    }
    
    public function DevolutiveParte3MPE($arrDevolutive, $arrBlocks, $arrCriteria, $offSet = 0, $isRA = false) 
    {
        //var_dump('arrDevolutive',$arrDevolutive);
        //exit;

        //printa capa e contracapa
        $this->introducaoParte3();
        
        $qst_number = $offSet;
        $blk_control = "";
        $crt_control = "";
  
        $this->objMakePdf->pagina = 68; //num inicial da questao deste bloco, para pegar num pdf de fundo
        
        #####################################
        
        //Legenda -> desabilitada em 21 fev 13
        //$this->Legenda();
        
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
//                $this->objMakePdf->SetTextColor(51,51,51);
//                $this->objMakePdf->SetFont('Myriad','BI',10);
                
                #######################################
                # CRITERIO
                #######################################
                
                //reescrever
                //adicionar numero pagina correto
                $npage = 0;

//                //insere fundo PDF design devolutiva
//                $this->objMakePdf->AddPage();
//                
//                switch($crt_criterion_id) {
//              
//                    default:
//                        $pags = array('null.pdf');
//                    break;   
//                }
//                                
//                $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);            
//                
//                //$this->objMakePdf->MultiCell(190,8,utf8_decode("  Critério ").$crt_criterion,0,"J",true);
//                
//                $this->objMakePdf->Ln(3);
                
                $crt_control = $crt_criterion;
            }
            
            
            
            #######
            # INSERE FUNDO PDF para QUESTAO/RESPOSTA
            #######
            $npage = $this->objMakePdf->pagina;
            
            $page = $npage."_parte3.pdf";
            /**
             * @TODO - tratar possivel erro
             * caso onde numero da questao é maior que numero do pdf de fundo que existe na pasta /capa
             * portanto nao ha pdf de fundo para inserir, entao insere o null.pdf
             * Pode ocorrer caso seja cadastrada uma questao de ultima hora e nao eh criado o pdf de fundo,
             */      
            $limite_pdf_pasta_capa = 76; //num da ultima questao deste bloco
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

                    $respostaEscrita = "";
                    /**
                     * printa check para a resposta selecionada
                     */
                    if (isset($alt_alternative_designation) && $alt_designation == $alt_alternative_designation) 
                    {
                        
                        /**
                         * 
                         * questoes customizadas para a Parte 3
                         * 
                         * questao 4 e questao 9 ou 8.1
                         * 
                         */
                        $arrayResp = array();
                        if ($question_number == 4 || $question_number == 9) {
                            switch ($question_number)
                            {
                                
                                case 4:
                                    if ($question['additional_info']['custom'] == 'matriz') 
                                    {
                                       $arrayResp = $question['additional_info'];
                                       //var_dump('questao 4: ',$question['additional_info']); //exit;
                                       $respostaEscrita = $arrayResp['answerArray'][8];
                                    
                                       //para printar Resposta Escrita
                                       $ans_write_answer = $respostaEscrita;
                                    
                                       $this->questao4($arrayResp);
                                    }
                                    break;
                                case 9:
                                    if ($question['additional_info']['custom'] == 'matriz') 
                                    {
                                       $arrayResp = $question['additional_info'];
                                       //var_dump('questao 9: ',$question['additional_info']); //exit;
                                       $this->questao9ou8ponto1($arrayResp);                                       
                                    }                                    

                                    break;                                
                                
                            }
           
                        } else { 
                        
                            switch ($alt_text_letra) 
                            {
                                case 'A':
                                    $this->objMakePdf->SetXY(25,103);
                                    break;
                                case 'B':
                                    $this->objMakePdf->SetXY(25,123);
                                    break;
                                case 'C':
                                    $this->objMakePdf->SetXY(25,145);
                                    break;
                                case 'D':
                                    $this->objMakePdf->SetXY(25,167);
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
            $this->objMakePdf->SetY(192);
            
            $this->objMakePdf->Ln(2);
            
           
            if (isset($ans_write_answer) && ($ans_write_answer != "")) 
            {                 
                ####################
                # resposta escrita
                $this->estilos->respostaEscrita("RESPOSTA ESCRITA",$ans_write_answer);
                ######## fim Resposta Escrita
            } 
            
            //$test = "question_number: ".$question_number." - total_questions: ".$total_questions;
            //$this->MultiCell(165,6,"RESPOSTA ESCRITA: ".$test,0,"J");
    
            if (isset($ans_annual_result) && (count($ans_annual_result) == 3)) 
            {
            
                ####### print Resultado Anual
                $this->estilos->resultadoAnual('RESULTADO ANUAL', $ans_annual_result, $ans_annual_result_unit);
                ####### fim print Resultado Anual

            }
            
            
            if ($alt_alternative_feedback != "" && $alt_alternative_feedback != '0') {
                
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
    
    
    
    public function questao4($arrQuestion4)
    {
        
       //serialize na tabela Answer campo InformationAditional  
        /*
        string 'arrQuestion4' (length=12)
        array (size=2)
          'custom' => string 'matriz' (length=6)
          'answerArray' => 
            array (size=8)
              1 => string 'a' (length=1)
              2 => string 'b' (length=1)
              3 => string 'c' (length=1)
              4 => string 'd' (length=1)
              5 => string 'c' (length=1)
              6 => string 'b' (length=1)
              7 => string 'a' (length=1)
              8 => string 'estou relatando aqui as acoes realizadas' (length=40)       
         */
        
   /* var_dump('arrQuestion4', $arrQuestion4); 
    echo "<br><br>";
        */
        
       if (count($arrQuestion4) ==0) {
           return;
       } 
        
        
       //valida se questao foi respondida
       if (array_key_exists('custom', $arrQuestion4)) {
            if (!in_array('matriz', $arrQuestion4)) { 
                //var_dump('arrQuestion4: ',$arrQuestion4); 
               return;
            } 
       } 
       
        
       $resposta4 = $arrQuestion4['answerArray']; 
              
       $X_coluna1 = 66;
       $X_coluna2 = 102;
       $X_coluna3 = 131;
       $X_coluna4 = 169;
       
       $Y_linha1 = 112;
       $Y_linha2 = 123;
       $Y_linha3 = 134;
       $Y_linha4 = 145;
       $Y_linha5 = 156;
       $Y_linha6 = 167;
       $Y_linha7 = 178;
       
       $tipoResp = 'check';
       $respSelecionada = '';
       
       //item 4.1
        ##########
        switch (isset($resposta4[1])? $resposta4[1] : '') {
            case 'a':
                //Linha 1 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha1, $respSelecionada, 'check', '', false);
                break;
            case 'b':
                //Linha 1 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha1, $respSelecionada, 'check', '', false);
                break;
            case 'c':
                //Linha 1 - Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha1, $respSelecionada, 'check', '', false);
                break;
            case 'd':
                //Linha 1 - Coluna 4
                $this->printaAlternativaParaRespostaQuestao($X_coluna4, $Y_linha1, $respSelecionada, 'check', '', false);
                break;
        }
        ###########
        //item 4.2
        ##########
        switch (isset($resposta4[2])? $resposta4[2] : '') {
            case 'a':
                //Linha 2 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha2, $respSelecionada, 'check', '', false);
                break;
            case 'b':
                //Linha 2 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha2, $respSelecionada, 'check', '', false);
                break;
            case 'c':
                //Linha 2 - Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha2, $respSelecionada, 'check', '', false);
                break;
            case 'd':
                //Linha 2 - Coluna 4
                $this->printaAlternativaParaRespostaQuestao($X_coluna4, $Y_linha2, $respSelecionada, 'check', '', false);
                break;
        }
        //item 4.3
#############
        switch (isset($resposta4[3])? $resposta4[3] : '') {

            case 'a':
                //Linha 3 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha3, $respSelecionada, 'check', '', false);
                break;
            case 'b':
                //Linha 3 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha3, $respSelecionada, 'check', '', false);
                break;
            case 'c':
                //Linha 3 - Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha3, $respSelecionada, 'check', '', false);
                break;
            case 'd':
                //Linha 3 - Coluna 4
                $this->printaAlternativaParaRespostaQuestao($X_coluna4, $Y_linha3, $respSelecionada, 'check', '', false);
                break;
            ###############
        }
        //item 4.4
        switch (isset($resposta4[4])? $resposta4[4] : '') {


            case 'a':
                //Linha 4 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha4, $respSelecionada, 'check', '', false);
                break;
            case 'b':
                //Linha 4 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha4, $respSelecionada, 'check', '', false);
                break;
            case 'c':
                //Linha 4 - Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha4, $respSelecionada, 'check', '', false);

                break;
            case 'd':
                //Linha 4 - Coluna 4
                $this->printaAlternativaParaRespostaQuestao($X_coluna4, $Y_linha4, $respSelecionada, 'check', '', false);
                
                break;
#################
        }
                //item 4.5
        switch (isset($resposta4[5])? $resposta4[5] : '') {
            case 'a':
                //Linha 5 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha5, $respSelecionada, 'check', '', false);
                break;
            case 'b':
                //Linha 5 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha5, $respSelecionada, 'check', '', false);
                break;
            case 'c':
                //Linha 5 - Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha5, $respSelecionada, 'check', '', false);
                break;
            case 'd':
                //Linha 5 - Coluna 4
                $this->printaAlternativaParaRespostaQuestao($X_coluna4, $Y_linha5, $respSelecionada, 'check', '', false);
                break;
        }
        ###################
                //item 4.6
        switch (isset($resposta4[6])? $resposta4[6] : '') {
            case 'a':
                //Linha 6 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha6, $respSelecionada, 'check', '', false);
                break;
            case 'b':
                //Linha 6 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha6, $respSelecionada, 'check', '', false);
                break;
            case 'c':
                //Linha 6 - Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha6, $respSelecionada, 'check', '', false);
                break;
            case 'd':
                //Linha 6 - Coluna 4
                $this->printaAlternativaParaRespostaQuestao($X_coluna4, $Y_linha6, $respSelecionada, 'check', '', false);
                break;
        }
        ####################
                //item 4.7
        switch (isset($resposta4[7])? $resposta4[7] : '') {

            case 'a':
                //Linha 7 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha7, $respSelecionada, 'check', '', false);
                break;
            case 'b':
                //Linha 7 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha7, $respSelecionada, 'check', '', false);
                break;
            case 'c':
                //Linha 7 - Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha7, $respSelecionada, 'check', '', false);
                break;
            case 'd':
                //Linha 7 - Coluna 4
                $this->printaAlternativaParaRespostaQuestao($X_coluna4, $Y_linha7, $respSelecionada, 'check', '', false);
                break;
            #####################
        } //fim switch 
    } //end function questao 4          

    
    /**
     * questao 8 eh normal: igual todas as outras
     * Equivale a questao 8.1 na geracao da devolutiva
     */
    public function questao9ou8ponto1($arrQuestion9) //questao 8.1
    {
       /**
        string 'arrQuestion9' (length=12)
        array (size=2)
          'custom' => string 'matriz' (length=6)
          'answerArray' => 
            array (size=10)
              1 => string 'Este é o nome da ação' (length=24)
              2 => string '' (length=0)
              3 => string '' (length=0)
              4 => string 'publico benefeciado' (length=19)
              5 => string '12' (length=2)
              6 => string '15' (length=2)
              7 => string 'S' (length=1)
              8 => string 'P' (length=1)
              9 => string 'N' (length=1)
              10 => string 'AR' (length=2)   
        **/
/*
    var_dump('arrQuestion9', $arrQuestion9); 
    echo "<br><br>";
    */
       if (count($arrQuestion9) ==0) {
           return;
       } 
        
       //valida se questao foi respondida
       if (array_key_exists('custom', $arrQuestion9)) {
            if (!in_array('matriz', $arrQuestion9)) { 
               return;
            } 
       } 
        
       $resposta9 = $arrQuestion9['answerArray']; 
    //ESTA QUESTAO NAO IMPRIMI COMENTARIOS
        
       //Nome da Acao
       $X_nome_acao = 40;//ok
       $Y_nome_acao = 94;
       $respSelecionada = isset($resposta9[1])? $resposta9[1] : '';
       $this->printaAlternativaParaRespostaQuestao($X_nome_acao, $Y_nome_acao, $respSelecionada, 'text', '', false);

       //Item
       $X_item = 27;
       $Y_item = 105;       
       $respSelecionada = isset($resposta9[2])? $resposta9[2] : '';
       $this->printaAlternativaParaRespostaQuestao($X_item, $Y_item, $respSelecionada, 'text', '', false);

       //Resposta
       $X_resposta = 100;
       $Y_resposta = 105;       
       $respSelecionada = isset($resposta9[3])? $resposta9[3] : '';
       $this->printaAlternativaParaRespostaQuestao($X_resposta, $Y_resposta, $respSelecionada, 'text', '', false);       
       
       //a. b. c.       
       $X_abc = 70;
       $Y_a = 115;
       $Y_b = 125;
       $Y_c = 135;
       
       //resp: a
       $respSelecionada = isset($resposta9[4])? $resposta9[4] : '';
       $this->printaAlternativaParaRespostaQuestao($X_abc, $Y_a, $respSelecionada, 'text', '', false);       
       //resp: b
       $respSelecionada = isset($resposta9[5])? $resposta9[5] : '';
       $this->printaAlternativaParaRespostaQuestao($X_abc, $Y_b, $respSelecionada, 'text', '', false);       
       //resp: c
       $respSelecionada = isset($resposta9[6])? $resposta9[6] : '';
       $this->printaAlternativaParaRespostaQuestao($X_abc, $Y_c, $respSelecionada, 'text', '', false);       

       // d, e, f, g
       
       //abcissa X
       $X_coluna1 = 90;
       $X_coluna2 = 123;
       $X_coluna3 = 159;
       //abcissa Y
       $Y_linha1 = 147;
       $Y_linha2 = 157;
       $Y_linha3 = 167;
       $Y_linha4 = 177;
       $respSelecionada = "";
       
       switch (isset($resposta9[7])? $resposta9[7] : '') { //opcoes S, P ou N
            case 'S':
                //Linha 1 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha1, $respSelecionada, 'check', '', false);
                break;
            case 'P':
                //Linha 1- Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha1, $respSelecionada, 'check', '', false);
                break;
            case 'N':
                //Linha 1- Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha1, $respSelecionada, 'check', '', false);
                break;
        }

        switch (isset($resposta9[8])? $resposta9[8] : '') { //opcoes S, E ou N
            case 'S':
                //Linha 2 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha2, $respSelecionada, 'check', '', false);
                break;
            case 'P'://equivale a Eventualmente
                //Linha 2- Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha2, $respSelecionada, 'check', '', false);
                break;
            case 'N':
                //Linha 2- Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha2, $respSelecionada, 'check', '', false);
                break;
        }

        switch (isset($resposta9[9])? $resposta9[9] : '') { //opcoes S, P ou N
            case 'S':
                //Linha 3 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha3, $respSelecionada, 'check', '', false);
                break;
            case 'P'://equivale a Eventualmente
                //Linha 3- Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha3, $respSelecionada, 'check', '', false);
                break;
            case 'N':
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha3, $respSelecionada, 'check', '', false);
                break;
        }

        switch (isset($resposta9[10])? $resposta9[10] : '') { //opcoes A, R ou AR
            case 'A':            
                //Linha 4 - Coluna 1
                $this->printaAlternativaParaRespostaQuestao($X_coluna1, $Y_linha4, $respSelecionada, 'check', '', false);
            break;
            case 'R':            
                //Linha 4 - Coluna 2
                $this->printaAlternativaParaRespostaQuestao($X_coluna2, $Y_linha4, $respSelecionada, 'check', '', false);
                break;
            case 'AR':
                //Linha 4- Coluna 3
                $this->printaAlternativaParaRespostaQuestao($X_coluna3, $Y_linha4, $respSelecionada, 'check', '', false);
            break;
        }
    } //end function
    
    
    
    /**
     * Faz print de imagem check e texto no PDF 
     * @param int $X
     * @param int $Y
     * @param string $tipoResp //'check', 'text'
     * @param string $respSelecionada
     * @param string $respTextDebug
     * @param boolean $debug
     */   
    public function printaAlternativaParaRespostaQuestao($X, $Y, $respSelecionada='', $tipoResp = 'check', $respTextDebug = '', $debug = false)
    {   
        $this->objMakePdf->SetXY($X,$Y);
        $iX = $this->objMakePdf->GetX();
        $iY = $this->objMakePdf->GetY();
        
        $largura_img_check = 5;
        
        $print_info_abcissas = $respSelecionada;
        
        if ($debug) 
        {
           $abcissas = "{ X: ".$iX. " - Y: ".$iY." }";
           $print_info_abcissas = $respSelecionada.$abcissas.$respTextDebug;           
        }
        
        switch($tipoResp) {
             case 'check':
                 $this->objMakePdf->Image($this->objMakePdf->public_path.Vtx_Devolutive_Tipo_MPE_MPE::IMG_CHECK,$iX,$iY,$largura_img_check);
                 if ($debug) {  
                     $this->objMakePdf->MultiCell(165,6,utf8_decode($print_info_abcissas),0,"J");         
                 }                 
                 break;
             case 'text':
                 $this->objMakePdf->MultiCell(165,6,utf8_decode($print_info_abcissas),0,"J"); //resposta a alternativa                 
                 break;
             
         }      
       
//                            $iX = $this->objMakePdf->GetX();
//                            $iY = $this->objMakePdf->GetY();
//
//                            $abcissas = "{ X: ".$iX. " - Y: ".$iY." }";
//                            $print_info_abcissas = $abcissas;
//                            //$print_info_abcissas = "";
//
//                            $this->objMakePdf->Image($this->objMakePdf->public_path.'/img/check.jpg',$iX,$iY,5);
//                            $this->objMakePdf->MultiCell(165,6,utf8_decode($print_info_abcissas),0,"J"); //resposta a alternativa       
                
    }
    
    
    
}
