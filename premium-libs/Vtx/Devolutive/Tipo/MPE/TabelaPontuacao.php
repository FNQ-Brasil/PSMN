<?php

/**
 * tabela para pontuacao
 * 
 *
 * @depends FPDF
 * @depends FPDI
 * 
 * @author esilva
 */
class Vtx_Devolutive_Tipo_MPE_TabelaPontuacao
{
    
    protected $objMakePdf;
    protected $objPontuacao;
    
    protected $arrPontuacaoObtida = array();
    protected $arrPorcentagemObtida = array();
    protected $qtdLinhasTabelaPontuacao;

    
    public function __construct($objMakePdf, $objPontuacao) 
    {                
        $this->objMakePdf = $objMakePdf;
        $this->objPontuacao = $objPontuacao;
        
        $this->renderizaTabela();
        
    }

    
    public function renderizaTabela()
    {       
        $fonte = 'Myriad';
        
        //$pdf = new PDF();
        // Column headings
        $header = array(utf8_decode('Critério'), 
                        utf8_decode('Pontuação Máxima'), 
                        utf8_decode('Pontuação Obtida'),
                        utf8_decode('% Obtida')
                       );
        // Data loading
        //$data = $this->objMakePdf->LoadData('countries.txt');
        $data = $this->objPontuacao;
        
        $this->objMakePdf->Ln(10);
        
        $this->objMakePdf->SetFont($fonte,'',12);

        //$this->objMakePdf->AddPage();
        $this->FancyTable($header,$data);
        //$pdf->Output();           
    }    
    
    
    
   /**
     * 
     * 
     * // Colored table
     * 
     * Codigo from:
     * http://www.fpdf.org/en/tutorial/tuto5.htm
     * @depends FPDF
     * @param type $header
     * @param type $data
     */
    public function FancyTable($header, $data)
    {
        $fonte = 'Myriad';
      
        $setX = 20;
        
        $this->objMakePdf->SetX($setX);
        
        // Colors, line width and bold font
        $this->objMakePdf->SetFillColor(255,255,255);    #SetFillColor(255,0,0);
        $this->objMakePdf->SetTextColor(0);   #SetTextColor(255); branco #SetTextColor(0); preto
        $this->objMakePdf->SetDrawColor(204,204,204);//SetDrawColor(128,0,0);
        $this->objMakePdf->SetLineWidth(0.8);
        $this->objMakePdf->SetFont($fonte,'','10');
        // Header
        //$w = array(80, 35, 40, 45);
        $w = array(70, 40, 40, 30);
        for($i=0;$i<count($header);$i++)
            $this->objMakePdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
        
        $this->objMakePdf->Ln();
        // Color and font restoration
        $this->objMakePdf->SetFillColor(247,247,247); //SetFillColor(224,235,255);
        $this->objMakePdf->SetTextColor(0);
        $this->objMakePdf->SetLineWidth(0.5);
        $this->objMakePdf->SetFont($fonte,'','10');
        
        // Data
        $fill = false;
        $total = 0;
        $linhaTabelaPontuacao = 0;
        
            //linha Lideranca
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Lideranca->nome);
            $pontuacaoMaxima = $this->objPontuacao->Lideranca->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Lideranca->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Lideranca->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;
            
            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;

