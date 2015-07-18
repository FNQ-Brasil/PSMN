<?php

class DbTable_Regiao extends Vtx_Db_Table_Abstract {

    protected $_name = 'Regiao';
    protected $_id = 'Id';
    protected $_sequence = true;
    protected $_dependentTables = array(
        'Regional'
    );

    public function getById($Id) {
        $query = $this->select()
                ->setIntegrityCheck(false)
                ->from(
                        array('Reg' => $this->_name), array('Nome', 'Descricao')
                )
                ->where('Reg.Id = ?', $Id);
        return $this->fetchRow($query);
    }

}
