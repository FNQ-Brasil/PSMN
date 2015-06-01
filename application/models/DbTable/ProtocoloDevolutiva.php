<?php

class DbTable_ProtocoloDevolutiva extends Vtx_Db_Table_Abstract
{
    protected $_name = 'ProtocoloDevolutiva';
    protected $_id = 'Id';
    protected $_sequence = true;

    
    /**
     * 
     * @param type $path
     * @return row
     */
    public function getProtocoloByDevolutivePath($path)
    {
        $where = array('DevolutivePath = ?' => $path);
        
        return $this->fetchRow($where, 'Id desc');
    }     
    
    /**
     * 
     * @param type $userId
     * @param type $programaId
     * @return type
     */
    public function getProtocoloByUserId($userId, $programaId)
    {
        $where = array('UserId = ?' => $userId, 'ProgramaId = ?' => $programaId);

        return $this->fetchRow($where, 'Id desc');
    }        
    
} //end class
