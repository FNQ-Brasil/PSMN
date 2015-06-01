<?php
/**
 * 
 * Model_Metier
 * @uses  
 * @author mcianci
 *
 */
class Model_Metier
{
    public $dbTable_Metier = "";
    
    function __construct() {
        $this->dbTable_Metier = new DbTable_Metier();
    }
   
    public function getAll()
    {
        $cache = Zend_Registry::get('cache_FS');
        $nameCache = 'metier';
        $dataCached = $cache->load($nameCache);

        if (!$dataCached) {               
            $dataCached = $this->dbTable_Metier->fetchAll();
            $cache->save($dataCached, $nameCache);
        }
        return $dataCached;
    }
    
}