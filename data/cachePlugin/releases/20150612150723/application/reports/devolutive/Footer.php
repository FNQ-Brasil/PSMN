<?php

class Report_Devolutive_Footer extends Report_Devolutive_Page {
	public function __construct($devolutiveReport, $devolutiveRow){
		parent::__construct($devolutiveReport, $devolutiveRow);

		if(!$this->isCoverPage()) $this->create();
	}

	protected function isCoverPage(){
		return $this->pageNo() == 1;
	}

	protected function create(){
		return $this->isCandidateDataPage() ? $this->createWithProtocol() : $this->createWithoutProtocol();
	}

	protected function isCandidateDataPage(){
		$candidateDataPages = array(2);
		return in_array($this->pageNo(), $candidateDataPages);
	}

	protected function createWithProtocol(){
		$this->setY(-1.5);
		$this->setFont('Helvetica', 'BI', 11);

		$protocol = $this->getProtocol();
		$createdAt = $this->getCreatedAt();

		$text = '(Protocolo '.$protocol.' às '.$createdAt.')';

		return $this->writeWithUTF8(1, $text);
	}

	protected function getProtocol(){
		return $this->devolutiveRow->getProtocolo();
	}

	protected function getCreatedAt(){
		return $this->devolutiveRow->getProtocoloCreateAt();
	}

	protected function createWithoutProtocol(){
		$createdAt = $this->getCreatedAt();

		$this->setY(-1.9);
		$this->setFont('Arial','BI',8);

		$this->line(1,27,19.8,27);
		$this->cellWithUTF8(0.9,1,$this->pageNo().'/{nb}');
		$this->cellWithUTF8(7.2,1,"Emissão: $createdAt",0,0,'C');

		$this->createImage();
	}

	protected function createImage(){

		$imgXPos = 9.7;
        $imgYPos = 27.7;
        $imgWidth =10.0;

		$imgFooter = $this->getPublicPath().'/img/logos_psmn_fundo_branco.png';

		$this->Image( $imgFooter,$imgXPos,$imgYPos,$imgWidth);

	}

}