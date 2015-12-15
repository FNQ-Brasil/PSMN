<?php
class DbTable_ServiceArea extends Vtx_Db_Table_Abstract
{
    protected $_name = 'ServiceArea';
    
    protected $_referenceMap = array(
	'Regional' => array(
            'columns' => array('RegionalId'),
            'refTableClass' => 'Regional',
            'refColumns' => array('Id')
        ),
        'State' => array(
            'columns' => array('StateId'),
            'refTableClass' => 'State',
            'refColumns' => array('Id'),
        ),
        'City' => array(
            'columns' => array('CityId'),
            'refTableClass' => 'City',
            'refColumns' => array('Id'),
        ),
        'Neighborhood' => array(
            'columns' => array('NeighborhoodId'),
            'refTableClass' => 'Neighborhood',
            'refColumns' => array('Id'),
        )
    );
    
    public function getAllServiceAreaByRegional(
        $regionalId , $fetch = 'all', $filter = null
    ) {
        $query = $this->select()
            ->distinct()
            ->setIntegrityCheck(false)
            
            ->from(
                array('SA' => 'ServiceArea'),
                array('Id','RegionalId','StateId','CityId','NeighborhoodId')
            );
                
            if ($filter['regional_id']) {
                $query->joinleft(
                    array( 'ServiceAreaRegionalDoGestor' => new Zend_Db_Expr(
                        "(SELECT StateId,CityId,NeighborhoodId FROM ServiceArea WHERE RegionalId = ".$filter['regional_id'].")"
                    )),
                        '
                        -- [Estado Busca Estados pelo StateId]
                        (
                            SA.StateId in (ServiceAreaRegionalDoGestor.StateId)  
                        -- Busca Cidades pelo StateId
                            OR SA.CityId in (
                                SELECT Id FROM City WHERE StateId in (ServiceAreaRegionalDoGestor.StateId)
                            )
                            -- Busca Bairros pelo StateId
                            OR SA.NeighborhoodId in (
                                SELECT Id FROM Neighborhood WHERE CityId in (
                                   SELECT Id FROM City WHERE StateId in (ServiceAreaRegionalDoGestor.StateId)
                                )
                            )
                        -- [Cidade Busca Cidades pelo CityId]
                            OR SA.CityId in (
                                SELECT Id FROM City WHERE Id in (ServiceAreaRegionalDoGestor.CityId)
                            )
                            -- [Busca Bairros pelo CityId]
                            OR SA.NeighborhoodId in (
                                SELECT Id FROM Neighborhood WHERE CityId in ( ServiceAreaRegionalDoGestor.CityId )
                            )
                        -- [Bairro Busca bairros pelo NeighborhoodId]
                            OR SA.NeighborhoodId in (
                                SELECT Id FROM Neighborhood WHERE Id in (ServiceAreaRegionalDoGestor.NeighborhoodId)
                            )
                        )'
                        ,
                    null //array('Id','RegionalId','StateId','CityId','NeighborhoodId')
                );
            }
            
            $query->where("SA.RegionalId = ?", $regionalId);
            
            if ($filter['regional_id']) {
                $query->where("SA.RegionalId = ?", $filter['regional_id']);
            }
            
        
            #$query->order('R.Description ASC');
        
        echo '<pre>';
        echo $query; 
        die;
        /*
        
        
        */
           
        $objResult = $this->fetch($query, $fetch);
		return $objResult;
    }
}


