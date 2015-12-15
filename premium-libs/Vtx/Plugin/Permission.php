<?php
/**
 * Access control plugin.
 * @author tsouza
 */
class Vtx_Plugin_Permission extends Zend_Controller_Plugin_Abstract
{    
    protected $_view;
	
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        //permite que aplicacoes CLI (cli.php) rodem sem erro de ACL
        if (CLI_APP == true) {
            return;
        }
        
        switch (Zend_Registry::get('programaTipo')) {
            case 'MpeDiagnostico':
                $sysId = 2;
                break;
            case 'SebraeMais':
                $sysId = 3;
                break;
            default:
                $sysId = 1;
                break;
        }
        
        //usa camada cache da aplicacao (premium-libs)
        $cacheSite = new Vtx_Cache_MPE_SiteCache();
        $acl = $cacheSite->fazCacheAcl($sysId);
        
        Zend_Registry::getInstance()->set('acl', $acl);
        Zend_Controller_Action_HelperBroker::addHelper(new Vtx_Action_Helper_Access());
    }
}