<?php

require_once (APPLICATION_PATH_LIBS .'/Fpdf/fpdf.php');

class Report_Devolutive_PDF extends FPDF {
	protected $devolutiveRow;
	protected $pathToSave;

	public function saveToFile($path=null){
		$p = ($path == null ? $this->pathToSave : $path);
		$this->Output($p);
	}

	public function Header(){
		new Report_Devolutive_Header($this, $this->devolutiveRow);
	}

	public function Footer(){
		new Report_Devolutive_Footer($this, $this->devolutiveRow);
	}

	public function __construct($devolutiveRow, $pathToSave){
		parent::__construct('P','cm','A4');

		$this->AliasNbPages();
		$this->SetTopMargin(4);
		$this->SetAutoPageBreak(true, 3.5);

		$this->devolutiveRow = $devolutiveRow;
		$this->pathToSave = $pathToSave;

		new Report_Devolutive_Cover($this);
		new Report_Devolutive_Introduction($this, $devolutiveRow);
		new Report_Devolutive_Enterprise($this, $devolutiveRow);
		new Report_Devolutive_President($this, $devolutiveRow);
		new Report_Devolutive_Score($this, $devolutiveRow);
		new Report_Devolutive_Courses($this,$devolutiveRow);
		new Report_Devolutive_NextSteps($this,$devolutiveRow);
	}

	public function getPathToSave(){
		return $this->pathToSave;
	}

}