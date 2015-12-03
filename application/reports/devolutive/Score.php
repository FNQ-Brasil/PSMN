<?php

class Report_Devolutive_Score extends Report_Devolutive_CandidateDataPage {

	protected $managementThemeManager;
	protected $managementThemeModel;
	protected $executionPontuacaoManager;
	protected $HREF = ' ';
	protected $verificador = false;

	public function __construct($devolutiveReport,$devolutiveRow,$verificador = false){
		parent::__construct($devolutiveReport,$devolutiveRow);

		$this->verificador =  $verificador;
		$this->initializeInstanceVariables();

		$this->addPage();
		$this->create();
	}

	protected function initializeInstanceVariables(){
		$this->managementThemeManager = new Manager_ManagementTheme();
		$this->managementThemeModel = new Model_ManagementTheme();
		$this->executionPontuacaoManager = new Manager_ExecutionPontuacao();
	}

	protected function create(){
		$this->heading('DESEMPENHO');
		$this->ln();
		$this->ln();

		$this->createGraphExplanation();
		$this->createGraph();
		$this->createDataTable();
	}

	protected function createGraph(){
		$graphImagePath = $this->createGraphImage();
		$this->setY(6.5);
		$this->image($graphImagePath, 2.5);
	}

	protected function createGraphImage(){
		$filepath = $this->getGraphImagePath();
		@unlink($filepath); // APPLICATION_PATH.'/../htdocs/devolutives/graphictest.png';
		$this->managementThemeManager->createScoreByThemeGraphic($this->getQuestionnaireId(), $this->getUserId(), $filepath,$this->verificador);
		return $filepath;
	}

	protected function getGraphImagePath(){
		return preg_replace('/[^\/]+$/','graphic.png',$this->devolutiveReport->getPathToSave());
	}

	protected function getQuestionnaireId(){
		return $this->devolutiveRow->getQuestionnaireId();
	}

	protected function getUserId(){
		return $this->devolutiveRow->getUserId();
	}

	protected function createDataTable(){
		$dataTableRows = $this->getDataTableRows();

		$this->ln();
		$this->dataTable(array('TEMA DE GESTÃO','PONTUAÇÃO (%)'), $dataTableRows, 1, 1, array('C','C'));
		$this->ln();

		$this->createTotalScore();
	}

	protected function getDataTableRows(){
		$dataTableRows = array();

		foreach($this->getScoreByTheme() as $theme){
			$score = number_format($theme->getThemeScore(), 1);
			$dataTableRows[] = array($theme->getThemeName(), $score);
		}

		return $dataTableRows;
	}

	protected function getScoreByTheme(){
		return $this->managementThemeModel->getScoreByTheme($this->getQuestionnaireId(), $this->getUserId(),$this->verificador);
	}

	protected function createTotalScore(){
		$this->setFontToDataTableHeader();
		$this->cellWithUTF8(9.5, 1, 'TOTAL', 1, 0, 'C');

		$finalScore = number_format($this->getFinalScore(), 1);

		$this->setFontToDataTableBody();
		$this->cellWithUTF8(9.5, 1, $finalScore, 1, 1, 'C');
	}

	protected function getFinalScore(){
		return $this->executionPontuacaoManager->calculateExecutionScore($this->getQuestionnaireId(), $this->getUserId(),$this->verificador);
	}

	protected function setFontToDataTableHeader(){
		$this->setFont('Helvetica', 'B', 11);
		$this->setTextColorToOrange();
	}

	protected function setFontToDataTableBody(){
		$this->setFont('Helvetica', '', 11);
		$this->setTextColorToBlack();
	}

