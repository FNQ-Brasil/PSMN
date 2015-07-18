<?php

class Management_UserController extends Vtx_Action_Abstract
{
    /**
     * Px
     *
     * @var Model_Acl
     */
    protected $aclModel;

    /**
     * P
     *
     * @var Model_UserAuth
     */
    protected $userAuth;
    
    public function init()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('insert', array('json'))
             ->addActionContext('edit', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();
        
        // Contextos dos actions
        $this->_helper->getHelper('ajaxContext')
            ->addActionContext('index', array('html'))
            ->initContext();
        
        $this->aclModel = Zend_Registry::get('acl');
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->roleRow = $this->aclModel->getRoleById($this->userAuth->getRoleId(), false);
        $this->roleAppraiserId = Zend_Registry::get('config')->acl->roleAppraiserId;

        $this->userModel = new Model_User();
        $this->dbTable_User = new DbTable_User();
        $this->userRoleModel = new Model_UserRole();
        $this->positionModel = new Model_Position();
        $this->regionalModel = new Model_Regional();
        $this->dbTable_Regional = new DbTable_Regional();
        $this->educationModel = new Model_Education();
        $this->userLocalityModel = new Model_UserLocality();
    }

    public function indexAction()
    {
        
        $this->view->roles = $this->aclModel->getAllRoles();
        
        $page = $this->_getParam('page',1);
        $count = $this->_getParam('count',10);
        $orderBy = $this->view->orderBy = $this->_getParam('orderBy');
        $filter = $this->_getParam('filter',null);
        $this->view->filter = $filter;
            
        if ($this->roleRow->getIsSystemAdmin() == 1) {
            $getAllRegional = $this->regionalModel->getAll();
            $this->view->getAllRegional = $getAllRegional;
            
            $this->view->getAllUsers = $this->userModel->getAll(null, null, $count, $page,null,$filter);
            #$regionalId = ($filter['regional_id'])?$filter['regional_id']:null;
            #$filter['regional_id'] = null;
            //$this->view->getAllUsers = $this->userModel->getAllUserByRegionalServiceArea(null, $regionalId, $filter, 'all', $count, $page, $orderBy);
            return;
        }
        
        // Listagem de usuários pela ServiceArea da Regional
        if ($this->aclModel->isAllowed($this->userAuth->getRole(), 'management:user', 'list-user-by-regional')) {
            $userLocality = $this->userLocalityModel->getUserLocalityByUserId($this->userAuth->getUserId());
            $regionalIdUserLogged = $userLocality->getRegionalId();
            
            $this->view->getAllRegional = $this->dbTable_Regional
                ->getAllRegionalByOneRegionalServiceArea(null,$regionalIdUserLogged,'all',$filter, $orderBy); // $this->roleAppraiserId
            // Listar os usuarios por ServiceArea
            $this->view->getAllUsers = $this->userModel
                ->getAllUserByRegionalServiceArea(null, $regionalIdUserLogged, $filter, 'all', $count, $page, $orderBy);
            //$this->view->getAllUsers = $this->userModel->getAllJoin($where, null, 5, $this->_getParam('page'));
            return;
        }
        
    }

    public function insertAction()
    {
        $listAdmin = false;
        $this->_helper->viewRenderer->setRender('edit');
        $this->view->getAllPositions = $this->positionModel->getAll();
        $this->view->getAllEducation = $this->educationModel->getAll();
        $this->view->roles = $this->aclModel->getAllRoles();
        
        if ($this->roleRow->getIsSystemAdmin() == 1) { 
            $this->view->getAllRegional = $this->regionalModel->getAll();
            $listAdmin = true;
        }
        
        if ($this->aclModel->isAllowed($this->userAuth->getRole(), 'management:user', 
                'list-user-by-regional')
        ) {
            if (!$listAdmin) {
                $userLocality = $this->userLocalityModel
                    ->getUserLocalityByUserId($this->userAuth->getUserId());
                $this->view->getAllRegional = $this->dbTable_Regional
                    ->getAllRegionalByOneRegionalServiceArea(null,$userLocality->getRegionalId());
            }
        }
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $registerRowData = $this->_getAllParams();
        $this->view->registerRowData = $registerRowData;
        $insert = $this->userModel->createUserTransaction($registerRowData);
        
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        
        $this->view->itemSuccess = true;
        $this->view->loadUrlUser = $this->view
            ->baseUrl('/management/user/success/itemInsertSuccess/true/firstnameSuccess/'
                .urlencode($registerRowData['user']['first_name']));
    }