            //linha Estrategias
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Estrategias->nome);
            $pontuacaoMaxima = $this->objPontuacao->Estrategias->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Estrategias->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Estrategias->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;
            
            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;         
                        
            //linha Clientes
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Clientes->nome);
            $pontuacaoMaxima = $this->objPontuacao->Clientes->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Clientes->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Clientes->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;

            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;              
            
            //linha Sociedade
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Sociedade->nome);
            $pontuacaoMaxima = $this->objPontuacao->Sociedade->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Sociedade->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Sociedade->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;

            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;               
 
            
            //linha Informacoes
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Informacoes->nome);
            $pontuacaoMaxima = $this->objPontuacao->Informacoes->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Informacoes->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Informacoes->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;

            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;                         
            
            //linha Pessoas
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Pessoas->nome);
            $pontuacaoMaxima = $this->objPontuacao->Pessoas->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Pessoas->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Pessoas->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;
            
            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;               

            //linha Processos
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Processos->nome);
            $pontuacaoMaxima = $this->objPontuacao->Processos->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Processos->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Processos->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;
            
            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;               
             
            //linha Resultados
            $this->objMakePdf->SetX($setX);
            $criterioDescription = utf8_decode($this->objPontuacao->Resultados->nome);
            $pontuacaoMaxima = $this->objPontuacao->Resultados->pontuacaoMaxima;
            $pontuacaoObtida = $this->objPontuacao->Resultados->pontuacaoObtida;
            $porcentagemObtida = $this->objPontuacao->Resultados->porcentagem;
            $this->setArrPontuacaoObtida($pontuacaoObtida);
            $this->setArrPorcentagemObtida($porcentagemObtida);
            $linhaTabelaPontuacao = $linhaTabelaPontuacao + 1;

            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;                  
            

            //seta qtd linhas de criterio
            $this->setQtdLinhasTabelaPontuacao($linhaTabelaPontuacao);
            
            //linha Total
            $this->objMakePdf->SetX($setX);
            $this->objMakePdf->SetFont($fonte,'B','10');
            $criterioDescription = utf8_decode('TOTAL');
            $pontuacaoMaxima = $this->totalPontuacaoMaxima();
            $pontuacaoObtida = $this->calcularTotalPontuacaoObtida();
            $porcentagemObtida = $this->calcularMediaPorcentagemObtida();

            $this->objMakePdf->Cell($w[0],6,$criterioDescription,'LR',0,'L',$fill);
            $this->objMakePdf->Cell($w[1],6,$pontuacaoMaxima,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[2],6,$pontuacaoObtida,'LR',0,'C',$fill);
            $this->objMakePdf->Cell($w[3],6,$porcentagemObtida."%",'LR',0,'R',$fill);
            $this->objMakePdf->Ln();
            $fill = !$fill;                  
            
            //fim tabela Pontuacao
            $this->objMakePdf->SetX($setX);
            // Closing line
            $this->objMakePdf->Cell(array_sum($w),0,'','T');
            $this->objMakePdf->Ln(1);
    }    

    /**
     * 
     * soma das pontuacao obtidas
     */
    private function calcularTotalPontuacaoObtida()
    {
        $somaTotal = array_sum($this->getArrPontuacaoObtida());
        
        return $somaTotal;
    }
    
    /**
     * refatorar -> somar cada uma das pontuacoes maxima
     * 
     * @return string
     */
    private function totalPontuacaoMaxima()
    {
        return "100,00";
    }
    
    /**
     * 
     * media da porcentagem obtida
     */
    private function calcularMediaPorcentagemObtida()
    {
        //$somaPorcentagem = array_sum($this->getArrPorcentagemObtida());
        //$mediaPorcentagem = ($somaPorcentagem / $this->getQtdLinhasTabelaPontuacao());
  
        //return Vtx_Util_Formatting::roundAndDouble($mediaPorcentagem);
        
        return Vtx_Util_Formatting::roundAndDouble($this->calcularTotalPontuacaoObtida());
    }
    
    
    public function getArrPontuacaoObtida() {
        return $this->arrPontuacaoObtida;
    }

    public function setArrPontuacaoObtida($arrPontuacaoObtida) {
        $this->arrPontuacaoObtida[] = $arrPontuacaoObtida;
    }

    public function getArrPorcentagemObtida() {
        return $this->arrPorcentagemObtida;
    }

    public function setArrPorcentagemObtida($arrPorcentagemObtida) {
        $this->arrPorcentagemObtida[] = $arrPorcentagemObtida;
    }

    public function getQtdLinhasTabelaPontuacao() {
        return $this->qtdLinhasTabelaPontuacao;
    }

    public function setQtdLinhasTabelaPontuacao($qtdLinhasTabelaPontuacao) {
        $this->qtdLinhasTabelaPontuacao = $qtdLinhasTabelaPontuacao;
    }
    
    
        
} //end class