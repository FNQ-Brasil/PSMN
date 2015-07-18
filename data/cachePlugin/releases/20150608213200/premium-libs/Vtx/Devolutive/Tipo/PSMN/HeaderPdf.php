<?php

/**
 * Classe responsavel pelo Header PDF da devolutiva.
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_Tipo_PSMN_HeaderPdf
{
    
    /**
     * @var Vtx_Devolutive_MakePdf
     */
    protected $objMakePdf;
    
    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf) 
    {                
        $this->objMakePdf = $objMakePdf;       
        
        $this->defineHeader();
    }
    
     
    /**
     * insere header na devolutiva
     * 
     * @depends FPDF
     */
    public function defineHeader() 
    {
        
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetDrawColor(51,51,51);
        $this->objMakePdf->SetLineWidth(0.2);
        $this->objMakePdf->Image($this->objMakePdf->public_path.$this->objMakePdf->getLogotipoEmpresa(),10,4,50);
        //$this->objMakePdf->Image($this->objMakePdf->public_path.$this->objMakePdf->getLogotipoQualidade(),52,10,25);
        $this->objMakePdf->SetFont('Arial','B',11);
            
        //$tmpTxt = substr($this->headerTitle,0.110);
        $tmpTxt = substr("{$this->objMakePdf->getHeaderTitle()}",0.110);

        //$this->Cell(80,10,utf8_decode($tmpTxt),0,0,'R');
        $this->objMakePdf->setXY(80,10);
        $this->objMakePdf->MultiCell(120,5,utf8_decode($tmpTxt),0,'R');
        $this->objMakePdf->Ln(15);     
        
    }
    
 
    
} //end class

?>
