/**
 * Mecanismo de busca e manipulação de estados e capitalCity.
 * Atenção: os ID das cidades são iguais da tabela Sescoop.City e 
 * os IDs dos Estados são iguais da tabela Sescoop.State
 * 
 * @access		public
 * @package     vorttex
 * @subpackage	vorttex.js
 * @author		Marco Cianci (marco.cianci@vorttex.com.br)
 * @copyright	Copyright (c) 2012, Vorttex (http://www.vorttex.com.br)
**/

var capitalCity = [];

capitalCity.push({id: 1,  acronym:'AC', nome:'Acre', 				capitalID : '16', 	capitalNAME : 'Rio Branco'}); 
capitalCity.push({id: 2,  acronym:'AL', nome:'Alagoas', 			capitalID : '109', 	capitalNAME : 'Maceió'});
capitalCity.push({id: 3,  acronym:'AM', nome:'Amazonas', 			capitalID : '243', 	capitalNAME : 'Manaus'});
capitalCity.push({id: 4,  acronym:'AP', nome:'Amapá', 				capitalID : '307', 	capitalNAME : 'Macapá'});
capitalCity.push({id: 5,  acronym:'BA', nome:'Bahia', 				capitalID : '988', 	capitalNAME : 'Salvador'});
capitalCity.push({id: 6,  acronym:'CE', nome:'Ceará', 				capitalID : '1347',	capitalNAME : 'Fortaleza'});
capitalCity.push({id: 7,  acronym:'DF', nome:'Distrito Federal', 	capitalID : '1778',	capitalNAME : 'Brasília'});
capitalCity.push({id: 8,  acronym:'ES', nome:'Espírito Santo', 		capitalID : '2048',	capitalNAME : 'Vitória'});
capitalCity.push({id: 9,  acronym:'GO', nome:'Goiás',				capitalID : '2174',	capitalNAME : 'Goiânia'});
capitalCity.push({id: 10, acronym:'MA', nome:'Maranhão',			capitalID : '2587', capitalNAME : 'São Luís'});
capitalCity.push({id: 11, acronym:'MG', nome:'Minas Gerais', 		capitalID : '2754', capitalNAME : 'Belo Horizonte'});
capitalCity.push({id: 12, acronym:'MS', nome:'Mato Grosso do Sul', 	capitalID : '4141', capitalNAME : 'Campo Grande'});
capitalCity.push({id: 13, acronym:'MT', nome:'Mato Grosso', 		capitalID : '4347', capitalNAME : 'Cuiabá'});
capitalCity.push({id: 14, acronym:'PA', nome:'Pará', 				capitalID : '4565', capitalNAME : 'Belém'});
capitalCity.push({id: 15, acronym:'PB', nome:'Paraíba', 			capitalID : '4964', capitalNAME : 'João Pessoa'});
capitalCity.push({id: 16, acronym:'PE', nome:'Pernambuco', 			capitalID : '5406', capitalNAME : 'Recife'});
capitalCity.push({id: 17, acronym:'PI', nome:'Piauí', 				capitalID : '5721', capitalNAME : 'Teresina'});
capitalCity.push({id: 18, acronym:'PR', nome:'Paraná', 				capitalID : '6015', capitalNAME : 'Curitiba'});
capitalCity.push({id: 19, acronym:'RJ', nome:'Rio de Janeiro', 		capitalID : '7043', capitalNAME : 'Rio de Janeiro'});
capitalCity.push({id: 20, acronym:'RN', nome:'Rio Grande do Norte',	capitalID : '7221', capitalNAME : 'Natal'});
capitalCity.push({id: 21, acronym:'RO', nome:'Rondônia', 			capitalID : '7352', capitalNAME : 'Porto Velho'});
capitalCity.push({id: 22, acronym:'RR', nome:'Roraima', 			capitalID : '7375', capitalNAME : 'Boa Vista'});
capitalCity.push({id: 23, acronym:'RS', nome:'Rio Grande do Sul', 	capitalID : '7994', capitalNAME : 'Porto Alegre'});
capitalCity.push({id: 24, acronym:'SC', nome:'Santa Catarina', 		capitalID : '8452', capitalNAME : 'Florianópolis'});
capitalCity.push({id: 25, acronym:'SE', nome:'Sergipe', 			capitalID : '8770', capitalNAME : 'Aracaju'});
capitalCity.push({id: 26, acronym:'SP', nome:'São Paulo', 			capitalID : '9668', capitalNAME : 'São Paulo'});
capitalCity.push({id: 27, acronym:'TO', nome:'Tocantins', 			capitalID : '9899', capitalNAME : 'Palmas'});
	
var searchCities = function (stateId, type) {
	var returnCapital = '';
	for(i in capitalCity) {
		if(capitalCity[i].id == stateId){
			returnCapital = capitalCity[i];
		}
	}	
	return returnCapital;
};