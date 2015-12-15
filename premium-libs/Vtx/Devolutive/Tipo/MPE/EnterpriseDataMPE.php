<?php

/**
 * Classe responsavel pelo cadastro empresa
 * 
 * @author esilva
 */
class Vtx_Devolutive_Tipo_MPE_EnterpriseDataMPE
{
    /**
     * DEBUG
     */
    const MODO_PRINT_LINHA_COLUNA_DEBUG = false;
    
    protected $objMakePdf;
    

    public function __construct(Vtx_Devolutive_Tipo_MPE_MPE $objMakePdf) 
    {                
        
        $this->objMakePdf = $objMakePdf;
        
    }
    
    
    /**
     * 
     * @param type $arrEnterprise
     * @param type $arrContact
     * @param type $arrIssues
     */
    public function dadosEmpresa ($arrEnterprise,$arrContact = null,$arrIssues = null)
    {
        
        //DADOS DA EMPRESA
        $razao_social = $arrEnterprise['Razão Social'];
        $nome_fantasia = $arrEnterprise['Nome Fantasia'];
        $categoria = $arrEnterprise['Categoria'];
        $cnae = $arrEnterprise['Atividade Econômica(CNAE)'];
        $tipo_empresa = $arrEnterprise['Tipo Empresa'];
        $cpf_cnpj = $arrEnterprise['CPF/CNPJ'];
        $num_colaboradores = $arrEnterprise['Número de Colaboradores'];
        $data_abertura = $arrEnterprise['Data de Abertura'];
        $endereco = $arrEnterprise['Endereço'];
        $bairro = $arrEnterprise['Bairro'];
        $cidade_estado = $arrEnterprise['Cidade/Estado'];
        $cep = $arrEnterprise['CEP'];
        
        //DADOS DO CONTATO DA EMPRESA
        $nome = $arrContact['Nome'];
        $cargo = $arrContact['Cargo'];
        $tel_fixo = $arrContact['Telefone'];
        $tel_cel = $arrContact['Celular'];
        $email_contato = $arrContact['E-mail'];
        
        #######################################################################
        //insere fundo PDF design devolutiva
        $this->objMakePdf->AddPage();
                
        $page_fundo = "p2_dados_empresa.pdf";
        $pags = array($page_fundo);
        $insertPdf = new Vtx_Devolutive_Tipo_MPE_InsertPdf($pags, $this->objMakePdf);              
        ########################################################################
        
        $this->objMakePdf->SetTextColor(51,51,51);
        
        //$this->objMakePdf->SetFont('Arial','',9);
        
        $this->objMakePdf->SetFont('Myriad','',9);
        
        
//        $texto1 = utf8_decode("Dados da Cooperativa");      
//        $this->MultiCell(190,8,$texto1,0,"L");
//        $this->ln(2);
        
        $this->objMakePdf->SetXY(15,85);
        
        $this->objMakePdf->SetFillColor(224,235,255);
        $this->objMakePdf->SetTextColor(0);
        $this->objMakePdf->SetDrawColor(51,51,51);
        $this->objMakePdf->SetLineWidth(.3);
        
        ######### COLUNAS
        
        $fill = false;
        $border = 0;
        $height = 5;
        $abcissa_x = 15;        
        
        $texto_exemplo = ": um dois três quatro, cinco, seis, sete, oito, nove.";
        $acimaQtdCaracteres = 40;

       ########################################################
       # COLUNA 1
       ######################################################## 
        //Linha 1 - Coluna 1
        $conteudo_resposta = $razao_social;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 1, Coluna 1 ".$texto_exemplo;
        }
        $setY = 89;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 86;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border); 
        $this->objMakePdf->Ln();          
        
        //Linha 2 - Coluna 1
        $conteudo_resposta = $cnae;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 2, Coluna 1 ".$texto_exemplo;
        }
        $setY = 111;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 108;
        }                
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();
        
        //Linha 3 - Coluna 1
        $conteudo_resposta = $num_colaboradores;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 3, Coluna 1 ".$texto_exemplo;
        }
        $setY = 131;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 129;
        }                
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();                            
        
        //Linha 4 - Coluna 1
        $conteudo_resposta = $bairro;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 4, Coluna 1 ".$texto_exemplo;
        }        
        $setY = 154;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 151;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();                            
        
        //Linha 5 - Coluna 1
        $conteudo_resposta = $nome;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 5, Coluna 1 ".$texto_exemplo;
        }
        $setY = 217;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 214;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();        
        
        //Linha 6 - Coluna 1
        $conteudo_resposta = $tel_cel;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 6, Coluna 1 ".$texto_exemplo;
        }
        $setY = 238;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 235;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();          
        
       ## end coluna 1      
       ########################################################
       # COLUNA 2
       ######################################################## 

        $abcissa_x = 77;
        
        //Linha 1 - Coluna 2
        $conteudo_resposta = $nome_fantasia;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 1, Coluna 2 ".$texto_exemplo;
        }
        $setY = 89;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 86;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();          
        
        //Linha 2 - Coluna 2
        $conteudo_resposta = $tipo_empresa;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 2, Coluna 2 ".$texto_exemplo;
        }
        $setY = 111;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 108;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();
        
        //Linha 3 - Coluna 2
        $conteudo_resposta = $data_abertura;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 3, Coluna 2 ".$texto_exemplo;
        }
        $setY = 131;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 129;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();                            
        
        //Linha 4 - Coluna 2
        $conteudo_resposta = $cidade_estado;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 4, Coluna 2 ".$texto_exemplo;
        }
        $setY = 154;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 151;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();                            
        
        //Linha 5 - Coluna 2
        $conteudo_resposta = $cargo;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 5, Coluna 2 ".$texto_exemplo;
        }
        $setY = 217;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 214;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();        
        
        //Linha 6 - Coluna 2
        $conteudo_resposta = $email_contato;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 6, Coluna 2 ".$texto_exemplo;
        }
        $setY = 238;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 235;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();          

        ## end coluna 2
        
       ########################################################
       # COLUNA 3
       ######################################################## 

        $abcissa_x = 139;
        
        //Linha 1 - Coluna 3
        $conteudo_resposta = $categoria;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 1, Coluna 3 ".$texto_exemplo;
        }
        $setY = 89;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 86;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();          
        
        //Linha 2 - Coluna 3
        $conteudo_resposta = $cpf_cnpj;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 2, Coluna 3 ".$texto_exemplo;
        }
        $setY = 111;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 108;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();
        
        //Linha 3 - Coluna 3
        $conteudo_resposta = $endereco;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 3, Coluna 3 ".$texto_exemplo;
        }
        $setY = 131;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 129;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();                            
        
        //Linha 4 - Coluna 3
        $conteudo_resposta = $cep;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 4, Coluna 3 ".$texto_exemplo;
        }
        $setY = 153;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 150;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();                            
        
        //Linha 5 - Coluna 3
        $conteudo_resposta = $tel_fixo;
        if (self::MODO_PRINT_LINHA_COLUNA_DEBUG) {
           $conteudo_resposta = "Linha 5, Coluna 3 ".$texto_exemplo;
        }
        $setY = 217;
        // texto maior que 1 linha
        if ( strlen($conteudo_resposta) > $acimaQtdCaracteres ) {
            $setY = 214;
        }        
        $this->objMakePdf->SetXY($abcissa_x,$setY);
        $this->objMakePdf->MultiCell(58,$height,utf8_decode($conteudo_resposta),$border);
        $this->objMakePdf->Ln();        
        
        ## end coluna 3
        
    } //end function
    
    

        
    
    
} //end class