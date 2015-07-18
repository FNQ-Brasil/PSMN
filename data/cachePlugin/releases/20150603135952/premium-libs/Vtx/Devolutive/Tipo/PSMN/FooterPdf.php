<?php

/**
 * Classe responsavel pelo Footer (rodape) PDF da devolutiva.
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_Tipo_PSMN_FooterPdf
{

    /**
     * @var Vtx_Devolutive_MakePdf
     */
    protected $objMakePdf;
    
    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf) 
    {                
        $this->objMakePdf = $objMakePdf;       
        
        $this->defineFooter();
    }
    
     
    /**
     * insere footer na devolutiva
     * 
     * @depends FPDF
     */
    public function defineFooter() 
    {

 
            $this->objMakePdf->SetTextColor(51,51,51);
            $this->objMakePdf->SetDrawColor(51,51,51);
            $this->objMakePdf->SetLineWidth(0.2);
            $this->objMakePdf->line(10,279,200,279); #$this->objMakePdf->line(10,280,200,280);
            $this->objMakePdf->SetXY(10,-15);#-15);
            $this->objMakePdf->SetFont('Arial','BI',7);
            
            $mm_distancia_da_margem_esquerda_img1 = 102; 
            $mm_distancia_do_topo_img1 = 280;
            $mm_largura_img1 =100; 
            
            $imagemFooter = $this->objMakePdf->public_path.$this->objMakePdf->getImagemFooter();

            $this->objMakePdf->Image( $imagemFooter,
                          $mm_distancia_da_margem_esquerda_img1,
                          $mm_distancia_do_topo_img1,
                          $mm_largura_img1
                          );           

 
            $this->objMakePdf->SetFont('Arial','BI',8);
            $this->objMakePdf->SetXY(30,-17);
            $this->objMakePdf->Cell(20,10,utf8_decode("{$this->objMakePdf->getEmissao_data()}"),0,0,'C');
            $this->objMakePdf->SetXY(9,-17);
            $this->objMakePdf->Cell(8,10,$this->objMakePdf->PageNo().'/{nb}',0,0,'C');
        
    }
    
 
    
} //end class

?>
