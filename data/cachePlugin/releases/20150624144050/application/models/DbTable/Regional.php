<?php
class DbTable_Regional extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Regional';
    protected $_primary = 'Id';
   
    public function getAllRegionalByOneRegionalServiceArea(
        $roleId = null, $userLoggedRegionalId, $fetch = 'all', $filter = null, $orderBy = null
    ) {
        $query = $this->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(
                array('R' => 'Regional'),array('Id','Description')
            )
            ->joinleft(
                array('UL' => 'UserLocality'), 'UL.RegionalId = R.Id', array('RegionalId','EnterpriseId')
            )
            ->joinleft(
                array('U' => 'User'), 'U.Id = UL.UserId', null // array('RoleId')
            )
            ->joinleft(
                array('UR' => 'User_Role'), 'UR.UserId = U.Id', null // array('RoleId')
            )
            ->join(
                array('SAdoAvaliador' => 'ServiceArea'), 'SAdoAvaliador.RegionalId = R.Id', null
            );
        
        $filtroStateId ='';
        if (isset($filter['state_id']) && $filter['state_id']) {
            $filtroStateId = 'AND StateId = '.$filter['state_id'];
        }
        
        $query->join(
                array( 'ServiceAreaRegionalDoGestor' => new Zend_Db_Expr(
                    "(SELECT StateId,CityId,NeighborhoodId FROM ServiceArea WHERE RegionalId = $userLoggedRegionalId $filtroStateId)"
                )), 
                    '
					-- [Estado Busca Estados pelo StateId]
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
					-- [Cidade  Busca Cidades pelo CityId]
                        OR SAdoAvaliador.CityId in (
                            SELECT Id FROM City WHERE Id in (ServiceAreaRegionalDoGestor.CityId)
                        )
                        -- Busca Bairros pelo CityId
                        OR SAdoAvaliador.NeighborhoodId in (
                            SELECT Id FROM Neighborhood WHERE CityId in ( ServiceAreaRegionalDoGestor.CityId )
                        )
					-- Bairro[ Busca bairros pelo NeighborhoodId]
                        OR SAdoAvaliador.NeighborhoodId in (
                            SELECT Id FROM Neighborhood WHERE Id in (ServiceAreaRegionalDoGestor.NeighborhoodId)
                        )
                    )',
                null
            );
            if ($roleId) {
                $query->where("UR.RoleId = ?", $roleId);
            }
            if (isset($filter['regional_national']) and $filter['regional_national']) {
                if ($filter['regional_national'] != 'S') {
                    $query->where("R.National = ?", $filter['regional_national']);
                }
            } else {
                $query->where("R.National = ?", 'N');
            }
            if (isset($filter['regional']) && $filter['regional']) {
                $query->where('R.Description LIKE (?)', '%'.$filter['regional'].'%');
            }
            if (isset($filter['regional_not']) && $filter['regional_not']) {
                $query->where('R.Id != ?', $filter['regional_not']);
            }
            if (!$orderBy) {
                $orderBy = 'R.Description ASC';
            }
            $query->order($orderBy);
            /*
            echo '<pre>';
            echo $query; 
            die;
            
            */
        $objResult = $this->fetch($query, $fetch);
		return $objResult;
        /*
        ->from(
            array('U' => 'User'),
            null //array('Id','FirstName','Surname','Login','Email','Gender')
        )
        */
        /*
        ->joinleft(
            array('R' => 'Regional'), 
            'R.Id = UL.RegionalId',
            array('Id','Description')
        )
        */
        /*
        '(
            SAdoAvaliador.StateId in (ServiceAreaRegionalDoGestor.StateId)  
            OR SAdoAvaliador.CityId in (
                SELECT Id FROM City WHERE StateId in (ServiceAreaRegionalDoGestor.StateId)
            )
            OR SAdoAvaliador.NeighborhoodId in (
                SELECT Id FROM Neighborhood WHERE Id in (ServiceAreaRegionalDoGestor.NeighborhoodId)
            )
          )'
        */
    }
    
}