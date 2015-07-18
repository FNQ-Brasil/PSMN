<?php

/**
 * Recupera cadastro da empresa
 *
 * @author vtx
 */
class Vtx_Devolutive_Tipo_MPE_getEnterpriseData {
    
    /**
     *
     * @var Model_Enterprise
     */
    protected $Enterprise;
    
    /**
     *
     * @var Model_Position
     */
    protected $Position;

    /**
     * dados da empresa
     * 
     * @param type $userId
     * @return type
     */
    public function getEnterpriseData($userId)
    {
        
        $User = new Model_User();
        //$President = new Model_President();
        $Enterprise = new Model_Enterprise();
        $UserLocality = new Model_UserLocality();
        $AddressEnterprise = new Model_AddressEnterprise();
        
        $Position = new Model_Position();
        
        $userRow = $User->getUserById($userId);
        $userLocalityRow = $UserLocality->getUserLocalityByUserId($userRow->getId());
        $enterpriseRow = $Enterprise->getEnterpriseById($userLocalityRow->getEnterpriseId());
        
        //var_dump('EnterpriseId: ',$enterpriseRow->getId());
        
        //$presidentRow = $President->getPresidentByEnterpriseId($enterpriseRow->getId());
        $addressEnterpriseRow = $AddressEnterprise->getAddressEnterpriseByEnterpriseId($enterpriseRow->getId());
        
        $arrAnnualRevenue = Vtx_Util_Array::annualRevenue();
        
        $arrCategoria = Vtx_Util_Array::categoriaMpe();
        $idCategoria = $enterpriseRow->getCategoriaId();
        
        $nomeCategoria = "";
        if (is_int($idCategoria)) {
           $nomeCategoria = $arrCategoria[$idCategoria];
        }
        
        $arrTipoEmpresa = Vtx_Util_Array::tipoEmpresaMPE();
        $idTipoEmpresa = $enterpriseRow->getEnterpriseTypeId();
        
        $tipoEmpresa = "";
        if (is_int($idTipoEmpresa)) {
           $tipoEmpresa = $arrTipoEmpresa[$idTipoEmpresa];
        }
        
       // var_dump('addressEnterpriseRow: ',$addressEnterpriseRow); 
       // exit;
        
        $neighborhood = $addressEnterpriseRow->findParentNeighborhood();
        //var_dump('neighborhood: ',$neighborhood);
        
        $idUf = $addressEnterpriseRow->getStateId();
        $idCidade = $addressEnterpriseRow->getCityId();
        
        //var_dump($idCidade);
        //var_dump($idUf);

        if ( is_int($idUf) ) {            
            $uf = DbTable_State::getInstance()->getById($idUf)->getUf();
        } else {
            $uf = "";
        }
        if ( is_int($idCidade) ) {
            $cidade = DbTable_City::getInstance()->getById($idCidade)->getName() ;
        } else {
            $cidade = "";
        }

        $cidadeEstado = $cidade."/".$uf;
        
        //var_dump ($cidadeEstado); exit;

        //var_dump($cidadeEstado);
        //exit;
        
        $cnpj = $enterpriseRow->getCnpj();
        if (is_string($cnpj)) {
            $cpfCnpj = $cnpj;
        } else {
            $cpfCnpj = "";
        }

        //$cnpjMask = Vtx_Util_Formatting::maskFormat($getcnpj,'##.###.###/####-##');        
        
        //$cnpj = null;
        //var_dump($cnpj);
        //exit;
        
        $arrEnterprise = array(
           // 'Registro OCB'              => (($enterpriseRow->getOcbRegister() != '') ? 
            //    $enterpriseRow->getOcbRegister() : ''),
            'Razão Social'              => (($enterpriseRow->getSocialName() != '') ? 
                $enterpriseRow->getSocialName() : ''),
            'E-mail'                    => (($enterpriseRow->getEmailDefault() != '') ? 
                $enterpriseRow->getEmailDefault() : ''),
            'Nome Fantasia'             => (($enterpriseRow->getFantasyName() != '') ? 
                $enterpriseRow->getFantasyName() : ''),
            //'Ramo de Atividade'         => (($enterpriseRow->getMetierId()) ? 
            //    DbTable_Metier::getInstance()->getById($enterpriseRow->getMetierId())->getDescription() : ''),
            'Atividade Econômica(CNAE)' => (($enterpriseRow->getCnae() != '') ? 
                $enterpriseRow->getCnae() : ''),
            'CPF/CNPJ'                  => ($cpfCnpj),
            'Faturamento Anual'         => (($enterpriseRow->getAnnualRevenue() != '' && isset($arrAnnualRevenue[$enterpriseRow->getAnnualRevenue()])) ? 
                $arrAnnualRevenue[$enterpriseRow->getAnnualRevenue()] : ''),
            'Número de Colaboradores'   => (($enterpriseRow->getEmployeesQuantity() != '') ? 
                $enterpriseRow->getEmployeesQuantity() : '0'),
            'Data de Abertura'          => (($enterpriseRow->getCreationDate() != '') ? 
                Vtx_Util_Date::format_dma($enterpriseRow->getCreationDate()) : ''),
            'Endereço'                  => (is_object($addressEnterpriseRow) ? 
                $addressEnterpriseRow->getStreetNameFull() : ''),
            'Número'                    => (is_object($addressEnterpriseRow) ? 
                $addressEnterpriseRow->getStreetNumber() : ''),
            'Complemento'               => (is_object($addressEnterpriseRow) ? 
                $addressEnterpriseRow->getStreetCompletion() : ''),
            'Bairro'                    => ($neighborhood? 
                $addressEnterpriseRow->findParentNeighborhood()->getName() : ''),
            'Cidade/Estado'             => ($cidadeEstado),
            'CEP'                       => (is_object($addressEnterpriseRow) ? 
                Vtx_Util_Formatting::maskFormat($addressEnterpriseRow->getCep(),'#####-###') : ''),
            'Categoria'                 => $nomeCategoria ,      
            'Tipo Empresa'              => $tipoEmpresa,
        );
        
        $idPosition = $userRow->getPositionId();
        $cargo = "";
        if (is_int($idPosition)) {
           $posicao = $Position->getPosition($idPosition);
           $cargo = $posicao? $posicao->getDescription() : '';
        }
        
        $arrContact = array(
            'Nome'          => (is_object($userRow) ? $userRow->getNomeCompleto() : ''),
            'Cargo'         => (is_object($userRow) ? $cargo : ''),
            'Telefone'      => (is_object($userRow) ? $userRow->getTelefone() : ''),
            'Celular'      => (is_object($userRow) ? $userRow->getCelular() : ''),
            'E-mail'        => (is_object($userRow) ? $userRow->getEmail() : '')
        );
            
        $arrIssues = array(
           // '0' => array('Q' => '1. É uma Matriz?', 'R' => (($enterpriseRow->getHeadOfficeStatus() == '1') ? 'Sim' : 'Não')),
            //'1' => array('Q' => '2. É uma Singular?', 'R' => (($enterpriseRow->getSingularStatus() == '1') ? 'Sim' : 'Não')),
            //'2' => array('Q' => '3. A cooperativa está vinculada a alguma Central?', 'R' => (($enterpriseRow->getCentralName() != '') ? $enterpriseRow->getCentralName() : 'Não')),
            //'3' => array('Q' => '4. A cooperativa está vinculada a alguma Federação?', 'R' => (($enterpriseRow->getFederationName() != '') ? $enterpriseRow->getFederationName() : 'Não')),
            //'4' => array('Q' => '5. A cooperativa está vinculada a alguma Confederação?', 'R' => (($enterpriseRow->getConfederationName() != '') ? $enterpriseRow->getConfederationName() : 'Não'))
        );
            
        return array($arrEnterprise, $arrContact, $arrIssues);
    }
}

?>
