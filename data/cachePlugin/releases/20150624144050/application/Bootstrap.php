<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function __construct($application)
    {
        parent::__construct($application);
        //Vtx_Error::set();     
        define('FPDF_FONTPATH', APPLICATION_PATH_LIBS . '/Fpdf/font/'); 
    }

    protected function _initCachePlugin()
    {
        //Tunning do Zend
        $pathIncCache = APPLICATION_PATH_CACHE . '/cachePlugin';
        $classFileIncCache =  $pathIncCache . '/pluginLoaderCache.php';
        if (file_exists($classFileIncCache)) {
           include_once $classFileIncCache;
        }
        Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);
    }
    
    protected function _initLog()
    {
        /*$options = $this->getOption('resources');

        $partitionConfig = $this->getOption('log');
        $logOptions = $options['log'];

        $baseFilename = $logOptions['stream']['writerParams']['stream'];
        if ($partitionConfig['partitionStrategy'] == 'context'){
            $baseFilename = $partitionConfig['path'].'/'.APPLICATION_ENV;
        }

        $logFilename = $baseFilename.'_'.date('Y_W'); //semanalmente
        $logOptions['stream']['writerParams']['stream'] = $logFilename;

        $logger = Zend_Log::factory($logOptions);
        Zend_Registry::set('logger', $logger);

        return $logger;*/
    }

    protected function _initLoaderResource()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '', 'basePath' => dirname(__FILE__),
            'resourceTypes' => array(
                'model' => array('path' => 'models/', 'namespace' => 'Model'),
                'dbtable' => array('path' => 'models/DbTable/', 'namespace' => 'DbTable')
            )
        ));

        return $autoloader;
    }

    protected function _initConfig()
    {
        $config = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/config.ini', APPLICATION_ENV
        );
        Zend_Registry::set('config', $config);

        return $config;
    }
    
    /**
     * Initializes the cache.
     */
    protected function _initCache()
    {
        $path_cache = APPLICATION_PATH_CACHE . '/cacheAcl';
        $appfrontend = array('lifetime' => 14400, 'automatic_serialization' => true);
        $appbackend = array('cache_dir' => $path_cache);
        $appcache = Zend_Cache::factory('Core', 'File', $appfrontend, $appbackend);
        Zend_Registry::set('cache_acl', $appcache);       
    }
    
    protected function _initCacheFS()
    {
        //diretorio onde sera cacheado
        $path_cache = APPLICATION_PATH_CACHE . '/cacheFS';
        //qto tempo de cache
        $appfrontend = array('lifetime' => 14400, //seconds 
                             'automatic_serialization' => true,
                             //'debug_header' => true,
                             );
        //
        $appbackend = array('cache_dir' => $path_cache);
        $appcache = Zend_Cache::factory('Core', 'File', $appfrontend, $appbackend);
     
        Zend_Registry::set('cache_FS', $appcache);       
    }
    
    
    /**
     * Add databases to the registry
     * 
     * @return void
     */
    public function _initDbRegistry()
    {
        $this->bootstrap('multidb');
        $multidb = $this->getPluginResource('multidb');
        Zend_Registry::set('db', $multidb->getDb('db'));
        //Zend_Registry::set('db3', $multidb->getDb('db3')); //sql
    }
    
    /**
     * Initializes default Configuration
     */
    protected function _initSescoopConfiguration()
    {
        Zend_Registry::set('programaTipo', 'Psmn');
        $configuration = new Model_Configuration;
        Zend_Registry::set('configDb', $configuration->getSescoopConfiguration());
    }

    protected function _initTranslate()
    {
        /* @TODO Colocar no cache ou nao */
		$translator = new Zend_Translate(array(
            'adapter' => 'array',
            'content' => APPLICATION_PATH_LIBS . '/Vtx/languages',
            'locale' => 'pt_BR',
            'scan' => Zend_Translate::LOCALE_DIRECTORY
        ));
		Zend_Validate_Abstract::setDefaultTranslator($translator);
        Zend_Registry::set('Zend_Translate', $translator);       
    }

    protected function _initPagination()
    {
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_Paginator::setDefaultPageRange(5);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginator/paginationControl.phtml');
    }
    
    
	/**
	 * Configura os HelperBrokers
	 */
	protected function _initActionHelpers()
	{
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH.'/modules/default/controllers/action/helper', 'Action_Helper');
	}    
    
    
}