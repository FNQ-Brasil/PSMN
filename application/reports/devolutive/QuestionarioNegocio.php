<?php

class Report_Devolutive_QuestionarioNegocio extends Report_Devolutive_CandidateDataPage {

	protected $managementThemeManager;
	protected $managementThemeModel;
	protected $executionPontuacaoManager;
	protected $HREF = ' ';
	protected $pontoVerificador = 0;

	public function __construct($devolutiveReport,$devolutiveRow){
		parent::__construct($devolutiveReport,$devolutiveRow);

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

		$this->createQuestionario();
		$this->createCriterioAvaliacao();
		$this->createScoreAvaliacao();
		$this->createVerificarAvaliador1();
		$this->createVerificarComentario();
		$this->createReport();

		//$this->createGraphExplanation();
		//$this->createGraph();
		//$this->createDataTable();
            
		//var_dump($this->devolutiveRow->configuraGravaPontuacaoExecution($this->devolutiveRow->getUserId()));
		//exit;
		//$verificacao = new Management_VerificacaoController();
		
		
	}

	protected function createGraph(){
		$graphImagePath = $this->createGraphImage();
		$this->setY(6.5);
		$this->image($graphImagePath, 2.5);
	}

	protected function createGraphImage(){
		$filepath = $this->getGraphImagePath();
		@unlink($filepath); // APPLICATION_PATH.'/../htdocs/devolutives/graphictest.png';
		$this->managementThemeManager->createScoreByThemeGraphic($this->getQuestionnaireId(), $this->getUserId(), $filepath);
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
			$score = number_format($theme->getThemeScore(), 2);
			$dataTableRows[] = array($theme->getThemeName(), $score);
		}