	protected function createGraphExplanation(){
		$this->setFontToBold();
		$this->writeFormatted("Interpretação Padrão do Gráfico Radar \n\n");

		$this->setFontToNormal();
		$this->writeFormatted("O gráfico radar apresenta o percentual de atendimento da candidata em função da pontuação máxima (100%) de cada um dos 8 temas de gestão. Pontuações acima de 80% apontam que você já adota a maioria das práticas solicitadas para o tema de gestão em questão, e está preparada para tentar evoluir para um novo patamar.  Deve, entretanto, continuar buscando realizar aquelas ações ou buscar os conhecimentos para as questões com os quais ainda não está totalmente satisfeita.\n\n");
		$this->writeFormatted("Se a pontuação estiver entre 60% a 80%, recomenda-se implementar ações que estão sendo realizadas e buscar os conhecimentos necessários. Significa que muitas práticas solicitadas estão implementadas, no entanto, ainda há lacunas que podem estar impactando o desempenho e a competitividade de seu negócio. \n\n");
		$this->writeFormatted("Para as pontuações abaixo de 60%, significa que apenas algumas práticas solicitadas estão sendo atendidas. Recomenda-se a adoção das práticas e a busca dos conhecimentos com os quais não está satisfeita e que são fundamentais para uma boa gestão. O Sebrae e a FNQ dispõem de diversas ferramentas que podem ajudá-la na melhoria da gestão de seu negócio. Acesse ");

		$this->writeLink("<A HREF='www.sebrae.com.br'>www.sebrae.com.br</A>");

		$this->setFontToNormal();
		$this->writeFormatted(' ou ');

		$this->writeLink("<A HREF='www.fnq.org.br'>www.fnq.org.br</A>");

		$this->setFontToNormal();
		$this->writeFormatted(" e conheça as soluções que podem contribuir para o aumento da competitividade de sua empresa. \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Conheça abaixo quais perguntas do questionário de avaliação da gestão estão relacionadas a cada tema de gestão: \n\n");
		$this->writeFormatted("Mercado e Vendas: ");

		$this->setFontToNormal();
		$this->writeFormatted("1 a 6 \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Finanças: ");

		$this->setFontToNormal();
		$this->writeFormatted("7 a 11 \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Pessoas: ");

		$this->setFontToNormal();
		$this->writeFormatted("12 a 13 \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Processos e Operações: ");

		$this->setFontToNormal();
		$this->writeFormatted("1 a 16 \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Inovação e Tecnologia: ");

		$this->setFontToNormal();
		$this->writeFormatted("1 a 16 \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Legislação: ");

		$this->setFontToNormal();
		$this->writeFormatted("12, 14 e 15 \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Estratégia: ");

		$this->setFontToNormal();
		$this->writeFormatted("1 a 16 \n\n");

		$this->setFontToUnderline();
		$this->writeFormatted("Empreendedorismo: ");

		$this->setFontToNormal();
		$this->writeFormatted("1 a 16");
	}

	protected function setFontToBold(){
		$this->setFont('Helvetica', 'B', 11);
	}

	protected function setFontToUnderline(){
		$this->setFont('Helvetica', 'U', 11);
	}

	protected function setFontToNormal(){
		$this->setFont('Helvetica', '', 11);
		$this->setTextColorToBlack();
	}

	protected function writeFormatted($text){
		parent::writeWithUTF8(0.6, $text);
	}

	function writeLink($html) {
		$html = str_replace("\n",' ',$html);
		$link = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);

		$attr = array();

		$attr['HREF'] = $link[2];

		$this->SetHref($attr);

		if ($this->HREF)
			$this->PutLink($this->HREF, $attr['HREF']);

		$this->ClearHref();

	}

	function SetHref($attr) {
		$href = isset($attr['HREF']) ? $attr['HREF'] : 'teste';
		$this->HREF = $href;
	}


	function ClearHref() {
		$this->HREF='';
	}

	function PutLink($URL,$txt) {
		$this->SetTextColor(0,0,255);
		$this->setFont('Helvetica', 'U', 11);
		$this->writeFormattedWithLink($txt,$URL);
		$this->SetTextColor(0);
	}

	protected function writeFormattedWithLink($text,$link){
		parent::writeWithUTF8WithLink(0.6, $text,$link);
	}

}