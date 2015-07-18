<?php

/**
 * Classe responsavel pela camada de CACHE da aplicacao
 * 
 * @author esilva, vtx
 */
class Vtx_Cache_Sescoop_BootstrapCache {

    /**
     * host para instancia do server Memcached
     * 
     * @var string
     */
    protected $hostMemcache;
    
    /**
     * configuracoes para cache ACL
     */
    protected $tipoCacheAcl; //Libmemcached, File, None

    protected $lifetimeAcl;  
    
    protected $pathCacheAcl;
    
    /**
     * configuracoes para cache FS
     */
    protected $tipoCacheFS; //Libmemcached, File, None

    protected $lifetimeFS;  
    
    protected $pathCacheFS;
    
    public function __construct() {}
    
    /**
     * 
     * desgin pattern Command
     */
    public static function execute()
    {
        //instancia cache: /premium-libs
        $camadaCache = new Vtx_Cache_Sescoop_BootstrapCache();

        //recupera configuracao do Cache no config.ini
        $camadaCache->setHostMemcache(Zend_Registry::get('config')->cache->host->memcached);
        
        $camadaCache->setTipoCacheAcl(Zend_Registry::get('config')->cache->Acl->tipo);
        $camadaCache->setLifetimeAcl(Zend_Registry::get('config')->cache->Acl->lifetime);
        $camadaCache->setPathCacheAcl(Zend_Registry::get('config')->cache->Acl->pathcache);
        
        $camadaCache->setTipoCacheFS(Zend_Registry::get('config')->cache->FS->tipo);
        $camadaCache->setLifetimeFS(Zend_Registry::get('config')->cache->FS->lifetime);
        $camadaCache->setPathCacheFS(Zend_Registry::get('config')->cache->FS->pathcache);
                
        $camadaCache->cacheAcl();
        $camadaCache->cacheFS();        
    }
    
    /**
     * 
     * @return array
     */
    private function configApp()
    {        
        $hostMemcache = $this->getHostMemcache();
        
        //dir do filesystem
        $path_cache = $this->getPathCacheFS();

        switch ($this->getTipoCacheFS())
        {
            case 'File':
                  $appfrontendFS = array(
                                'lifetime' => $this->getLifetimeFS(), //seconds 
                                'automatic_serialization' => true,
                                //'debug_header' => true,
                                );
                  $appbackendFS = array( 
                                'cache_dir' => $path_cache
                                     
                                );
                  
                  break;
            case 'Libmemcached':  
            case 'Memcached':
                  $appfrontendFS = array(
                                  'lifetime' => $this->getLifetimeFS(), //seconds 
                                  'automatic_serialization' => true,
                                  'caching' => true,
                                  );
                
                  $appbackendFS = array(
                                 'servers' => array(
                                                array(
                                                'host'   => $hostMemcache,
                                                'port'   => 11211,
                                                'weight' => 1
                                                )
                                             ),
                                 'compression' => false
                                 );                
                
                  break;
                            
        } //end switch
        
        //diretorio onde sera cacheado
        $path_cache = $this->getPathCacheAcl();
        
        switch ($this->getTipoCacheAcl())
        {
            case 'File':
                    $appfrontendAcl = array('lifetime' => $this->getLifetimeAcl(), 
                             'automatic_serialization' => true
                             );
        
                    $appbackendAcl = array('cache_dir' => $path_cache);
                  
                  break;
            case 'Libmemcached':  
            case 'Memcached':
                   $appfrontendAcl = array(
                                  'lifetime' => $this->getLifetimeAcl(), //seconds 
                                  'automatic_serialization' => true,
                                  'caching' => true,
                                  );
                
                    $appbackendAcl = array(
                                 'servers' => array(
                                                array(
                                                'host'   => $hostMemcache,
                                                'port'   => 11211,
                                                'weight' => 1
                                                )
                                             ),
                                 'compression' => false
                                 );                
                
                  break;
                            
        } //end switch
        

        return array ( 'FS' => array(
                            'frontend' => $appfrontendFS,
                            'backend' => $appbackendFS
                            ),
                       'Acl' => array(
                            'frontend' => $appfrontendAcl,
                            'backend' => $appbackendAcl                          
                           
                            )
                      );
    }

    /**
     * faz cache do Acl
     */
    public function cacheAcl()
    {
        $arrConfig = $this->configApp();
        
        $appfrontend = $arrConfig['Acl']['frontend'];
        
        $appbackend = $arrConfig['Acl']['backend'];
        
        $appcache = Zend_Cache::factory('Core', 
                                        $this->getTipoCacheAcl(), 
                                        $appfrontend, 
                                        $appbackend
                                        );
        
        Zend_Registry::set('cache_acl', $appcache);            
    }

    /**
     * faz cache de blocos, questoes e alternativass
     */
    public function cacheFS()
    {
       
        $arrConfig = $this->configApp();
        
        $appfrontend = $arrConfig['FS']['frontend'];
        
        $appbackend = $arrConfig['FS']['backend'];
        
        $appcache = Zend_Cache::factory('Core', 
                                        $this->getTipoCacheFS(), 
                                        $appfrontend, 
                                        $appbackend
                                        );
     
        Zend_Registry::set('cache_FS', $appcache);       
    }    
    

    public function getTipoCacheAcl() {
        return $this->tipoCacheAcl;
    }

    public function setTipoCacheAcl($tipoCacheAcl) {
        $this->tipoCacheAcl = $tipoCacheAcl;
    }

    public function getLifetimeAcl() {
        return $this->lifetimeAcl;
    }

    public function setLifetimeAcl($lifetimeAcl) {
        $this->lifetimeAcl = $lifetimeAcl;
    }

    public function getTipoCacheFS() {
        return $this->tipoCacheFS;
    }

    public function setTipoCacheFS($tipoCacheFS) {
        $this->tipoCacheFS = $tipoCacheFS;
    }

    public function getLifetimeFS() {
        return $this->lifetimeFS;
    }

    public function setLifetimeFS($lifetimeFS) {
        $this->lifetimeFS = $lifetimeFS;
    }
   
    public function getHostMemcache() {
        return $this->hostMemcache;
    }

    public function setHostMemcache($hostMemcache) {
        $this->hostMemcache = $hostMemcache;
    }

    public function getPathCacheAcl() {
        return $this->pathCacheAcl;
    }

    public function setPathCacheAcl($pathCacheAcl) {
        $this->pathCacheAcl = $pathCacheAcl;
    }

    public function getPathCacheFS() {
        return $this->pathCacheFS;
    }

    public function setPathCacheFS($pathCacheFS) {
        $this->pathCacheFS = $pathCacheFS;
    }

}
