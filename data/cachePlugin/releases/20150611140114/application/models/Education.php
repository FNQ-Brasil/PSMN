<?php
/**
 * 
 * Model_Education
 * @uses  
 * @author mcianci
 *
 */
class Model_Education
{
    public $dbTable_Education = "";
    
    function __construct() {
        $this->dbTable_Education = new DbTable_Education();
    }
   
    /*
    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_Education->fetchAll($where, $order, $count, $offset);
    }*/
    public function getAll($where = null, $fetch = 'all')
    {
        $dataCached = false;
        $returnAllNoFilter = (!$where)? true : false;
        
        if ($returnAllNoFilter) {
            $cache = Zend_Registry::get('cache_FS');
            $nameCache = 'education'.$fetch;
            $dataCached = $cache->load($nameCache);
            if ($dataCached) {
                return $dataCached;
            };
        }
         
        $dataCached = $this->dbTable_Education->fetch( ($where) ?
            $where : $this->dbTable_Education->select() , $fetch
        );

        if ($returnAllNoFilter) {
            $cache->save($dataCached, $nameCache);
        }
        return $dataCached;
    }

    public function getById($id){
        return $this->dbTable_Education->fetchRow(array('Id = ?' => $id));
    }
}