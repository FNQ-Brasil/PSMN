<?php

class DbTable_ContactUsRecipient extends Vtx_Db_Table_Abstract {
    protected $_name = 'ContactUsRecipient';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getRecipientsByUf($uf){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('CUR' => 'ContactUsRecipient'), null)
            ->joinInner(array('S' => 'State'), 'S.Id = CUR.StateId', null)
            ->joinInner(array('U' => 'User'), 'U.Id = CUR.UserId', array('FirstName','Surname','Email'))
            ->where('S.Uf = ?', $uf)
        ;

        return $this->fetchAll($query);
    }
}

?>