<?php

class Report_Devolutive_CandidateDataPage extends Report_Devolutive_Page {
	protected $modelEnterprise;
	protected $modelAddressEnterprise;
	protected $modelPresident;
	protected $modelAddressPresident;
	protected $modelState;
	protected $modelCity;
	protected $modelNeighborhood;
	protected $modelPosition;
	protected $modelEducation;
	protected $modelCategoryAward;

	protected function createDataTable(){
		$fieldsets = $this->getDataTableFieldsets();
		$labels = $this->getAttributeTranslations();

		foreach($fieldsets as $fieldset) {
			foreach ($fieldset as $attr_name => $attr_value) $this->createLabel($labels[$attr_name]);
			$this->ln();
			foreach ($fieldset as $attr_name => $attr_value) $this->createField($attr_value);
			$this->ln();
		}
	}

	protected function getDataTableFieldsets() { // stub
		array();
	}

	protected function getAttributeTranslations() { // stub
		array();
	}

	protected function createLabel($label){
		$this->setFontToLabel();
		$this->cellWithUTF8(6, 1, $label, 0, 0);
		$this->cellWithUTF8(0.4, 1);
	}

	protected function setFontToLabel(){
		$this->setFont('Helvetica', '', 11);
		$this->setTextColorToGray();
	}

	protected function createField($value){
		$this->setFontToField();
		$value = $this->cutToFit($value);
		$this->cellWithUTF8(6, 1, $value, 0, 0, 'L', true);
		$this->cellWithUTF8(0.4, 1);
	}

	protected function setFontToField(){
		$this->setFont('Helvetica', 'B', 10);
		$this->setTextColorToBlack();
		$this->setFillColor(221, 221, 221);
	}

	public function cutToFit($text,$limit=null){
		if($limit == null)
			$limit = 30;
		return strlen($text) > $limit ? substr($text, 0, $limit).'...' : $text;
	}

	protected function getAddressEnterprise(){
		return $this->modelAddressEnterprise->getAddressEnterpriseByEnterpriseId($this->getEnterprise()->getId());
	}

	protected function getEnterprise(){
		$userId = $this->devolutiveRow->getUserId();
        $idKeyEnterprise = $this->modelEnterprise->getIdKeyByUserId($userId);
        return $this->modelEnterprise->getEnterpriseByIdKey($idKeyEnterprise);
	}

	protected function getPresident(){
		return $this->modelPresident->getPresidentByEnterpriseId($this->getEnterprise()->getId());
	}

	protected function getAddressPresident(){
		return $this->modelAddressPresident->getAddressPresidentByPresidentId($this->getPresident()->getId());
	}

	public function __construct($devolutiveReport, $devolutiveRow){
		parent::__construct($devolutiveReport, $devolutiveRow);

		$this->initializeInstanceVariables();
	}

	private function initializeInstanceVariables(){
		$this->modelEnterprise = new Model_Enterprise();
		$this->modelAddressEnterprise = new Model_AddressEnterprise();
		$this->modelPresident = new Model_President();
		$this->modelAddressPresident = new Model_AddressPresident();
		$this->modelState = new Model_State();
		$this->modelCity = new Model_City();
		$this->modelNeighborhood = new Model_Neighborhood();
		$this->modelPosition = new Model_Position();
		$this->modelEducation = new Model_Education();
		$this->modelCategoryAward = new Model_EnterpriseCategoryAward();
	}

}