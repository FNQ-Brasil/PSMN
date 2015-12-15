<?php

/**
 * Classe responsavel pelo cadastro da Empresa no PDF da devolutiva.
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_DadosEmpresaPdf
{
    
    protected $objMakePdf;

    protected $objDevolutive;
    
    
    public function __construct( Vtx_Devolutive_MakePdf $objMakePdf, Model_Devolutive $objDevolutive ) 
    {                    
        /** @var Vtx_Devolutive_MakePdf objMakePdf **/
        $this->objMakePdf = $objMakePdf;       
        /** @var Model_Devolutive $objDevolutive **/
        $this->objDevolutive = $objDevolutive;        
        
        $this->printEnterpriseData();
    }
    
    //public function printEnterpriseData($arrEnterprise,$arrContact = null,$arrIssues = null)
    public function printEnterpriseData()
    {
        $fonte = 'Arial';
        
        //nova pagina
        $this->objMakePdf->AddPage();
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont($fonte,'B',12);
        $texto1 = utf8_decode("{$this->objMakePdf->getTxtCadastroEmpresa()}");
        
        $this->objMakePdf->MultiCell(190,8,$texto1,0,"L");
        $this->objMakePdf->ln(2);
        
        $this->objMakePdf->SetFillColor(224,235,255);
        $this->objMakePdf->SetTextColor(0);
        $this->objMakePdf->SetDrawColor(51,51,51);
        $this->objMakePdf->SetLineWidth(.3);
        
        $wid1 = array('0' => '55', '1' => '135');
        $this->objMakePdf->Cell(array_sum($wid1),0,'','B');
        $this->objMakePdf->ln();
        
        $fill = false;
        foreach($this->objDevolutive->getArrEnterprise() as $key => $row)
        {
            $this->objMakePdf->SetFont($fonte,'B','9');
            $this->objMakePdf->Cell($wid1['0'],6,utf8_decode(" ".$key),'L',0,'L',$fill);
            $this->objMakePdf->SetFont($fonte,'','9');
            $this->objMakePdf->Cell($wid1['1'],6,utf8_decode(" ".$row),'R',0,'L',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;
        }
        $this->objMakePdf->Cell(array_sum($wid1),0,'','T');
        $this->objMakePdf->ln(6);
        
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont($fonte,'B',12);
        $texto1 = utf8_decode("{$this->objMakePdf->getTxtContatoEmpresa()}");
        
        $this->objMakePdf->MultiCell(190,8,$texto1,0,"L");
        $this->objMakePdf->ln(2);
        
        $this->objMakePdf->SetFillColor(224,235,255);
        $this->objMakePdf->SetTextColor(0);
        $this->objMakePdf->SetDrawColor(51,51,51);
        $this->objMakePdf->SetLineWidth(.3);
        
        $wid2 = array('0' => '55', '1' => '135');
        $this->objMakePdf->Cell(array_sum($wid2),0,'','B');
        $this->objMakePdf->ln();
        
        $fill = false;
        foreach($this->objDevolutive->getArrContact() as $key => $row)
        {
            $this->objMakePdf->SetFont($fonte,'B','9');
            $this->objMakePdf->Cell($wid2['0'],6,utf8_decode(" ".$key),'L',0,'L',$fill);
            $this->objMakePdf->SetFont($fonte,'','9');
            $this->objMakePdf->Cell($wid2['1'],6,utf8_decode(" ".$row),'R',0,'L',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;
        }
        $this->objMakePdf->Cell(array_sum($wid2),0,'','T');
        $this->objMakePdf->ln(6);
        
        $this->objMakePdf->SetTextColor(51,51,51);
        $this->objMakePdf->SetFont($fonte,'B',12);
        $texto1 = utf8_decode("{$this->objMakePdf->getTxtResCadastroEmpresa()}");
        
        $this->objMakePdf->MultiCell(190,8,$texto1,0,"L");
        $this->objMakePdf->ln(2);
        
        $this->objMakePdf->SetFillColor(224,235,255);
        $this->objMakePdf->SetTextColor(0);
        $this->objMakePdf->SetDrawColor(51,51,51);
        $this->objMakePdf->SetLineWidth(.3);
        $this->objMakePdf->SetFont($fonte,'','9');
        
        $wid3 = array('0' => '90', '1' => '100');
        $this->objMakePdf->Cell(array_sum($wid3),0,'','B');
        $this->objMakePdf->ln();
        
        $fill = false;
        foreach($this->objDevolutive->getArrIssues() as $row)
        {
            $this->objMakePdf->Cell($wid3['0'],6,utf8_decode(" ".$row['Q']),'L',0,'L',$fill);
            $this->objMakePdf->Cell($wid3['1'],6,utf8_decode($row['R']),'R',0,'L',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;
        }
        $this->objMakePdf->Cell(array_sum($wid3),0,'','T');
        
    }

    
} //end class

?>