    public function editAction()
    {
        $userId = $this->_getParam('id');
        $userRow = $this->userModel->getUserById($userId);
        if (!$userRow) {
            throw new Exception('Invalid user');
        }
        $this->view->userId = $userId;
        $this->_helper->viewRenderer->setRender('edit');
        $this->view->getAllPositions    = $this->positionModel->getAll();
        $this->view->getAllEducation    = $this->educationModel->getAll();
        $this->view->roles              = $this->aclModel->getAllRoles();
        $listAdmin = false;
        
        if ($this->roleRow->getIsSystemAdmin() == 1) { 
            $this->view->getAllRegional = $this->regionalModel->getAll();
            $listAdmin = true;
        }
        
        if ( $this->aclModel->isAllowed($this->userAuth->getRole(), 'management:user', 
                'list-user-by-regional') 
        ) {
            if (!$listAdmin) {
                $userLocality = $this->userLocalityModel
                    ->getUserLocalityByUserId($this->userAuth->getUserId());
                $this->view->getAllRegional = $this->dbTable_Regional
                    ->getAllRegionalByOneRegionalServiceArea(null,$userLocality->getRegionalId());
            }
        }
        $userRowData['user'] = array(
            'first_name' => $userRow->getFirstName(),
            'surname' => $userRow->getSurname(),
            'gender' => $userRow->getGender(),
            'born_date' => $userRow->getBornDate(),
            'position_id' => $userRow->getPositionId(),
            'education_id' => $userRow->getEducationId(),
            'email' => $userRow->getEmail(),
            'cpf' => $userRow->getCpf(),
            'login' => $userRow->getLogin(),
            'status' => $userRow->getStatus(),
            'password_hint' => $userRow->getPasswordHint(),
        );
        $this->view->registerRowData = $userRowData;
        $userLocalityRow = $this->userLocalityModel->getUserLocalityByUserId($userRow->getId());
        
        if ($userLocalityRow){
            $this->view->registerRowData['userLocality']['regional_id'] = $userLocalityRow->getRegionalId();
        }
        
        $userRoleRow = $this->userRoleModel->getUserRoleByUserId($userRow->getId());
        $this->view->registerRowData['userRole']['role_id'] = $userRoleRow->getRoleId();
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        $registerRowData = $this->_getAllParams();
        $this->view->registerRowData = $registerRowData;
        $insert = $this->userModel->updateUserTransaction($registerRowData, $userRow);
        
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        
        $this->view->itemSuccess = true;
        $this->view->loadUrlUser = $this->view
            ->baseUrl('/management/user/success/itemUpdateSuccess/true/firstnameSuccess/'
                .urlencode($registerRowData['user']['first_name']));
    }

    public function successAction()
    {
        $this->_helper->layout()->disableLayout();
        $params = $this->_getAllParams();
        if(isset($params['itemInsertSuccess']))
        {
            $this->view->itemInsertSuccess = $params['itemInsertSuccess'];
            $this->view->firstnameSuccess = $params['firstnameSuccess'];
        }
        if (isset($params['itemUpdateSuccess'])) 
        {
            $this->view->itemUpdateSuccess = $params['itemUpdateSuccess'];
            $this->view->firstnameSuccess = $params['firstnameSuccess'];
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
    }

    public function deleteAction()
    {
        $userId = $this->_getParam('id');
        $userRow = $this->userModel->getUserById($userId);
        if (!$userRow) {
            throw new Exception('Invalid User');
        }

        $delete = $this->userModel->deleteUser($userRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            $this->_forward('index');
            return;
        }
        $this->view->itemDeleteSuccess = true;
        $this->_forward('index');
    }
    
    public function updateAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $dbTablePresident = new DbTable_President();
        $createUser = $dbTablePresident->migrateUser();
        $objArray = $createUser->toArray();
        $obj = array();
        foreach ($objArray as $newUser) {
            $obj = array();
            $senha = !empty($newUser['Password'])? $newUser['Password'] : md5('1234');
            $nome = !empty($newUser['Name'])? $newUser['Name'] : "psmn";
            $email = !empty($newUser['Email'])? $newUser['Email'] : "null";

            $obj['cpf'] = Vtx_Util_Formatting::maskFormat($newUser['Cpf'],'###.###.###-##') ;
            $obj['first_name'] = $nome;
            $obj['surname'] = $newUser['NickName'];
            $obj['email'] = $email;
            $obj['enterprise_id'] = $newUser['EnterpriseId'];
            $obj['keypass'] = $senha;
            
            $createUserMigrate = $this->userModel
                ->createUserMigrate($obj);
            if (!$createUserMigrate['status']) {
                echo '<pre>';
                print_r($obj);
                echo $createUserMigrate['messageError'];
                return;
            }
            /*
            */
            echo $newUser['EnterpriseId'].'<br />';
        }
        
        
        
    }

}