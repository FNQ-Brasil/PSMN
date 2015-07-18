<?php

/**
 * Classe responsavel pelo apresentacao do PDF da devolutiva.
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_PresentationPdf
{
    
    protected $objMakePdf;


    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf) 
    {                
        $this->objMakePdf = $objMakePdf;       
        
        $this->definePresentation();
    }
    
     
    /**
     * insere Presentation na devolutiva
     * 
     * @depends FPDF
     */
    public function definePresentation() 
    {
        $apres = $this->objMakePdf->getTexts()->presentation;
        $texto1 = utf8_decode($apres->texto1);
        $texto2 = utf8_decode($apres->texto2);
        $texto3 = utf8_decode($apres->texto3);
        $texto4 = utf8_decode($apres->texto4);
        $texto5 = utf8_decode($apres->texto5);
        $texto6 = utf8_decode($apres->texto6);
        
        $this->objMakePdf->AddPage();
        
        $fonte = 'Arial';
        
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont($fonte,'B',10);
        $this->objMakePdf->ln(4);
        
        $this->objMakePdf->MultiCell(190,5, $texto1,0,"J");
        $this->objMakePdf->ln(3);
        
        $this->objMakePdf->MultiCell(190,5,$texto2,0,"J");
        $this->objMakePdf->ln(5);
        
        $this->objMakePdf->SetFont($fonte,'',9);
        $this->objMakePdf->MultiCell(190,5, $texto3,0,"J");
        $this->objMakePdf->ln(3);
        
        $this->objMakePdf->MultiCell(190,5, $texto4,0,"J");
        $this->objMakePdf->ln(8);
        
        $this->objMakePdf->SetFont($fonte,'B',9);
        $this->objMakePdf->MultiCell(190,5, $texto5,0,"J");
        
        $this->objMakePdf->ln(3);
        $this->objMakePdf->MultiCell(190,5, $texto6,0,"J");        
        
    }
    
} //end class

?>