		return $dataTableRows;
	}

	protected function getScoreByTheme(){
		return $this->managementThemeModel->getScoreByTheme($this->getQuestionnaireId(), $this->getUserId());
	}

    protected function createQuestionario(){
		$this->heading('AVALIAÇÃO DA GESTÃO');
		$this->ln();
		$this->ln();
				
		$questionsAnswer = $this->devolutiveRow->getArrayDevolutiveRAA(52, $this->getUserId());
 			
		$pesos = array(
						1 => "Nada satisfeita",
						2 => "Pouco satisfeita",
						3 => "Muito satisfeita",
						4 => "Totalmente satisfeita"
		); 
		 foreach ($questionsAnswer[0] as $questionId=>$question) {
			 
 
			 $numero = $question["designation"];
			 $pergunta = $question["pergunta"];
			 $respostaUser = $pesos[$question["alternative_designation"]];
			 $comentario = $question["write_answer"];
			 
			 $this->setFontToBold();
		     $this->writeFormatted($numero . ". " . $pergunta. ": \n");
			 $this->setFontToNormal();
			 $this->writeFormatted("Resposta: " . $respostaUser. "\n");
			 
			 if($comentario != "")
			 	$this->writeFormatted("Comentário: " . $comentario ."\n\n");
			 else
			 	$this->writeFormatted("\n");
			 
		 }
		
		
	}
	
		
	public function comentario($array,$value) {
		$filtered = false;
		$member = "CriterioNumber";

		foreach($array as $k => $v) {
		
			if($v["CriterioNumber"] == $value)
			$filtered = $v["Comment"];
		}
		
		return $filtered;
	}
	
	protected function createCriterioAvaliacao(){
		$this->addPage();

		$this->heading('PONTOS FORTES E OPORTUNIDADES DE MELHORIA');
		$this->ln();
		$this->ln();
		$questionsAnswer = $this->devolutiveRow->getArrayCriterioAvaliacao();

		$alpha[0] = 'Alphabet';
		for ($i = 'A'; $i<'Z'; $i++) {
			$alpha[] = $i;
		}
		$alpha[26] = 'Z';
		$pesos = array(
						"F" => "Ponto forte",
						"M" => "Oportunidade de melhoria"
		); 
		 
	
		 foreach ($questionsAnswer["questionsAvaliacao"] as $question) {
			 $numero = $alpha[$question["Id"]] ;
			 $pergunta = $question["Value"];
			 $respostaUser =$questionsAnswer["respostas"][$question["Id"]];

			 if($respostaUser["Resposta"] == "F"){
			   $this->pontoVerificador +=2;
			   if( $this->pontoVerificador == 17)  $this->pontoVerificador -= 1;
			   if( $this->pontoVerificador == 18)  $this->pontoVerificador -= 1;
			   if( $this->pontoVerificador == 19)  $this->pontoVerificador -= 1;
			   if( $this->pontoVerificador == 20)  $this->pontoVerificador -= 1;
			   if( $this->pontoVerificador == 21)  $this->pontoVerificador -= 1;
			 }
						
						
			 $respostaUser = $respostaUser["Resposta"];
  			 $this->setFontToBold();
		     $this->writeFormatted($numero . ". " . $pergunta. "\n");
			 $this->setFontToNormal();
			 $this->writeFormatted("Resposta: " . $pesos[$respostaUser]. "\n\n");
 
			 
		 }
		 $this->writeFormatted("Comentário: ". $questionsAnswer["conclusao"]. "\n\n");

		// exit;
		
		
		
		
	}
	
 protected function createScoreAvaliacao(){
	    $this->addPage();
		$this->heading('PONTUAÇÃO DO RELATO DOS AVALIADORES E VERIFICADOR');
		$this->ln();
		$this->ln();
		
		$questionsScore = (object)$this->devolutiveRow->getArrayVerificacao();
		
		$questionsAnswer =(object) $this->devolutiveRow->getArrayReport();
		
		
		
	
		$score = $questionsAnswer->scores;
		$pontosFinal = $questionsScore->respostas[0]->getPontosFinal();
 
 
		
		$dataTableRows = array();
		
	    $avaliador = $score->getFirstNameAvaliadorPri();
		$pontos = 0;
		$qtd =0;
		if ($avaliador){
			
			if ($score->getAppraiserStatus() == 'C'){
				$ponto = number_format($score->getPontos(), 1 , ',', '.') . " pts.";
				$pontos+=(float)$score->getPontos();
				$qtd++;
			}
			elseif ($score->getAppraiserStatus() == 'I')
				$ponto = "Iniciado";
			elseif ($score->getAppraiserStatus() == 'N')
			    $ponto = "Não Iniciado";
			$dataTableRows[] = array($avaliador . " (" . $score->getLoginAvaliadorPri() . ") ", $ponto);

		}	
		$avaliador = $score->getFirstNameAvaliadorSec();
	
		if ($avaliador){
			
			if ($score->getAppraiserStatusSec() == 'C'){
				$ponto = number_format($score->getPontosSec(), 1 , ',', '.') . " pts";
				$pontos+=(float)$score->getPontosSec();
				$qtd++;
			}
			elseif ($score->getAppraiserStatusSec() == 'I')
				$ponto = "Iniciado";
			elseif ($score->getAppraiserStatusSec() == 'N')
			    $ponto = "Não Iniciado";
			$dataTableRows[] = array($avaliador . " (" . $score->getLoginAvaliadorPri() . ") ", $ponto);

		}	
		
		$avaliador = $score->getFirstNameAvaliadorTer();

		if ($avaliador){
			
			if ($score->getAppraiserStatusTer() == 'C'){
				$ponto = number_format($score->getPontosTer(), 1 , ',', '.') . " pts";
				$pontos+=(float)$score->getPontosTer();
				$qtd++;
			}
			elseif ($score->getAppraiserStatusTer() == 'I')
				$ponto = "Iniciado";
			elseif ($score->getAppraiserStatusTer() == 'N')
			    $ponto = "Não Iniciado";
			$dataTableRows[] = array($avaliador . " (" . $score->getLoginAvaliadorPri() . ") ", $ponto);

		}	

		$this->ln();
		$this->dataTable(array('AVALIADOR ESTADUAL','PONTUAÇÃO'), $dataTableRows, 1, 1, array('C','C'));
		$this->ln();

		$this->setFontToDataTableHeader();
		$this->cellWithUTF8(9.5, 1, 'MÉDIA AVALIADORES', 1, 0, 'C');
		$media = $pontos/$qtd;
		
		$media = number_format($media, 1 , ',', '.') . " pts";
		
		$this->setFontToDataTableBody();
		$this->cellWithUTF8(9.5, 1,$media, 1, 1, 'C');
		
		
		
		$this->setFontToDataTableHeader();
		$this->cellWithUTF8(9.5, 1, 'VERIFICADOR' . " (" . $score->getLoginChecker() . ")", 1, 0, 'C');
		$media = $pontos/$qtd;
		
		$pontosFinal = number_format($pontosFinal, 1 , ',', '.') . " pts";
		
		$this->setFontToDataTableBody();
		$this->cellWithUTF8(9.5, 1,$pontosFinal, 1, 1, 'C');
		
		
		
		
	}
	
	protected function createVerificarComentario(){
	    $this->addPage();
		$this->heading('VERIFICAÇÃO DO RELATO');
		$this->ln();
		$this->ln();
		//$questionsAnswer =(object) $this->devolutiveRow->getArrayVerificacao("avafnq");

		$questionsAnswer = (object)$this->devolutiveRow->getArrayVerificacao();
		

		$blocoAnterior = ''; 
		$criterioAnterior = '';
		$qsts = array();
		$blocos = array();
		$criterios = array(); 
		$model_apevaluationverificador = new Model_ApeEvaluationVerificador();
		

		//["$blockId$criterioId"]['Comment'];
		//Questoes
	
		foreach ($questionsAnswer->questoes as $k => $questao){
			$questaoLetra = $questao->getQuestaoLetra();
			$bloco = $questao->getBloco();
			$criterio = $questao->getCriterio();
			if ($bloco != $blocoAnterior) {
				$qsts[$bloco] = array(
				'BlockName' => Vtx_Util_PsmnAvaliacao::BlocosAvaliacao($bloco), 'Criterions' => array()
									);
			}
			
			if ($criterio != $criterioAnterior) {
				$qsts[$bloco]['Criterions'][$criterio] = array(
				'CriterionValue' => Vtx_Util_PsmnAvaliacao::CriteriosAvaliacao($bloco, $criterio),
				'Questions' => array()
				);
				
			}
			$qsts[$bloco]['Criterions'][$criterio]['Questions'][$questao->getId()] = $questao;
			$blocoAnterior = $bloco;
			$criterioAnterior = $criterio;
		
		}
		

	
		
		$dataTableRows = array();
		$item = 0;
		$subitem;
		
		
  
		//$this->respostas[$questaoId]['Resposta']
		
		 foreach ($qsts as $blockId => $bloco){
			 $item++;
			 $subitem = 0;
			 $this->setFontToBold();
		     $this->writeFormatted($item .". " . $bloco['BlockName']. "\n");
			 
			 
			 foreach ( $qsts[$blockId]['Criterions'] as $criterioId => $criterio){
				 $subitem++;
				  $this->setFontToBold();
				 $this->writeFormatted("\t\t\t\t\t\t\t\t".$item ."." . $subitem ." " .   $criterio['CriterionValue'] . "\n");
				 
 				 $this->setFontToNormal();
		 
				 
				 $comentario = $this->comentario($questionsAnswer->commentAnswers,"$blockId$criterioId");
				 
				 $this->writeFormatted("Comentário: " . $comentario . "\n\n");

				  
			 }
			 		
			
		
		 }
		
	}
	
	
	
		protected function createReport(){
	    $this->addPage();
		$this->heading('RELATO DA CANDIDATA');
		$this->ln();
		$this->ln();
		
		//$questionsAnswer =(object) $this->devolutiveRow->getArrayVerificacao("avafnq");
		$questionsAnswer =(object) $this->devolutiveRow->getArrayReport();
		
 
 	    $this->setFontToNormal();
	    $this->writeFormatted($questionsAnswer->report->getReport());
	
		
	}
	
		protected function createVerificarAvaliador1(){
	    $this->addPage();
		$this->heading('VERIFICAÇÃO DO RELATO COMENTÁRIOS');
		$this->ln();
		$this->ln();
		
		
		
		//$questionsAnswer =(object) $this->devolutiveRow->getArrayVerificacao("avafnq");
		$questionsAnswer =(object) $this->devolutiveRow->getArrayVerificacao();
		
		
		$blocoAnterior = ''; 
		$criterioAnterior = '';
		$qsts = array();
		$blocos = array();
		$criterios = array(); 
		$model_apevaluationverificador = new Model_ApeEvaluationVerificador();


		//["$blockId$criterioId"]['Comment'];
		//Questoes
		foreach ($questionsAnswer->questoes as $k => $questao){
			$questaoLetra = $questao->getQuestaoLetra();
			$bloco = $questao->getBloco();
			$criterio = $questao->getCriterio();
			if ($bloco != $blocoAnterior) {
				$qsts[$bloco] = array(
				'BlockName' => Vtx_Util_PsmnAvaliacao::BlocosAvaliacao($bloco), 'Criterions' => array()
									);
			}
			if ($criterio != $criterioAnterior) {
				$qsts[$bloco]['Criterions'][$criterio] = array(
				'CriterionValue' => Vtx_Util_PsmnAvaliacao::CriteriosAvaliacao($bloco, $criterio),
				'Questions' => array()
				);
				
			}
			$qsts[$bloco]['Criterions'][$criterio]['Questions'][$questao->getId()] = $questao;
			$blocoAnterior = $bloco;
			$criterioAnterior = $criterio;
		
		}
		
		$arrRespostas = array();
		
			$pesos = array(
							'D' => "Não Evidenciado",
							'A' => "Levemente Evidenciado",
							'S' => "Fortemente Evidenciado"
			); 
			
		foreach ($questionsAnswer->respostas as $k => $resposta){
			
			$arrRespostas[$resposta->getAvaliacaoPerguntaId()] = $pesos[$resposta->getResposta()];
			
		}
	
 
		$dataTableRows = array();
		$item = 0;
		$subitem;
		
		$letras = array('A','B','C','D','E','F','G','H','I');
	
       
		//$this->respostas[$questaoId]['Resposta']
		

		 foreach ($qsts as $blockId => $bloco){
			 $item++;
			 $subitem = 0;
			 $dataTableRows[] = array("\t" . $item .". " . $bloco['BlockName'], "");
			 
			 foreach ( $qsts[$blockId]['Criterions'] as $criterioId => $criterio){
				 $subitem++;
				 $dataTableRows[] = array("\t\t\t\t\t\t\t\t" . $item ."." . $subitem ." " .   $criterio['CriterionValue'] , "");
				 
		
		$ii = 0;
				  foreach ($qsts[$blockId]['Criterions'][$criterioId]['Questions'] as $questaoId => $questao){
				   
	                    $resposta =isset($arrRespostas[$questaoId]) ? $arrRespostas[$questaoId]: "Não Evidenciado";
						  
						  
						 
						  $dataTableRows[] = array("\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t" .$letras[$ii], $resposta);
						  $ii = $ii + 1;
				  }
				  
			 }
			 		
			
		
		 }
		
		
		
		
		
		
		
		
		
		
		$this->ln();
		$this->dataTable(array('CRITÉRIOS, SUBCRITÉRIOS E ITENS','PONTUAÇÃO'), $dataTableRows, 1, 1, array('L','C'));
		$this->ln();
		
		
		
		
	}
	
	
	
	protected function createTotalScore(){
		$this->setFontToDataTableHeader();
		$this->cellWithUTF8(9.5, 1, 'TOTAL', 1, 0, 'C');

		$finalScore = number_format($this->getFinalScore(), 2);

		$this->setFontToDataTableBody();
		$this->cellWithUTF8(9.5, 1, $finalScore, 1, 1, 'C');
	}

	protected function getFinalScore(){
		return $this->executionPontuacaoManager->calculateExecutionScore($this->getQuestionnaireId(), $this->getUserId());
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