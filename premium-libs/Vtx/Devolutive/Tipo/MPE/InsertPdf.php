<?php

/**
 * Classe responsavel por insercao de pdf externo na geracao devolutiva
 * 
 * @depends FPDF
 * @depends FPDI
 * 
 * @author esilva
 */
class Vtx_Devolutive_Tipo_MPE_InsertPdf
{
    //@refactoring para get/set em MPE class
    //const DIR_CAPA = "/capa/"; 
    
    /**
s     * @var Vtx_Devolutive_Tipo_MPE_MPE
     */
    protected $objMakePdf;
    
    protected $numeroPaginaInserir;
    
    protected $cacheYesOrNo;

    /**
     * @var Zend_Cache_Core
     */
    protected $cache;

    public function __construct(Array $pdfs, $objMakePdf, $addPage = false) 
    {                
        $this->objMakePdf = $objMakePdf;
        $this->cache = Zend_Registry::get('cache_FS');
        
        $programaTipo = Zend_Registry::get('programaTipo');
        $isSebraeMais = ($programaTipo == 'SebraeMais')?TRUE:FALSE;
        $isMpeDiagnostico = ($programaTipo == 'MpeDiagnostico')?TRUE:FALSE;
        
        //printa paginas
        foreach ($pdfs as $pdf) {
            if (($isSebraeMais or $isMpeDiagnostico) and $pdf == 'p3_Parte1_GestaoDaEmpresa.pdf') {
                continue;
            }
            $this->insercaoPdf($pdf, $addPage);
        }
        
    }
        
    /**
     * insere arquivo PDF na devolutiva
     * 
     * @depends FPDI
     */
    public function insercaoPdf($pagePdf, $addPage = false) 
    {
        $tipoInsert = 'arquivoPdf'; //default
                
        $dirSource = $this->objMakePdf->public_path.$this->objMakePdf->getDirCapa();
        
        $pathArquivoPdf = $dirSource. $pagePdf;
        
        $arrSubstituir = array('.','-');
        $pagePdfForCacheName = str_replace($arrSubstituir, '_', $pagePdf);
        
        //parser do pdf via FPDI ou recupera parser do cache
        //$this->cacheOrParserPdfWithFPDI($pindex.phpagePdfForCacheName, $pathArquivoPdf);
        
        //caso o tipo do questionario seja diagnostico
        $pagecount = $this->objMakePdf->setSourceFile($pathArquivoPdf, $this->cache);
        
        //lib FPDI
        switch ($tipoInsert) 
        {
            case 'arquivoPdf':
                $tplidx = $this->objMakePdf->importPage(1, '/MediaBox'); 
            break;
            case 'paginaPdf':
                $tplidx = $this->objMakePdf->importPage($this->getNumeroPaginaInserir(), '/MediaBox'); 
            break;        
        
        }
        //lib FPDF
        if ($addPage) {
            $this->objMakePdf->addPage(); 
        }
        //lib FPDF
        $this->objMakePdf->useTemplate($tplidx, 0, 0, 210); 
        	 
        //$pdf->Output('newpdf.pdf', 'D');              
  
    }
        
    /**
     * Recupera parser do Pdf: faz o parser em tempo real ou recupera do cache
     * 
     * @param string $arqInsert
     * @return fpdi_pdf_parser
     */
    public function cacheOrParserPdfWithFPDI($pagePdf, $pathArquivoPdf)
    {
        
        //try {
        //verifica parser do Pdf no cache
        $parserPdfCache = $this->cache->load($pagePdf);

        $origem = "--->parserPdf vem do cache---";         
        
        $this->objMakePdf->current_filename = $pathArquivoPdf;
        
        //recupera do cache
        if ($parserPdfCache == false) 
        {               
            //tento fazer o parser do pdf
            
            $parserPdfCache = $this->objMakePdf->_getPdfParser($pathArquivoPdf);   
            
            //salvo o parser do pdf no cache
            $this->cache->save($parserPdfCache, $pagePdf);
            
            $origem = "--->parserPdf NAO vem do cache---";
        } else {
      
            //grava parser pdf no var do objeto FPDI
            $this->objMakePdf->parsers[$pathArquivoPdf] = $parserPdfCache;
            
        }      
        echo $origem;
        
        $this->setCacheYesOrNo($origem);
        $result = true;
                
        //} catch (Exception $e) {
        //    throw new Exception("There is a problem at PDF parser process");
        //    $result = false;
       // }
        
        return $result;
        
    }
    
    public function getNumeroPaginaInserir() {
        return $this->numeroPaginaInserir;
    }

    public function setNumeroPaginaInserir($numeroPaginaInserir) {
        $this->numeroPaginaInserir = $numeroPaginaInserir;
    }
    
    public function getCacheYesOrNo() {
        return $this->cacheYesOrNo;
    }

    public function setCacheYesOrNo($cacheYesOrNo) {
        $this->cacheYesOrNo = $cacheYesOrNo;
    }    
    
    
} //end class