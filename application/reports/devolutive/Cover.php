<?php

class Report_Devolutive_Cover extends Report_Devolutive_Page {
	public function __construct($devolutiveReport){
		parent::__construct($devolutiveReport);
		
		$this->create();
	}

	private function create(){
		$coverImagePath = $this->getPublicPath().'/img/capa/cover.jpg';
		$this->addPage();
		$this->image($coverImagePath, 0, 0);
	}
}