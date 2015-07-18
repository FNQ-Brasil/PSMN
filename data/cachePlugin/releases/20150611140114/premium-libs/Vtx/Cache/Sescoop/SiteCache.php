<?php
/**
 * Metodos para cache do site
 *
 * @author esilva
 */
class Vtx_Cache_Sescoop_SiteCache
{
        
    /**
     * @var Zend_Cache_Core
     */
    protected $cache;


    public function __construct() 
    {
        $this->cache = Zend_Registry::get('cache_acl');   
    }
    
    /**
     * metodo chamado em Vtx_Plugin_Permission
     */
    public function fazCacheAcl($sysId = 1)
    {   
        if ($sysId == 1) {
            $nameCache = 'acl';
        } else {
            $nameCache = 'acl' . $sysId;
        }
        
        $acl = $this->cache->load($nameCache);
        if (!$acl) {
            $acl = new Model_Acl(true);
            $this->cache->save($acl, $nameCache);
        }
        
        return $acl;
    }
}

