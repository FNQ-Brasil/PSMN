<?php

class Report_Devolutive_Introduction extends Report_Devolutive_Page {

	public function __construct($devolutiveReport,$devolutiveRow){
		parent::__construct($devolutiveReport,$devolutiveRow);

		$this->addPage();
		$this->create();
	}

	protected function create(){
		$this->setFontToNormal();

		$this->writeFormatted("Prezada Empresária,\n\n");
		$this->writeFormatted("Este relatório é decorrência de sua participação no Prêmio SEBRAE Mulher de Negócios (PSMN) - ciclo 2015 -, realizado pelo Serviço Brasileiro de Apoio às Micro e Pequenas Empresas (SEBRAE), Federação das Associações de Mulheres de Negócios e Profissionais do Brasil (BPW) e Secretaria de Políticas para as Mulheres (SPM), com o apoio técnico da Fundação Nacional da Qualidade (FNQ). ");

		$this->setFontToBold();

		$this->writeFormatted("Os comentários nele apresentados foram elaborados com base nas respostas de sua Autoavaliação da Gestão, ao se candidatar ao Prêmio");

		$this->setFontToNormal();

		$this->writeFormatted(".\n\nEsta devolutiva está dividida em duas partes: 1) Gráfico Radar com sua pontuação em cada um dos seguintes temas: Mercado e Vendas, Finanças, Pessoas, Processos e Operações, Inovação e Tecnologia, Legislação, Estratégia e Empreendedorismo; 2) ");

		$this->writeFormatted("Portfólio de Cursos do Sebrae, com a relação de cursos disponíveis que podem ajudá-la a melhorar a gestão de sua empresa");

		$this->setFontToNormal();

		$this->writeFormatted(".\n\nNossa expectativa é que este relatório possa ser útil na gestão de sua empresa e em sua atuação como empreendedora. Desejamos ótima leitura!\n\n");
		$this->writeFormatted("Gostaríamos ainda de agradecer sua candidatura ao Prêmio, ciclo 2015, e nos colocamos à disposição para qualquer esclarecimento.\n\n");
		$this->writeFormatted("Atenciosamente,\n\n");
		$this->writeFormatted("Realizadores do Prêmio SEBRAE Mulher de Negócios");
	}

	protected function setFontToNormal(){
		$this->setFont('Helvetica', '', 11);
		$this->setTextColorToBlack();
	}

	protected function writeFormatted($text){
		parent::writeWithUTF8(0.6, $text);
	}

	protected function setFontToBold(){
		$this->setFont('Helvetica', 'B', 11);
	}

	protected function setFontToRed(){
		$this->setTextColor(255, 0, 0);
	}

}