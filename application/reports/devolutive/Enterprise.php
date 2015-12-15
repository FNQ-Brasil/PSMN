<?php

class Report_Devolutive_Enterprise extends Report_Devolutive_CandidateDataPage {

	public function __construct($devolutiveReport, $devolutiveRow){
		parent::__construct($devolutiveReport, $devolutiveRow);

		$this->addPage();
		$this->create();
	}

	private function create(){
		$this->heading('DADOS DA CANDIDATA');
		$this->ln();
		$this->ln();
		$this->createDataTable();
	}

	protected function getDataTableFieldsets(){ // override for Report_Devolutive_CandidateDataPage
		$enterprise = $this->getEnterprise();
		$addressEnterprise = $this->getAddressEnterprise();

		$cnpj = Vtx_Util_Formatting::maskFormat($enterprise['Cnpj'], '##.###.###/####-##');
		$creationDate = strtotime($enterprise['CreationDate']);
		$creationDate = date('d/m/Y',$creationDate);
		$annualRevenue = $this->getAnnualRevenue();
		$categorySector = $this->getCategorySector();
		$cep = Vtx_Util_Formatting::maskFormat($addressEnterprise['Cep'], '#####-###');
		$state = $this->getState();
		$city = $this->getCity();
		$neighborhood = $this->getNeighborhood();

		$categoryAward = $this->getCategoryAward();

		return array(
			array(
				'SocialName' => $enterprise['SocialName'],
				'FantasyName' => $enterprise['FantasyName'],
				'Cnpj' => $cnpj
			),
			array(
				'CreationDate' => $creationDate,
				'EmailDefault' => $enterprise['EmailDefault'],
				'Cnae' => $enterprise['Cnae']
			),
			array(
				'Phone' => $enterprise['Phone'],
				'Site' => $enterprise['Site'],
				'EmployeesQuantity' => $enterprise['EmployeesQuantity']
			),
			array(
				'AnnualRevenue' => $annualRevenue,
				'CategorySector' => $categorySector,
				'Cep' => $cep
			),
			array(
				'StreetNameFull' => $addressEnterprise['StreetNameFull'],
				'StreetNumber' => $addressEnterprise['StreetNumber'],
				'StreetCompletion' => $addressEnterprise['StreetCompletion']
			),
			array(
				'Neighborhood' => $neighborhood,
				'City' => $city,
				'State' => $state
			),
			array(
				'CategoryAward' => $categoryAward
			)
		);
	}

	private function getAnnualRevenue(){
        return Vtx_Util_Array::annualRevenuePsmn($this->getEnterprise()->getAnnualRevenue());
    }

    private function getCategorySector(){
        return Vtx_Util_Array::categorySector($this->getEnterprise()->getCategorySectorId());
    }

    private function getState(){
        return $this->modelState->getStateById($this->getAddressEnterprise()->getStateId())->getName();
    }

    private function getCity(){
        return $this->modelCity->getCityById($this->getAddressEnterprise()->getCityId())->getName();
    }

    private function getNeighborhood(){
    	$neighborhoodId = $this->getAddressEnterprise()->getNeighborhoodId();
    	return $this->modelNeighborhood->getNeighborhoodById($neighborhoodId)->getName();
    }

	private function getCategoryAward(){
		return $this->modelCategoryAward->getCategoryAwardById($this->getEnterprise()->getCategoryAwardId())
			->getDescription();
	}

	protected function getAttributeTranslations(){ // override for Report_Devolutive_CandidateDataPage
		return array(
			'SocialName' => 'Razão Social',
			'FantasyName' => 'Nome Fantasia',
			'Cnpj' => 'CNPJ',
			'CreationDate' => 'Data de Abertura',
			'EmailDefault' => 'E-mail',
			'Cnae' => 'CNAE',
			'Phone' => 'Telefone',
			'Site' => 'Site',
			'EmployeesQuantity' => 'Nº de Empregados',
			'AnnualRevenue' => 'Porte da Empresa',
			'CategorySector' => 'Setor',
			'Cep' => 'CEP',
			'StreetNameFull' => 'Endereço',
			'StreetNumber' => 'Número',
			'StreetCompletion' => 'Complemento',
			'Neighborhood' => 'Bairro',
			'City' => 'Cidade',
			'State' => 'UF',
			'CategoryAward' => 'Categoria'
		);
	}

}