<?php

class DbTable_ExecutionPontuacao extends Vtx_Db_Table_Abstract
{
    protected $_name = 'ExecutionPontuacao';
    protected $_id = 'Id';
    protected $_sequence = true;

    
    public function getPontuacaoByExecutionId($executionId)
    { 
        $query = $this->select()
            ->from(
                array('ep' => 'ExecutionPontuacao')
            )
            ->where('ep.ExecutionId = ?', $executionId)
            ;
        
        $objResult = $this->fetchAll($query);
	
        return $objResult;
                
    }    
    
    
} //end class
