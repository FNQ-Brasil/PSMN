<?php

class DbTable_User extends Vtx_Db_Table_Abstract
{
    protected $_name = 'User';
    protected $_id = 'Id';
    protected $_sequence = true;
	protected $_rowClass = 'DbTable_UserRow';

    protected $_referenceMap = array(
        'Education' => array(
          	'columns' => 'EducationId',
            'refTableClass' => 'Education',
            'refColumns' => 'Id'
        ),
        'Position' => array(
          	'columns' => 'PositionId',
            'refTableClass' => 'Position',
            'refColumns' => 'Id'
        ),
        'UserRole' => array(
          	'columns' => 'Id',
            'refTableClass' => 'UserRole',
            'refColumns' => 'UserId'
        )
        
    );
 
    protected $_dependentTables = array(
        'DbTable_Execution',
        'DbTable_Regional',
        'DbTable_UserRole',
        'DbTable_UserLocality'
    );

    
    /**
     * 
     * implementado com base na funcao  getDataFromGestorByStateId do model User (sescoop)
     * e com base na funcao getAll do model Regional (mpe).
     * 
     * @param int $stateId
     * @param string $fetch
     * @return type
     */
    public function getGestorPorEstado($stateId, $fetch = 'all') {

        $modelRegional = new Model_Regional();
               
        $query = $this->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(
                array('U' => 'User'),
                array('Id','FirstName','Surname','Email')
            ) 
            ->join(
                array('UR' => 'User_Role'), 
                'UR.UserId = U.Id AND UR.RoleId = 34 ',
                array('RoleId')
            )
            ->join(
                array('UL' => 'UserLocality'), 
                'UL.UserId = U.Id',
                array('RegionalId')
            )
            ->joinleft(
                array('SA' => 'ServiceArea'), 
                'SA.RegionalId = UL.RegionalId',
                array('StateId')
            )
            ->joinleft(
                array('R' => 'Regional'), 
                'R.Id = UL.RegionalId',
                array('Description')
            )
            ->where('U.Status <> ?', 'I')
            ->where('SA.StateId = ?', $stateId)
            ->orWhere("SA.CityId in (SELECT Id FROM City WHERE StateId in ($stateId))")
            ->orWhere("SA.NeighborhoodId in (SELECT Id FROM Neighborhood WHERE CityId in (SELECT Id FROM City WHERE StateId in ($stateId)))")
            ;
           
        $objResult = $this->fetch($query, $fetch);
        
		return $objResult;
        
    }
    
       
    /**
     * encontra gestores de uma determinada UF
     */    
    public function gestoresDaUfParaReceberemFaleConosco($uf)
    {

        $modelState = new Model_State();
        
        $objState = $modelState->getStateByUf($uf);
        $stateId = $objState->getId();
        //echo "<br>stateId: ".$stateId;
        
        $gestoresFetchAll = $this->getGestorPorEstado($stateId, 'all') ;
        
        //var_dump('gestores',$gestores);
        
        return $gestoresFetchAll;
    } 
    
    
    public function getAllAppraiserByRegionalServiceArea(
        $roleId = null, $regionalId = null, $filter = null, $fetch = 'all', $orderBy = null
    ) {
        $query = $this->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(
                array('U' => 'User'),
                array('Id','FirstName','Surname',
                    'Login','Email','Gender',
                    new Zend_Db_Expr('(SELECT count(1) FROM AppraiserEnterprise as ApE WHERE ApE.UserId = U.Id) as AppraiserToEnterprise'))
            )
            ->join(
                array('UR' => 'User_Role'), 
                'UR.UserId = U.Id',
                array('RoleId')
            )
            ->join(
                array('UL' => 'UserLocality'), 
                'UL.UserId = U.Id',
                array('RegionalId','EnterpriseId')
            )
            ->join(
                array('SAdoAvaliador' => 'ServiceArea'), 
                'SAdoAvaliador.RegionalId = UL.RegionalId',
                null
            )
            ->joinleft(
                array('R' => 'Regional'), 
                'R.Id = UL.RegionalId',
                array('Description')
            );
            if ($regionalId) {
                $query->join(
                    array( 'ServiceAreaRegionalDoGestor' => new Zend_Db_Expr(
                        "(SELECT StateId,CityId,NeighborhoodId FROM ServiceArea WHERE RegionalId = $regionalId)"
                    )),
                        '
                        -- [Estado - Busca Estados pelo StateId]
                        (
                            SAdoAvaliador.StateId in (ServiceAreaRegionalDoGestor.StateId)  
                        -- [Busca Cidades pelo StateId]
                            OR SAdoAvaliador.CityId in (
                                SELECT Id FROM City WHERE StateId in (ServiceAreaRegionalDoGestor.StateId)
                            )
                        -- [Busca Bairros pelo StateId]
                            OR SAdoAvaliador.NeighborhoodId in (
                                SELECT Id FROM Neighborhood WHERE CityId in (
                                   SELECT Id FROM City WHERE StateId in (ServiceAreaRegionalDoGestor.StateId)
                                )
                            )
                        -- [Cidade - Busca Cidades pelo CityId]
                            OR SAdoAvaliador.CityId in (
                                SELECT Id FROM City WHERE Id in (ServiceAreaRegionalDoGestor.CityId)
                            )
                            -- [Busca Bairros pelo CityId]
                            OR SAdoAvaliador.NeighborhoodId in (
                                SELECT Id FROM Neighborhood WHERE CityId in ( ServiceAreaRegionalDoGestor.CityId )
                            )
                        -- [Bairro - Busca bairros pelo NeighborhoodId]
                            OR SAdoAvaliador.NeighborhoodId in (
                                SELECT Id FROM Neighborhood WHERE Id in (ServiceAreaRegionalDoGestor.NeighborhoodId)
                            )
                        )'
                        ,
                    null
                );
            }
            $query->where("UR.RoleId != ?", Zend_Registry::get('config')->acl->roleSupremeAdminId);
            $query->where("UR.RoleId != ?", Zend_Registry::get('config')->acl->roleEnterpriseId);
            if ($roleId) {
                $query->where("UR.RoleId = ?", $roleId);
            }

            if (isset($filter['appraiser_status']) and ($appraiserStatus = $filter['appraiser_status'])) {
                $query->joinLeft('AppraiserUser', 'AppraiserUser.UserId = U.Id', null);
                $extraCondition = ($appraiserStatus == 'unable') ? 'OR AppraiserUser.Status is NULL' : '';
                $query->where("AppraiserUser.Status = ? $extraCondition", $appraiserStatus);
            }

            if (isset($filter['role_id']) and $filter['role_id']) {
                $query->where("UR.RoleId = ?", $filter['role_id']);
            }
            
            if (isset($filter['regional_national']) and $filter['regional_national']) {
                if ($filter['regional_national'] != 'S') {
                    $query->where("R.National = ?", $filter['regional_national']);
                }
            } else {
                $query->where("R.National = ?", 'N');
            }
            
            /*
            if (isset($filter['status']) and $filter['status']) {
            $query->where("U.Status != ?", 'I');
            }
            */
            
            
            if (isset($filter['regional_id']) and $filter['regional_id']) {
                $query->where("UL.RegionalId = ?", $filter['regional_id']);
            }
            
            if (isset($filter['first_name']) and $filter['first_name']) {
                $query->where("CONCAT(U.FirstName,' ',U.Surname) LIKE(?)", '%'.$filter['first_name'].'%');
            }
            /*
            if (isset($filter['surname']) and $filter['surname']) {
                $query->where("U.Surname LIKE (?)", '%'.$filter['surname'].'%');
            }
            */
            if (isset($filter['login']) and $filter['login']) {
                $query->where("U.Login LIKE (?)", '%'.$filter['login'].'%');
            }

            if (isset($filter['cpf']) and $filter['cpf']) {
                $cleanCpf = preg_replace('/[^0-9]/', '', $filter['cpf']);
                $query->where("U.Cpf in ('$cleanCpf','".$filter['cpf']."')");
            }
            if (isset($filter['status']) and $filter['status'] == 'A') {
                $query->where('U.Status = ? OR U.Status = ""', $filter['status']);
            } else if (isset($filter['status']) and $filter['status'] == 'I') {
                $query->where('U.Status = ?', $filter['status']);
            }
            /*
            */
            
            if (!$orderBy) {
                $orderBy = 'U.FirstName ASC';
            }
            $query->order($orderBy);

        
        /*
        echo '<pre>';
        echo $query;
        die;
        
        */
           
        $objResult = $this->fetch($query, $fetch);
		return $objResult;
    }

