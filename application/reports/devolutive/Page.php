<?php

abstract class Report_Devolutive_Page {
	protected $devolutiveReport;
	protected $devolutiveRow;

	public function getPublicPath(){
		return Zend_Registry::get('config')->paths->public;
	}

	// pdf contents

	protected function heading($text){
		$this->setTextColorToOrange();
		$this->setFont('Helvetica', 'B', 30);
		$this->writeWithUTF8(1, $text);
		$this->setTextColorToBlack();
	}

	protected function subtitle($text){
		$this->setTextColorToPurple();
		$this->setFont('Helvetica', 'B', 15);
		$this->writeWithUTF8(1, $text);
		$this->setTextColorToBlack();
	}

	protected function writeWithUTF8($h, $text){
		return $this->write($h, utf8_decode($text));
	}

	protected function cellWithUTF8($w, $h=null, $txt=null, $border=null, $ln=null, $align=null, $fill=null, $link=null){
		return $this->cell($w, $h, utf8_decode($txt), $border, $ln, $align, $fill, $link);
	}

	protected function dataTable($headers, $rows, $columnHeight=1, $border=1, $contentAligns=null, $columnsWidth=null){
		$headerAligns = array();
		foreach($headers as $header) $headerAligns[] = 'C';

		$this->setFontToDataTableHeader();
		$this->dataTableRow($headers, $columnHeight, $border, $headerAligns, $columnsWidth);

		$this->setFontToDataTableBody();

		foreach ($rows as $row) {
			$this->dataTableRow($row, $columnHeight, $border, $contentAligns, $columnsWidth);
		}
	}

	protected function dataTableRow($cells, $columnHeight, $border, $contentAligns, $columnsWidth){
		$defaultColumnWidth = $this->calculateDataTableColumnsWidth($cells);
		$defaultContentAlign = 'L';

		$i = 0;
		foreach($cells as $cell){
			$contentAlign = $contentAligns != null ? $contentAligns[$i] : $defaultContentAlign;
			$columnWidth = $columnsWidth != null ? $columnsWidth[$i] : $defaultColumnWidth;

			$this->cellWithUTF8($columnWidth, $columnHeight, $cell, $border, 0, $contentAlign);
			$i++;
		}
		$this->ln();
	}

	protected function calculateDataTableColumnsWidth($headers){
		$pageWidth = 19;
		$columns = count($headers);
		return $pageWidth / $columns ;
	}

	protected function setFontToDataTableHeader(){} // stub
	protected function setFontToDataTableBody(){} // stub

	// text colors

	protected function setTextColorToOrange(){
		$this->setTextColor(238, 124, 72);
	}

	protected function setTextColorToBlack(){
		$this->setTextColor(34, 34, 34);
	}

	protected function setTextColorToGray(){
		$this->setTextColor(102, 102, 102);
	}

	protected function setTextColorToPurple(){
		$this->setTextColor(122,76,102);
	}

	// fpdf delegates

	protected function addPage(){
		return $this->devolutiveReport->AddPage();
	}

	protected function image($file, $x=null, $y=null, $width=null, $height=null, $type=null, $link=null){
		return $this->devolutiveReport->Image($file, $x, $y, $width, $height, $type, $link);
	}

	protected function addFont($family, $style=null, $file=null){
		return $this->devolutiveReport->AddFont($family, $style, $file);
	}

	protected function setFont($family, $style=null, $size=null){
		return $this->devolutiveReport->SetFont($family, $style, $size);
	}

	protected function cell($w, $h=null, $txt=null, $border=null, $ln=null, $align=null, $fill=null, $link=null){
		return $this->devolutiveReport->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
	}

	protected function write($h, $txt, $link=null){
		return $this->devolutiveReport->Write($h, $txt, $link);
	}

	protected function setY($y){
		return $this->devolutiveReport->SetY($y);
	}

	protected function setTextColor($r, $g=null, $b=null){
		return $this->devolutiveReport->SetTextColor($r, $g, $b);
	}

	protected function ln(){
		return $this->devolutiveReport->Ln();
	}

	protected function setFillColor($r, $g=null, $b=null){
		return $this->devolutiveReport->SetFillColor($r, $g, $b);
	}

	protected function pageNo(){
		return $this->devolutiveReport->PageNo();
	}

	protected function aliasNbPages(){
		return $this->devolutiveReport->AliasNbPages();
	}

	protected function line($x1, $y1, $x2, $y2){
		return $this->devolutiveReport->Line($x1, $y1, $x2, $y2);
	}

	// constructors

	public function __construct($devolutiveReport, $devolutiveRow=null){
		$this->devolutiveReport = $devolutiveReport;
		$this->devolutiveRow = $devolutiveRow;

		// $this->addExternalFonts();
	}

	protected function addExternalFonts(){
		$this->addLatoFont();
	}

	protected function addLatoFont(){
		$latoPath = "/lato";

		$this->addFont('Lato','', 'latoregular.php');
		$this->addFont('Lato','I', 'latoitalic.php');
		$this->addFont('Lato','B', 'latobold.php');
		$this->addFont('Lato','IB', 'latobolditalic.php');
	}

	protected function writeWithUTF8WithLink($h, $text,$link){
		return $this->writeWithLink($h, utf8_decode($text),$link);
	}

	protected function writeWithLink($h, $txt, $link){
		return $this->devolutiveReport->Write($h, $txt, $link);
	}


}