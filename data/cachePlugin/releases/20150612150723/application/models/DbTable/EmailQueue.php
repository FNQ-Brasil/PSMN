<?php

class DbTable_EmailQueue extends Vtx_Db_Table_Abstract
{
    protected $_name = 'EmailQueue';
    protected $_id = 'Id';
    protected $_sequence = true;
    //protected $_rowClass = 'DbTable_EmailQueueRow';


    
    /**
     * retorna mensagens de email por status na fila.
     * Default retorna mensagens em estado de ESPERAs, prontas para
     * serem disparadas pela rotina backend de disparo de emails.
     * 
     * 
     * @param string $status
     * @return fetchall
     */
    public function getStatusQueue($limit = 100, $status = 'ESPERA')
    {
        
        $query = $this->select()
            ->from(
                array('eq' => 'EmailQueue')
            )
            ->where('eq.StatusQueue = ?', $status)
            ->limit($limit) ;
        
        $objResultCriterion = $this->fetchAll($query);
	
        return $objResultCriterion;
                
    }    
    
} //end class
