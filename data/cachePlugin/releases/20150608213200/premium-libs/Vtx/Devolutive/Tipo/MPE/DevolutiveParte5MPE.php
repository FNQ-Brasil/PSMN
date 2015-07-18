<?php


/**
 * Description of DevolutiveParte5MPE - Conclusão do avaliador
 *
 * @author vtx
 */
class Vtx_Devolutive_Tipo_MPE_DevolutiveParte5MPE {

    protected $objMakePdf;
    
    protected $estilos;
    
    public function __construct ($objMakePdf)
    {
        $this->objMakePdf = $objMakePdf;
        
        $this->estilos = new Vtx_Devolutive_Tipo_MPE_Estilos($objMakePdf);
    }
    
    public function introducaoParte5()
    {
        ####################################
        //pagina 1 do conclusão
        $this->objMakePdf->AddPage();
        $intro_parte5 = 'fundo-mpe.pdf';
        $pags = array($intro_parte5);
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);                
        $this->objMakePdf->AddPage();
    }

    public function DevolutiveParte5MPE($enterpriseId,$programaIdAvaliador) 
    {
            $configDb = Zend_Registry::get('configDb');
        
            $blockIdGE = $configDb->qstn->currentBlockIdGestaoEmpresa;
            $blockIdPI = $configDb->qstn->currentBlockIdPraticasInovacao;
            $blockIdRS = $configDb->qstn->currentBlockIdResponsabilidadeSocial;
            
            $modelQuestionnaireConclusion = new Model_QuestionnaireConclusion();

            $questionnaireConclusionRowGestaoEmpresa = $modelQuestionnaireConclusion->getByEnterpriseIdProgramaId(
                $enterpriseId, $programaIdAvaliador, $blockIdGE
            );
            $conclusionGestaoEmpresa = ($questionnaireConclusionRowGestaoEmpresa)?
                $questionnaireConclusionRowGestaoEmpresa->getConclusion():'';

            $questionnaireConclusionRowPraticasInovacao = $modelQuestionnaireConclusion->getByEnterpriseIdProgramaId(
                $enterpriseId, $programaIdAvaliador, $blockIdPI
            );
            $conclusionPraticasInovacao = ($questionnaireConclusionRowPraticasInovacao)?
                $questionnaireConclusionRowPraticasInovacao->getConclusion():'';

            $questionnaireConclusionRowResponsabilidadeSocial = $modelQuestionnaireConclusion->getByEnterpriseIdProgramaId(
                $enterpriseId, $programaIdAvaliador, $blockIdRS
            );
            $conclusionResponsabilidadeSocial = ($questionnaireConclusionRowResponsabilidadeSocial)?
                $questionnaireConclusionRowResponsabilidadeSocial->getConclusion():'';
        
        //printa capa e contracapa
        //$this->introducaoParte5();
        
        $avaliacao_feedback = $conclusionGestaoEmpresa;
        
        $this->objMakePdf->pagina = $qst_number = 88; //num inicial da questao deste bloco, para pegar num pdf de fundo
        #####################################
        
            /*
            $this->objMakePdf->SetFillColor(215,232,237);
            $this->objMakePdf->SetFillColor(252,219,153);
            */
            #######
            # INSERE FUNDO PDF para QUESTAO/RESPOSTA
            #######
            $page = "fundo-mpe.pdf";
            /**
             * @TODO - tratar possivel erro
             * caso onde numero da questao é maior que numero do pdf de fundo que existe na pasta /capa
             * portanto nao ha pdf de fundo para inserir, entao insere o null.pdf
             * Pode ocorrer caso seja cadastrada uma questao de ultima hora e nao eh criado o pdf de fundo,
             */      
            /*
            $limite_pdf_pasta_capa = 87; //num. no pdf fundo, da ultima questao deste bloco
            if ($npage > $limite_pdf_pasta_capa) {
                $page = 'null.pdf';
            }
            */
            $this->objMakePdf->AddPage(); //nova pagina
            $pags = array($page);
            $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);  
                
            $this->objMakePdf->pagina++;
            
            if ($avaliacao_feedback != "" && $avaliacao_feedback != '0') 
            {
                #########escreve texto Comentario (Devolutiva)
                if ($conclusionGestaoEmpresa) {
                    $this->estilos->conclusao("Conclusão de gestão", utf8_decode($conclusionGestaoEmpresa));
                }
                if ($conclusionPraticasInovacao) {
                    $this->estilos->conclusao("Conclusão de inovação", utf8_decode($conclusionPraticasInovacao));
                }
                if ($conclusionResponsabilidadeSocial) {
                    $this->estilos->conclusao("Conclusão de responsabilidade social", utf8_decode($conclusionResponsabilidadeSocial));
                }
                #########fim texto comentario
            }
                        
            /*
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

            $cell_width_resposta = 51;
            $cell_height_resposta = 6;
            $cell_border_resposta = 0;
            $cell_align_texto_resposta = "R";
            $cell_texto_resposta = "Resposta";
            
            $this->objMakePdf->SetFont('Myriad','B',15);
            */
            
            //COMENTARIO
            
        
        return $qst_number;
    }
    
    
    
    
}


