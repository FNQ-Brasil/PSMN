<?php

class Report_Devolutive_Header extends Report_Devolutive_Page {

	public function __construct($devolutiveReport, $devolutiveRow){
		parent::__construct($devolutiveReport, $devolutiveRow);

		$this->create();
	}

	protected function create(){
		$imagePath = $this->getPublicPath().'/img/logo_psmn.png';

		$this->line(1,3.5,19.8,3.5);
		$this->image($imagePath,14.8,1,5);
	}

}