<?php

/**
 * Classe responsavel pela Capa PDF da devolutiva.
 * 
 *
 * @depends FPDF
 * @depends FPDI
 * 
 * @author esilva
 */
class Vtx_Devolutive_CapaPdf
{
    const DIR_CAPA = "/capa/";
    
    protected $objMakePdf;


    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf) 
    {                
        $this->objMakePdf = $objMakePdf;       
        
        $this->defineCapa();
    }
    
     
    /**
     * insere arquivo PDF como capa(pagina 1) da devolutiva
     * 
     * @depends FPDI
     */
    public function defineCapa() 
    {
        
        $dirSource = $this->objMakePdf->public_path.self::DIR_CAPA;
        
        //caso o tipo do questionario seja diagnostico
        $pagecount = $this->objMakePdf->setSourceFile($dirSource. $this->objMakePdf->getCapaDiagnostico()); 
        
        //Caso o tipo do questionario seja Autoavaliacao
        if ($this->objMakePdf->getTipoQuestionario() == 2) {
            $pagecount = $this->objMakePdf->setSourceFile($dirSource. $this->objMakePdf->getCapaAvaliacao()); 
        } 
        //lib FPDI
        $tplidx = $this->objMakePdf->importPage(1, '/MediaBox'); 
        
        //lib FPDF
        $this->objMakePdf->addPage(); 
        
        //lib FPDF
        $this->objMakePdf->useTemplate($tplidx, 0, 0, 210); 
        	 
        //$pdf->Output('newpdf.pdf', 'D');              

        
    }
        
} //end class