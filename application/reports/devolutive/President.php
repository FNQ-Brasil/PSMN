<?php

class Report_Devolutive_President extends Report_Devolutive_CandidateDataPage {

	public function __construct($devolutiveReport, $devolutiveRow){
		parent::__construct($devolutiveReport, $devolutiveRow);

		$this->addPage();
		$this->create();
	}

	private function create(){
		$this->heading('DADOS DO REPRESENTANTE');
		$this->ln();
		$this->ln();
		$this->createDataTable();
	}

	protected function getDataTableFieldsets(){ // override for Report_Devolutive_CandidateDataPage
		$president = $this->getPresident();
		$addressPresident = $this->getAddressPresident();

		$bornDate = strtotime($president['BornDate']);
		$bornDate = date('d/m/Y', $bornDate);
		$cpf = Vtx_Util_Formatting::maskFormat($president['Cpf'], '###.###.###-##');
		$position = $this->getPosition();
		$educationLevel = $this->getEducationLevel();
		$cep = Vtx_Util_Formatting::maskFormat($addressPresident['Cep'], '#####-###');
		$state = $this->getState();
		$city = $this->getCity();
		$neighborhood = $this->getNeighborhood();


		return array(
			array(
				'Name' => $president['Name'],
				'BirthDate' => $bornDate,
				'Cpf' => $cpf
			),
			array(
				'Position' => $position,
				'EducationLevel' => $educationLevel,
				'Email' => $president['Email']
			),
			array(
				'Cellphone' => $president['Cellphone'],
				'Phone' => $president['Phone'],
				'Cep' => $cep
			),
			array(
				'StreetNameFull' => $addressPresident['StreetNameFull'],
				'StreetNumber' => $addressPresident['StreetNumber'],
				'StreetCompletion' => $addressPresident['StreetCompletion']
			),
			array(
				'Neighborhood' => $neighborhood,
				'City' => $city,
				'State' => $state
			)
		);
	}

    private function getPosition(){
        return $this->modelPosition->getPosition($this->getPresident()->getPositionId())->getDescription();
    }

    private function getEducationLevel(){
        return $this->modelEducation->getById($this->getPresident()->getEducationId())->getDescription();
    }

    private function getState(){
        return $this->modelState->getStateById($this->getAddressPresident()->getStateId())->getName();
    }

    private function getCity(){
        return $this->modelCity->getCityById($this->getAddressPresident()->getCityId())->getName();
    }

    private function getNeighborhood(){
    	$neighborhoodId = $this->getAddressPresident()->getNeighborhoodId();
    	return $this->modelNeighborhood->getNeighborhoodById($neighborhoodId)->getName();
    }

	protected function getAttributeTranslations(){ // override for Report_Devolutive_CandidateDataPage
		return array(
			'Name' => 'Nome',
			'BirthDate' => 'Data de Nascimento',
			'Cpf' => 'CPF',
			'Position' => 'Cargo',
			'EducationLevel' => 'Nível Educacional',
			'Email' => 'E-mail',
			'Cellphone' => 'Celular',
			'Phone' => 'Telefone Fixo',
			'Cep' => 'CEP',
			'StreetNameFull' => 'Endereço',
			'StreetNumber' => 'Número',
			'StreetCompletion' => 'Complemento',
			'Neighborhood' => 'Bairro',
			'City' => 'Cidade',
			'State' => 'UF'
		);
	}

}