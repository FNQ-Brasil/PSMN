<?php

class DbTable_ResourceRow extends Vtx_Db_Table_Row_Abstract
{
    public function getResourceName()
    {
        return $this->getModule() . ':' . $this->getDescription();
    }
    /**
     * Retorna o nomes dos privilpegios não padrão
     * @return type
     */
	public function getOtherPrivileges()
    {
//        $query = $this->getTable()->select()
//            ->setIntegrityCheck(false)
//            ->from(
//                array('Role_Resource_Privilege'),
//                array('Privilege')
//            )
//            ->distinct()
//            ->where('ResourceId = ?', $this->getId())
//            ->where('Privilege not in (?)', Model_Acl::$DEFAULT_PRIVILEGES)
//            ->order('Privilege');

        
        $query = $this->getTable()->select()
            ->setIntegrityCheck(false)
            ->from(
                array('rrp'=>'Role_Resource_Privilege'),
                array('Privilege')
            )
            ->joinLeft(
                    array('pld'=>'PrivilegeLongDescription'),
                    'rrp.ResourceId = pld.ResourceId AND rrp.Privilege=pld.Privilege',
                    array('LongDescription')
                    )
            ->distinct()
            ->where('rrp.ResourceId = ?', $this->getId())
            ->where('rrp.Privilege not in (?)', Model_Acl::$DEFAULT_PRIVILEGES)
            ->order('rrp.Privilege');        
        
        return $this->getTable()->fetchAll($query);
	}
}