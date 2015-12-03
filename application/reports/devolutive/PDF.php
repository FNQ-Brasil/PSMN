<?php

require_once (APPLICATION_PATH_LIBS .'/Fpdf/fpdf.php');

class Report_Devolutive_PDF extends FPDF {
	protected $devolutiveRow;
	protected $pathToSave;
	protected $tipoPDF =0;

	public function saveToFile($path=null){
		$p = ($path == null ? $this->pathToSave : $path);
		$this->Output($p);
	}

	public function Header(){
		new Report_Devolutive_Header($this, $this->devolutiveRow);
	}

	public function Footer(){
		new Report_Devolutive_Footer($this, $this->devolutiveRow,$this->tipoPDF);
	}
	
	public function __construct($devolutiveRow, $pathToSave,$tipoPDF=null){
		parent::__construct('P','cm','A4');

		$this->AliasNbPages();
		$this->SetTopMargin(4);
		$this->SetAutoPageBreak(true, 3.5);

		$this->devolutiveRow = $devolutiveRow;
		$this->pathToSave = $pathToSave;
	    $this->tipoPDF = $tipoPDF;
		new Report_Devolutive_Cover($this,$tipoPDF);
		
		if($tipoPDF != 4) new Report_Devolutive_Introduction($this, $devolutiveRow);
		
		if($tipoPDF == 4)  new Report_Devolutive_IntroducaoVerificador($this, $devolutiveRow);
		
		
		new Report_Devolutive_Enterprise($this, $devolutiveRow);
		new Report_Devolutive_President($this, $devolutiveRow);
		
		if($tipoPDF == 4) new Report_Devolutive_Score($this, $devolutiveRow,true);
		else  new Report_Devolutive_Score($this, $devolutiveRow);

		if($tipoPDF == 4) new Report_Devolutive_QuestionarioNegocio($this, $devolutiveRow);
		
		if($tipoPDF != 4){
			new Report_Devolutive_Courses($this,$devolutiveRow);
			new Report_Devolutive_NextSteps($this,$devolutiveRow);
		}
	}

	public function getPathToSave(){
		return $this->pathToSave;
	}
	
	//$TipoPDF = 3 Tipo PSMN (Mulhere de Negocio)
	//$TipoPDF = 4 Tipo Avaliação do Negocio (VERIFICADOR)
	public function getTipoPDF(){
		return $tipoPDF;
	}

}