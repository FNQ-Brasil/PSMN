<?php

class DbTable_ServiceAreaCache extends Vtx_Db_Table_Abstract {
    protected $_name = 'ServiceAreaCache';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function createCacheFromViewData(){
        $deleteSql = 'DELETE FROM ServiceAreaCache';

        $insertSql = '
          INSERT INTO ServiceAreaCache (RegionalId, StateId, CityId, NeighborhoodId)
          SELECT RegionalId, StateId, CityId, NeighborhoodId
          FROM vw_ServiceArea;
        ';

        $this->execute($deleteSql);
        $this->execute($insertSql);
    }

    private function execute($sql){
        $this->getDefaultAdapter()->query($sql);
    }
}