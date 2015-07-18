<?php

class Vtx_Util_Array
{
	public static function gender($genderSelect = null)
	{
        $gender = array(
            'M' => 'Masculino',
            'F' => 'Feminino',
        );
        
        if ($genderSelect) {
            return $gender[$genderSelect];
        }
        
		return $gender;
	}
    
    public static function eligibility()
    {
        $eligibility = array( 
            '1' => 'Elegível', 
            '0' => 'Não Elegível', 
            '2' => 'Aguardando análise', 
            '' => ''
        );
        return $eligibility;
    }
    
	public static function status()
	{
        $status = array(
            'A' => 'Ativo',
            'I' => 'Inativo',
        );
		return $status;
	}
    
	public static function faixaIdadePSMN($idFaixa = null)
	{
        $faixas = array(
            1 => array('Menos de 25', 0, 24),
            2 => array('Entre 25 e 29', 25, 29),
            3 => array('Entre 30 e 34', 30, 34),
            4 => array('Entre 35 e 39', 35, 39),
            5 => array('Entre 40 e 44', 40, 44),
            6 => array('Entre 45 e 49', 45, 49),
            7 => array('Acima de 50', 50, 120),
        );
        return $idFaixa? $faixas[$idFaixa] : $faixas;
	}
    
	public static function fases()
	{
        $fases = array(
            '1' => 'Fase 1',
            '2' => 'Fase 2',
            '3' => 'Fase 3',
            '4' => 'Fase 4',
            '5' => 'Fase 5',
            '6' => 'Classificadas',
        );
		return $fases;
	}
    
	public static function programasMpe($programa)
	{
        
        switch ($programa) {
            case 'MpeBrasil':
                $anos = array(
                    '2013' => '2013',
                    '2012' => '2012',
                );
                break;

            case 'MpeDiagnostico':
                $anos = array(
                    '20132' => '2013',
                    //'20122' => '2012',
                );
                break;
            
            case 'SebraeMais':
                $anos = array(
                    '20133' => '2013',
                    //'20123' => '2012',
                );
                break;
            
            default:
                break;
        }
		return $anos;
	}
    
    public static function annualRevenue($id = null)
	{
        $annualRevenue = array(
            '1' => 'Até R$ 360.000',
            '2' => 'Entre R$ 360.000 e R$ 2.400.000',
            '3' => 'Entre R$ 2.400.000 e R$ 3.600.000',
            '4' => 'Entre R$ 3.600.000 e R$ 48.000.000',
            '5' => 'Entre R$ 48.000.000 e R$ 300.000.000',
            '6' => 'Entre R$ 300.000.000 e R$ 1.000.000.000',
            '7' => 'Acima de R$ 1.000.000.000'
        );
        
        if ($id) {
            return $annualRevenue[$id];
        }
        
		return $annualRevenue;
    }

    public static function howFindUsPsmn()
	{
        $howFindUsPsmn = array(
            '1' => 'SEBRAE',
            '2' => 'BPW',
            '3' => 'SPM',
            '4' => 'Revista',
            '5' => 'Recebi uma ligação',
            '6' => 'Jornal',
            '7' => 'Rádio',
            '8' => 'TV',
            '9' => 'Internet',
            '10' => 'Rede Social',
            '11' => 'Placa/faixa/outdoor/busdoor ou similar',
            '12' => 'Parceiros locais',
            '13' => 'Outro meio',
        );
		return $howFindUsPsmn;
    }
    
    public static function annualRevenuePsmn($id = null)
	{
        $annualRevenue = array(
            '1' => 'até R$ 60.000 - Microempreendedor Individual',
            '2' => 'até R$ 360.000 - Microempresa',
            '3' => 'de R$ 360.000 a R$ 3.600.000 - Empresa de Pequeno Porte',
        );

        if($id){
            return $annualRevenue[$id];
        }

		return $annualRevenue;
    }
    
