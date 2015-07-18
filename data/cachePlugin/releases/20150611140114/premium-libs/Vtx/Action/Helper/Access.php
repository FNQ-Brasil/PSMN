<?php
/**
 * 
 * Vtx_Action_Helper_Access
 * @uses Zend_Controller_Action_Helper_Abstract
 * @author tsouza
 *
 */
class Vtx_Action_Helper_Access extends Zend_Controller_Action_Helper_Abstract
{
    private $_action;
    private $_auth;
    private $_acl;
    private $_resourceName;
    
    /**
     * the page to direct to if there is a current
     * user but they do not have permission to access
     * the resource
     *
     * @var array
     */
    private $_noacl = array(
        'module' => 'default',
        'controller' => 'login',
        'action' => 'index'
    );

    public function __construct(Zend_View_Interface $view = null, array $options = array())
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_acl = Zend_Registry::get('acl');
    }

    public function init()
    {
        $this->_action = $this->getActionController();
    }
    
    public function preDispatch()
    {
        $roleUser = 'guest';
        if ($this->_auth->hasIdentity()) {
            $user = $this->_auth->getIdentity();
            $roleUser = is_object($user)? $this->_auth->getIdentity()->getRole() : null;
        }
        
        $request = $this->_action->getRequest();
        $module = $request->getModuleName();

        $controller = $request->getControllerName();
        $this->_resourceName = $resource = $module . ':' . $controller;
        $privilege = $request->getActionName();

        if (!$this->_acl->has($this->_resourceName)) {
            $Acl = new Model_Acl();
            $Acl->createResource($module, $controller);
            $this->_resourceName = null;
        }

        if ($this->_acl->isAllowed($roleUser, $this->_resourceName, $privilege)) {
            return;
        }

        $classController = ucfirst($controller) . 'Controller';
        if ($module != 'default') {
            $classController = ucfirst($module) . '_' .$classController;
        }
        $classMethods = get_class_methods($classController);

        if(!in_array($privilege . 'Action', $classMethods) and $module != 'default') {
            throw new Exception('No action error: ' . $privilege);
        }

        /* Módulo default qualquer papel pode acessar */
        if ($module == 'default') {
            return;
        }

        $baseUrl = new Zend_View_Helper_BaseUrl();

        if ($this->_auth->hasIdentity()) {
            die(
                "<b title='$roleUser, {$this->_resourceName}'>Sem acesso.</b>, <a href='"
                . $baseUrl->baseUrl('login/logout') . "'>tente novamente.</a>"
            );
        }

        Zend_Layout::getMvcInstance()->setLayout('site');

        $request->setModuleName($this->_noacl['module'])
            ->setControllerName($this->_noacl['controller'])
            ->setActionName($this->_noacl['action'])
            ->setParam('originalRequest', array(
                'resource' => $resource,
                'privilege' => $privilege,
                'uri' => $request->getRequestUri(),
                'role' => $roleUser
            ))
            ->setDispatched(false);
    }
}