<?php

/**
 * Classe responsavel por estilos de texto na devolutiva
 * 
 *
 * @depends FPDF
 * @depends FPDI
 * 
 * @author esilva
 */
class Vtx_Devolutive_Tipo_MPE_Estilos
{
    
    protected $objMakePdf;
   
    public function __construct($objMakePdf) 
    {                
        $this->objMakePdf = $objMakePdf;
        
    }
    
    private function colorTitulos()
    {
        $this->objMakePdf->SetTextColor(0,51,102);
    }

    public function comentario($titulo, $conteudo) 
    {
//        # coordenada Y
//        # para printar Resposta Escrita, Comentario e Resposta anual
//        $this->objMakePdf->SetY(192);

        $this->colorTitulos();

        $this->objMakePdf->SetFont('Myriad', 'B', 13.5);

        $this->objMakePdf->SetX(15);

        $cell_width_devolutiva = 46;
        $cell_height_devolutiva = 6;
        $cell_border_devolutiva = 0;
        $cell_align_texto_devolutiva = "L";
        $cell_texto_devolutiva = utf8_decode($titulo);

        //texto devolutive
        $this->objMakePdf->Cell($cell_width_devolutiva, $cell_height_devolutiva, $cell_texto_devolutiva, $cell_border_devolutiva, 0, $cell_align_texto_devolutiva
        );  # $this->Cell(15,6,'D',0,0,"C");
        //conteudo comentario

        $this->objMakePdf->Ln(7);

        $this->objMakePdf->SetX(15);
        $this->objMakePdf->SetTextColor(51, 51, 51);
        //$this->SetFont('Arial','BI',9);
        $this->objMakePdf->SetFont('Myriad', '', 10.5);
        //if (isset($alt_alternative_feedback)) { $alt_feed = $alt_alternative_feedback; }
        //$this->MultiCell(173,5,$alt_feed,0,"J");
        $this->objMakePdf->MultiCell(180, 5.5, $conteudo, 0, "J");
    }
    
    public function respostaEscrita($titulo, $conteudo)
    {
//        # coordenada Y
//        # para printar Resposta Escrita, Comentario e Resposta anual
//        $this->objMakePdf->SetY(192);

        $this->colorTitulos();

        $this->objMakePdf->SetFont('Myriad', 'B', 13.5);

        #coordenada X
        $this->objMakePdf->SetX(15);

        $cell_width_devolutiva = 46;
        $cell_height_devolutiva = 6;
        $cell_border_devolutiva = 0;
        $cell_align_texto_devolutiva = "L";
        $cell_texto_devolutiva = utf8_decode($titulo);

        //texto devolutive
        $this->objMakePdf->Cell($cell_width_devolutiva, $cell_height_devolutiva, $cell_texto_devolutiva, $cell_border_devolutiva, 0, $cell_align_texto_devolutiva
        );
        $this->objMakePdf->Ln(7);

        $this->objMakePdf->SetTextColor(51, 51, 51);
        $this->objMakePdf->SetX(15);
        $this->objMakePdf->SetFont('Myriad', 'I', 10.5);
        //$alt_answ = $ans_write_answer; 
        $this->objMakePdf->MultiCell(180, 5.5, $conteudo, 0, "J");
        $this->objMakePdf->ln(4);
    }
    
    public function resultadoAnual($titulo, $ano_result, $ano_unit)
    {
//        # coordenada Y
//        # para printar Resposta Escrita, Comentario e Resposta anual
//        $this->objMakePdf->SetY(192);

        $this->colorTitulos();

        $this->objMakePdf->SetFont('Myriad', 'B', 13.5);

        $this->objMakePdf->SetX(15);

        $cell_width_devolutiva = 46;
        $cell_height_devolutiva = 6;
        $cell_border_devolutiva = 0;
        $cell_align_texto_devolutiva = "L";
        $cell_texto_devolutiva = utf8_decode($titulo);

        //texto devolutive
        $this->objMakePdf->Cell($cell_width_devolutiva, $cell_height_devolutiva, $cell_texto_devolutiva, $cell_border_devolutiva, 0, $cell_align_texto_devolutiva
        );
        $this->objMakePdf->Ln(7);
        //-----------------

        $this->objMakePdf->SetX(15);

        foreach ($ano_result AS $aar_year => $aar_value) {

            $this->objMakePdf->SetTextColor(51, 51, 51);
            $this->objMakePdf->SetFont('Myriad', 'I', 10.5);
            $this->objMakePdf->Cell(20, 6, utf8_decode($aar_year) . ": ", 0, 0, "R");

            $aar_unit = "";
            if ($ano_unit != "") {
                $aar_unit = "(" . utf8_decode($ano_unit) . ")";
            } else {
                $aar_unit = utf8_decode($ano_unit);
            }

            //$this->Cell(35,5,($aar_value != "") ? utf8_decode($aar_value)." ".$aar_unit : "",0,0,"L");
            $this->objMakePdf->Cell(35, 6, ($aar_value != "") ? utf8_decode($aar_value) . " " . $aar_unit : "", 0, 0, "L");
        }

        $this->objMakePdf->Ln(8);
    }    
        
    public function conclusao($titulo, $conteudo) 
    {
//        # coordenada Y
//        # para printar Resposta Escrita, Comentario e Resposta anual
//        $this->objMakePdf->SetY(192);

        $this->colorTitulos();

        $this->objMakePdf->SetFont('Myriad', 'B', 13.5);

        $this->objMakePdf->SetX(30);
        $this->objMakePdf->SetLeftMargin(5);

        $cell_width_devolutiva = 46;
        $cell_height_devolutiva = 30;
        $cell_border_devolutiva = 0;
        $cell_align_texto_devolutiva = "L";
        $cell_texto_devolutiva = utf8_decode($titulo);

        //texto devolutive
        $this->objMakePdf->Cell($cell_width_devolutiva, $cell_height_devolutiva, $cell_texto_devolutiva, $cell_border_devolutiva, 0, $cell_align_texto_devolutiva
        );  # $this->Cell(15,6,'D',0,0,"C");
        //conteudo comentario

        $this->objMakePdf->Ln(20);

        $this->objMakePdf->SetX(30);
        $this->objMakePdf->SetLeftMargin(5);
        $this->objMakePdf->SetTextColor(51, 51, 51);
        //$this->SetFont('Arial','BI',9);
        $this->objMakePdf->SetFont('Myriad', '', 10.5);
        //if (isset($alt_alternative_feedback)) { $alt_feed = $alt_alternative_feedback; }
        //$this->MultiCell(173,5,$alt_feed,0,"J");
        $this->objMakePdf->MultiCell(170, 5.5, $conteudo, 0, "J");
    }
    
} //end class