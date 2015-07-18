<?php

/**
 * Classe responsavel por insercao de pdf externo na geracao devolutiva
 * 
 *
 * @depends FPDF
 * @depends FPDI
 * 
 * @author esilva
 */
class Vtx_Devolutive_Tipo_PSMN_DevolutiveParte2Pdf
{
    const DIR_CAPA = "/capa/";
    
    protected $objMakePdf;


    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf) 
    {                
        $this->objMakePdf = $objMakePdf;       
        
        //printa paginas
        foreach ($this->objMakePdf->getArquivoPdfDevolutiveParte2() as $pdf) {
            $this->insercaoPdf($pdf);
        }
    }
    
     
    /**
     * insere arquivo PDF como capa(pagina 1) da devolutiva
     * 
     * @depends FPDI
     */
    public function insercaoPdf($pagePdf) 
    {
        
        $dirSource = $this->objMakePdf->public_path.self::DIR_CAPA;
        
        //caso o tipo do questionario seja diagnostico
        $pagecount = $this->objMakePdf->setSourceFile($dirSource. $pagePdf);
        
        //lib FPDI
        $tplidx = $this->objMakePdf->importPage(1, '/MediaBox'); 
        
        //lib FPDF
        $this->objMakePdf->addPage(); 
        
        //lib FPDF
        $this->objMakePdf->useTemplate($tplidx, 0, 0, 210); 
        	 
        //$pdf->Output('newpdf.pdf', 'D');              

        
    }
        
} //end class