    public static function faturamentoAnualMpe()
	{
        $annualRevenue = array(
            '1' => 'até R$ 60.000 - Microempreendedor Individual',
            '2' => 'até R$ 360.000 - Microempresa',
            '3' => 'R$ 360.000 a R$ 3.600.000 - Empresa de Pequeno Porte',
            '4' => 'acima de R$ 3.600.000 - Médias e Grandes Empresas'
        );
		return $annualRevenue;
    }
    
    /**
     * Utilizada no PSMN
     * @return string
     */
    public static function categorySector($id = null)
	{
        $categorySector = array(
            '1' => 'Agronegócio',
            //'2' => 'Artesanato',
            '3' => 'Comércio',
            '4' => 'Indústria',
            '5' => 'Serviços'
        );

        if($id){
            return $categorySector[$id];
        }

		return $categorySector;
    }
    
    /**
     * Utilizada no PSMN
     * @return string
     */
    public static function categoryAward()
	{
        $categorySector = array(
            '1' => 'Pequenos Negócios',
            '2' => 'Produtora Rural',
            '3' => 'Microempreendedora Individual'
        );
		return $categorySector;
    }
    
    /**
     * Retorna nome descritivo da Categoria - MPE
     * 
     * @param type $idCategoria
     * @return type
     */
    public static function categoriaStringMpe($idCategoria=0)
    {
        $arrCat = self::categoriaMpe();
        
        $stringCategoria = "";
        
        if ($idCategoria > 0) {
            $stringCategoria = $arrCat[$idCategoria];
        }
        
        return $stringCategoria;
    }
    
    /**
     * Utilizada no MPE
     * @return string
     */
    public static function categoriaMpe()
	{
        $categoriaMpe = array(
            '1' => 'Agronegócio',
            '3' => 'Comércio',
            '4' => 'Indústria',
            '2' => 'Serviços de Educação',
            '6' => 'Serviços de Saúde',
            '7' => 'Serviços de Tecnologia da Informação',
            '8' => 'Serviços de Turismo',
            '5' => 'Serviços'
        );
		return $categoriaMpe;
    }
    
    public static function categoriaPsmn($idPremio = null)
	{
        $categoria = array(
            '1' => 'Pequenos Negócios',
            '2' => 'Produtora Rural',
            '3' => 'Microempreendedora Individual'
        );
		return $idPremio? $categoria[$idPremio] : $categoria;
    }
    
    /**
     * MPE EnterpriseType
     */
    public static function tipoEmpresaMPE()
    {
        return array (
          1 => 'Empresa com CNPJ',
          2 => 'Produtor Rural'
        );
    }


    /*
     * in_array — Checks if a value exists in an array
     */
    public static function array_all_in_array($needles, $haystack) {
        foreach ($needles as $needle) {
            if ( ! in_array($needle, $haystack) ) {
                return false;
            }
        }
        return true;
    }
    
	public static function participantes()
	{
        $status = array(
            '3' => 'Inscritas',
            '2' => 'Candidatas',
        );
		return $status;
	}
    
    public static function pontuacaoMaximaCriteriosGestao($criterioNumero = null)
    {
        $pontuacoes = array(
            1 => '16,0', 
            2 => '9,0',
            3 => '9,0', 
            4 => '6,0',
            5 => '6,0', 
            6 => '9,0', 
            7 => '15,0', 
            8 => '30,0'
        );
        return $criterioNumero? $pontuacoes[$criterioNumero] : $pontuacoes;
    }      
    
    public static function ciclosSescoop($programaId) {
        
        switch ($programaId) {
            case 131 : 
                $ciclo = 'PDGC 2013';
                break;
            case '132'  : 
                $ciclo = 'Prêmio 2013';
                break;
            case 141  :
                $ciclo = 'PDGC 2014';
                break;
            case '142'   :
                $ciclo = 'Prêmio 2014';
                break;
            default:
                $ciclo = '';
                break;
        }
    return $ciclo;
    }
    
}