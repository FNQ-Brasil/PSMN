<?php

class DbTable_EnterpreneurFeatures extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EnterpreneurFeatures';
    protected $_id = 'Id';
    protected $_sequence = true;
   
    public function getId($limit = 100, $Id)
    {
        
        $query = $this->select()
            ->from(
                array('ef' => 'EnterpreneurFeatures')
            )
            ->where('ef.Id = ?', $Id)
            ->limit($limit) ;
        
        $objResult = $this->fetchAll($query);
	
        return $objResult;
                
    }    
    
} //end class
