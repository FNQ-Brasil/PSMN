<?php
/**
 * 
 * Model_Regiao
 * @uses  
 *
 */
class Model_Regiao
{
    public $dbTable_Regiao = "";
    
    function __construct() {
        $this->dbTable_Regiao = new DbTable_Regiao();
    }

    function getRegiaoByUf($uf)
    {
        return $this->dbTable_Regiao->fetchRow(array('Uf = ?' => $uf));
    }

    public function getAll($where=null,$order=null,$count=null,$offset=null,$filter=null)
    {
        $dataCached = false;
        $returnAllNoFilter = (!$where and !$order and !$count and !$offset and !$filter)? true : false;
        
        if ($returnAllNoFilter) {
            $cache = Zend_Registry::get('cache_FS');
            $nameCache = 'regiaosAll';
            $dataCached = $cache->load($nameCache);
            if ($dataCached) {
                return $dataCached;
            };
        }
        
        $query = $this->dbTable_Regiao->select()
                ->setIntegrityCheck(false)
                ->distinct()
                ->from(
                    array('R' => 'Regiao'),
                    array('Id', 'Nome','Descricao')
        );
        if (isset($filter['Nome']) and $filter['Nome']) {
            $query->where('R.Nome LIKE (?)', '%'.$filter['Nome'].'%');
        }
        $query->order('Nome ASC');

        $dataCached = $this->dbTable_Regiao->fetchAll($query);
        
        if ($returnAllNoFilter) {
            $cache->save($dataCached, $nameCache);
        }
        return $dataCached;
    }
    

	

}