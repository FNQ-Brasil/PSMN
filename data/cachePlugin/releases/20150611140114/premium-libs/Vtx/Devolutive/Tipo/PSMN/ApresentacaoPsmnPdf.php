<?php

/**
 * Classe responsavel pela Apresentacao PDF da devolutiva.
 * 
 *
 * @author esilva
 */
class Vtx_Devolutive_Tipo_PSMN_ApresentacaoPsmnPdf
{
   
    /**
     * @var Vtx_Devolutive_MakePdf
     */
    protected $objMakePdf;
    
    /**
     * @var Model_Devolutive
     */
    protected $objDevolutive;
    
   

    public function __construct(Vtx_Devolutive_MakePdf $objMakePdf, Model_Devolutive $objDevolutive) 
    {                
        /** @var Vtx_Devolutive_MakePdf objMakePdf **/
        $this->objMakePdf = $objMakePdf;       
        /** @var Model_Devolutive $objDevolutive **/
        $this->objDevolutive = $objDevolutive;        
       
        $this->defineApresentacao();
        
    }
    
     
    public function defineApresentacao()
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
        
  
//        var_dump('---------arrEnterprise------------',$this->objDevolutive->getArrEnterprise());
//        
//        echo "<br><BR>";
//        var_dump('-------arrContact-----------',$this->objDevolutive->getArrContact());
//        
//        exit;
        
        $arrEnterprise = $this->objDevolutive->getArrEnterprise();
        $arrContact = $this->objDevolutive->getArrContact();
        
        $cpfEmpreendedora = "CPF da Empreendedora: ". $arrContact['Cpf'];
        
        $prezadaEmpresaria = "Prezada ".$arrContact['Nome'];
        
        //mostra somente se nao for produtor rural
        if (!$this->identificaProdutorRural()) {
            $cnpjEmpresa = "CNPJ da Empresa: ". $arrEnterprise['CPF/CNPJ'];
            $this->objMakePdf->MultiCell(190,5, $cnpjEmpresa,0,"J");
            $this->objMakePdf->ln(3);
        }

        $this->objMakePdf->MultiCell(190,5, $cpfEmpreendedora,0,"J");
        
        
        $this->objMakePdf->ln(4);
        
        $this->objMakePdf->MultiCell(190,5, $texto1,0,"J");
        $this->objMakePdf->ln(3);
        
        //$this->objMakePdf->MultiCell(190,5,$texto2,0,"J");
        $this->objMakePdf->MultiCell(190,5,$prezadaEmpresaria,0,"J");
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
    
    /**
     * @author esilva
     * 
     * Checa se usuario é Produtor Rural
     * 
     * PRural nao possui cnpj
     * 
     * esta regra foi validada com o Marco em 3 maio 13
     * @return boolean
     */
    public function identificaProdutorRural()
    {
        $produtorRural = false;
        $arrEnterprise = $this->objDevolutive->getArrEnterprise();
        if ( is_null($arrEnterprise['CPF/CNPJ']) || ($arrEnterprise['CPF/CNPJ'] == '') ) {
            $produtorRural = true;
        }
        
        return $produtorRural;
    }
    
    
} //end class

?>