    public function getAllAppraiser($roleId, $filter = null, $fetch = 'all')
    {
        $config = Zend_Registry::get('config');
        
        $roleId = ($roleId)?$roleId:$config->acl->roleAppraiserId;
        
        $query = $this->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(
                array('U' => 'User'),
                array('Id','FirstName','Surname',
                    'Login','Email','Gender',
                    new Zend_Db_Expr('(SELECT count(1) FROM AppraiserEnterprise as ApE WHERE ApE.UserId = U.Id) as AppraiserToEnterprise'))
            )
            ->join(
                array('UR' => 'User_Role'), 
                'UR.UserId = U.Id',
                array('RoleId')
            )
            ->join(
                array('UL' => 'UserLocality'), 
                'UL.UserId = U.Id',
                array('RegionalId')
            )
            ->join(
                array('SAdoAvaliador' => 'ServiceArea'), 
                'SAdoAvaliador.RegionalId = UL.RegionalId',
                null
            )
            ->joinleft(
                array('R' => 'Regional'), 
                'R.Id = UL.RegionalId',
                array('Description')
            )
            ->where("UR.RoleId = ?", $roleId);
        $query->where("UR.RoleId != ?", $config->acl->roleSupremeAdminId);
        $query->where("UR.RoleId != ?", $config->acl->roleEnterpriseId);
        if ($filter['regional_id']) {
            $query->where("R.Id = ?", $filter['regional_id']);
        }
        $query->order('U.FirstName ASC');
       /*
        echo '<pre>';
        echo $query; 
        die;
         
        */
        $objResult = $this->fetch($query, $fetch);
		return $objResult;
    }
    
    
    
    
    /**
     * 
     * retorna fetchAll da table Enterprise
     * 
     * uso para script de execucao em massa para atualizacao de usuarios
     * 
     */
    public function getUserByLimitAndIdMaior($Id_EnterpriseCategoryAwardCompetition, $limit, $competitionId = 2013)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ep' => 'EnterpriseCategoryAwardCompetition'), array('EnterpriseProgramaId' => 'Id')
            )
            ->join(
                array('ul' => 'UserLocality'), 'ep.EnterpriseId = ul.EnterpriseId',
                null
            )
            ->join(
                array('u' => 'User'), 'ul.UserId = u.Id', array('UserId' => 'u.Id', 'u.FirstName')        
            )
            ->join(
                array('e' => 'Enterprise'), 'ep.EnterpriseId = e.Id', array('e.SocialName')       
            )    
            ->where('ep.Id >= ?', $Id_EnterpriseCategoryAwardCompetition)
            ->where('ep.CompetitionId = ?', $competitionId)
            ->limit($limit)        
        ;
        echo $query."\n\n";
        $objResult = $this->fetchAll($query);
        
		return $objResult;
    }
    
    /**
     * 
     * retorna fetchAll da table Enterprise
     * 
     * Uso para script de execucao em massa para atualizacao de usuarios
     * 
     * faz join com Execution
     */
    public function getUserByLimitAndIdMaiorJoinExecution($Id_EnterpriseCategoryAwardCompetition, $limit, $competitionId = 2014)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ep' => 'EnterpriseCategoryAwardCompetition'), array('EnterpriseProgramaId' => 'Id')
            )
            ->join(
                array('ul' => 'UserLocality'), 'ep.EnterpriseId = ul.EnterpriseId',
                null
            )
            ->join(
                array('u' => 'User'), 'ul.UserId = u.Id', array('UserId' => 'u.Id', 'u.FirstName')        
            )
            ->join(
                array('e' => 'Enterprise'), 'ep.EnterpriseId = e.Id', array('e.SocialName')       
            )   
            ->join(
                array('x' => 'Execution'), 'x.UserId = ul.UserId and x.QuestionnaireId = 51'       
            )     
            ->joinLeft(
                array('EP' => 'ExecutionPontuacao'), 'EP.ExecutionId = x.Id'   , null    
            )  
            ->where('ep.Id >= ?', $Id_EnterpriseCategoryAwardCompetition)
            ->where('ep.CompetitionId = ?', $competitionId)
            ->where("e.Status = ('A')")
            ->where('x.DevolutivePath is not null')
            ->where('EP.NegociosTotal is null')
            ->limit($limit)        
        ;
        echo $query."\n\n";
        $objResult = $this->fetchAll($query);
        
		return $objResult;
    }

    public function get($userId){
        return $this->fetchRow(array('Id = ?' => $userId));
    }
    
}

