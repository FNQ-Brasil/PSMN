<?php

/**
 * Classe responsavel pelo Pagina 1 do PDF da devolutiva.
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_FirstPagePdf
{
    
    protected $objMakePdf;


    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf) 
    {                
        $this->objMakePdf = $objMakePdf;       
        
        $this->defineFirstPage();
    }
    
     
    /**
     * insere FirstPage na devolutiva
     * 
     * @depends FPDF
     */
    public function defineFirstPage() 
    {
        
        $periodo =  Vtx_Devolutive_MakePdf::PERIODO;
        $fonte = 'Arial';
        
        $arr = $this->objMakePdf->getArrQuestionnaireDef();
        
        $qto_title = utf8_decode($arr['title']);
        $qto_description = utf8_decode($arr['description']);
        $qto_long_description = utf8_decode($arr['long_description']);
        $qto_operation_beginning = utf8_decode($arr['operation_beginning']);
        $qto_operation_ending = utf8_decode($arr['operation_ending']);
        $dta_tmp = explode("/",$qto_operation_beginning);
        $str_ciclo = $dta_tmp[2];
        
        $this->objMakePdf->AddPage();
        $this->objMakePdf->SetFillColor(214,232,237);
        $this->objMakePdf->Rect(10,10,190,277,'F');
        $this->objMakePdf->SetFillColor(255,255,255);
        $this->objMakePdf->Rect(15,15,180,25,'F');
        $this->objMakePdf->Image($this->objMakePdf->public_path.$this->objMakePdf->getLogotipoEmpresa(),26,18,50);
        $this->objMakePdf->Image($this->objMakePdf->public_path.$this->objMakePdf->getLogotipoQualidade(),140,22,32);
        $this->objMakePdf->SetFillColor(255,255,255);
        $this->objMakePdf->SetXY(10,50);
        $this->objMakePdf->SetFont($fonte,'BI',24);
        $this->objMakePdf->SetTextColor(31,118,138);
        
        $tc = $this->objMakePdf->getTituloCapa();
        $stc = $this->objMakePdf->getSubTituloCapa();
        
        if ($this->objMakePdf->getIsRA()) {
            $this->objMakePdf->MultiCell(190,13,utf8_decode("{$this->objMakePdf->getTituloCapa()}"),0,"C");
        } else {
            $this->objMakePdf->MultiCell(190,13,utf8_decode("{$this->objMakePdf->getTituloCapa()}"),0,"C");
        }        
        $this->objMakePdf->SetLineWidth(1.2);
        $this->objMakePdf->SetDrawColor(255,255,255);
        $this->objMakePdf->Line(15,65,195,65);
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont($fonte,'BI',14.3);
        $this->objMakePdf->SetXY(10,68);
        $this->objMakePdf->MultiCell(190,10,utf8_decode("{$this->objMakePdf->getSubTituloCapa()}"),0,"C");
        $this->objMakePdf->MultiCell(190,10,$qto_description,0,"C");
        $this->objMakePdf->MultiCell(190,10,"Ciclo ".$str_ciclo,0,"C");
        $this->objMakePdf->SetXY(145,253);
        $this->objMakePdf->SetLineWidth(0.5);
        $this->objMakePdf->SetFont($fonte,'B',8);
        $this->objMakePdf->MultiCell(45,7,utf8_decode("{$periodo}"),1,"C");
        $this->objMakePdf->SetFont($fonte,'B',16);
        $this->objMakePdf->SetTextColor(31,118,138);
        $this->objMakePdf->SetX(145);
        $this->objMakePdf->MultiCell(45,8,$qto_operation_beginning,1,"C");
        $this->objMakePdf->SetX(145);
        $this->objMakePdf->MultiCell(45,8,$qto_operation_ending,1,"C");
         
    }

    
} //end class

?>
