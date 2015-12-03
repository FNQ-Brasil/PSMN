<?php

class Report_Devolutive_Cover extends Report_Devolutive_Page {
	
	protected $tipoPDF;
	public function __construct($devolutiveReport,$tipoPDF=null){
		parent::__construct($devolutiveReport);
		
		$this->tipoPDF = $tipoPDF;
		$this->create();
	}

	private function create(){

		if($this->tipoPDF == 4)
			$coverImagePath = $this->getPublicPath().'/img/capa/cover2.jpg';
		else
			$coverImagePath = $this->getPublicPath().'/img/capa/cover.jpg';
			
		$this->addPage();
		$this->image($coverImagePath, 0, 0);
	}
}