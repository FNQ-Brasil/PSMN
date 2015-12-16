<?php

class Report_Devolutive_IntroducaoVerificador extends Report_Devolutive_Page {

	public function __construct($devolutiveReport,$devolutiveRow){
		parent::__construct($devolutiveReport,$devolutiveRow);

		$this->addPage();
		$this->create();
	}

	protected function create(){
		
		$this->heading('INTRODUÇÃO');
		$this->ln();
		$this->ln();
		
		$this->setFontToNormal();

 
		$this->writeFormatted("Este relatório foi gerado com base nas avaliações dos avaliadores e na visita do verificador do Prêmio SEBRAE Mulher de Negócios, ao validar as respostas da ");
		
		$this->writeFormatted("Autoavaliação da Gestão ",true);
		
		$this->writeFormatted("e as informações apresentadas no ");

		$this->writeFormatted("Relato. ", true);

		$this->writeFormatted("Ele contém o parecer do verificador quanto ao atendimento das ");
		
		$this->writeFormatted("16 perguntas de Gestão, ",true);
		
		$this->writeFormatted("e os comentários dos avaliadores e verificador com relação ao relato, levando-se em conta o atendimento aos ");
		
		$this->writeFormatted("Itens de Verificação do Relato.\n\n",true);
			
		$this->writeFormatted("O documento está dividido em quatro partes: ");

		$this->writeFormatted("1) Gráfico Radar com a pontuação ajustada após a visita ",true);
		
		$this->writeFormatted("do verificador, em cada um dos seguintes temas: Mercado e Vendas, Processo e Operações, Finanças, Pessoas, Legislação e Normas, Inovação e Tecnologia, Estratégia e Empreendorismo; ");
				
		$this->writeFormatted("2) Tabela de Avaliação da Gestão, ",true);
		$this->writeFormatted("destacando como cada uma ");
		$this->writeFormatted("das 16 questões de Gestão ",true);
		$this->writeFormatted("foi avaliada pelo verificador e quais foram os Pontos Fortes ou Oportunidades de Melhoria. No questionário de Gestão, por pergunta, o avaliador colocará um comentário, identificando qual(is) a(s) principal(is) lacuna(s) identificada(s) na visita; ");
		$this->writeFormatted("3) Pontuação do(s) avaliador(es) e do verificador para o Relato da Candidata;"); 
		$this->writeFormatted("apresentando o grau de internalização dos 11 Fundamentos de Excelência, disseminados pela Fundação Nacional da Qualidade (FNQ), na ");
		$this->writeFormatted("Criação do Negócio, Desenvolvimento (condução) do Negócio e nos Resultados ",true);
		$this->writeFormatted("gerados; ");
		$this->writeFormatted("4) Relato da Candidata. \n\n",true);

		$this->writeFormatted("Realizadores do Prêmio SEBRAE Mulher de Negócios. ");
	}

	protected function writeFormatted($text,$bold=false){
		if($bold) $this->setFontToBold();
		else $this->setFontToNormal();
 
		parent::writeWithUTF8(0.6, $text);		
	}
	
	protected function setFontToNormal(){
		$this->setFont('Helvetica', '', 11);
		$this->setTextColorToBlack();
	}


	protected function setFontToBold(){
		$this->setFont('Helvetica', 'B', 11);
	}

	protected function setFontToRed(){
		$this->setTextColor(255, 0, 0);
	}

}
