<?php

/**
 * @author everton silva
 */
class CacheController extends Vtx_Action_Abstract 
{
    /**
     *
     * @var Zend_Cache 
     */
    protected $cache;

    public function indexAction() 
    {
        //$this->_redirect('/?cache');
        //desabilita layout
        //$this->_helper->layout()->disableLayout();  
        //echo "teste";
        
        $this->cache = Zend_Registry::get('cache_FS');               
       
        
        $devol = new Model_User();
        
        $user = $devol->getUserById(66);
        
        //var_dump('user: ', $user);
        
        echo "<br><br><br>";
        
        //var_dump('cache: ', $this->cache);
        
        $userCache = $this->cache->load('userCache');
        
        echo "<br><br><br>";
        
        //var_dump('userCache: ', $userCache);
        
        //recupera do cache
        if ($userCache == false) {            
            //add fechall from db into cache
            $this->cache->save($user, 'userCache');
        }
        
        echo "<br><br>------------------------";
        
        
        $this->cache->save('conteudo variavel teste2 eh este!!', 'teste2');
        $this->cache->save('conteudo variavel teste3 eh este!!', 'teste3');
        
        
        echo "<br>teste2: ".$this->cache->load('teste2');
        echo "<br>teste3: ".$this->cache->load('teste3');
        
        
    }
    
    
    public function templatecacheAction()
    {
        $this->_helper->layout()->disableLayout();  
        $this->_helper->viewRenderer->setNoRender();
        
        $frontendOptions = array(
            'lifetime' => 5, // cache lifetime of 30 seconds
            'automatic_serialization' => false  // this is the default anyways
        );

        $backendOptions = array('cache_dir' => APPLICATION_PATH_CACHE . '/cacheFS');

        $cache = Zend_Cache::factory('Output', 'File', $frontendOptions, $backendOptions);

        // we pass a unique identifier to the start() method
        if (!$cache->start('mypage')) {
            // output as usual:   $this->view->question = $loadQuestionRow;

            echo 'Hello world! ';
            echo 'This is cached (' . time() . ') ';

            $cache->end(); // the output is saved and sent to the browser
        }

        echo 'This is never cached (' . time() . ').';        
    
    
        if ( $this->_getParam('remove') == 's' )
        {
               $cache->remove('');
        }
    
    }
    
    /**
     * Metodo que faz limpeza do Cache (Memcached ou Filesystem)
     * das duas instancias de cache do MPE: cache_FS e cache_acl
     * 
     * Como chamar via admim:
     * 
     * http://[mpe-dominio]/cache/clean-all-caches
     * 
     * @author esilva
     * 
     * @param Zend_Cache $cache
     */
    public function cleanAllCachesAction(Zend_Cache $cache=null)
    {    
        $this->_helper->layout()->disableLayout();  
        $this->_helper->viewRenderer->setNoRender();        
        
        $this->cache = Zend_Registry::get('cache_FS');    
        $this->cache2 = Zend_Registry::get('cache_acl');
        
        echo "<br>Inserindo conteudo no Cache (Memcached ou Filesystem) <br><br>";
        
        $this->cache->save('conteudo variavel teste eh este!!', 'teste');
        $testando = $this->cache->load('teste');
        $this->cache2->save('conteudo variavel testeAcl eh este!!', 'testeAcl');
        $testandoAcl = $this->cache2->load('testeAcl');
        
        sleep(1);

        echo "";
        
        var_dump('teste_var (cache): ', $testando);
        var_dump('teste_var_acl (cache): ', $testandoAcl);
        
        echo "<br><BR>LIMPANDO CACHE!!!<br><BR>";
        
        // clean all records
        $this->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        $this->cache2->clean(Zend_Cache::CLEANING_MODE_ALL);

        // clean only outdated
        $this->cache->clean(Zend_Cache::CLEANING_MODE_OLD);   
        $this->cache2->clean(Zend_Cache::CLEANING_MODE_OLD);   
        
        sleep(2);
        
        echo "<br> [Verificação da limpeza do cache]<Br><BR>";

        $testando = $this->cache->load('teste');
        $testando2 = $this->cache->load('teste2');
        $testando3 = $this->cache->load('teste3');
        $testandoAcl = $this->cache2->load('testeAcl');
        
        //recupera do cache
        if ($testando == false) {            
            echo "variavel [teste] não mais existe<br>";
            echo "variavel [teste2] não mais existe<br>";
            echo "variavel [teste3] não mais existe<br>";   
            echo "variavel [testeAcl] não mais existe<br>";
        } else {
            echo "variavel [teste] existe no cache: ";
            echo "teste: ". $testando."<br>";
            echo "variavel [teste2] existe no cache: ";
            echo "teste2: ". $testando2."<br>";
            echo "variavel [teste3] existe no cache: ";
            echo "teste3: ". $testando3."<br>";
            echo "variavel [testeAcl] existe no cache: ";
            echo "testeAcl: ". $testandoAcl."<br>";            
        }
    }
    
    
    
    
    
} //end controller