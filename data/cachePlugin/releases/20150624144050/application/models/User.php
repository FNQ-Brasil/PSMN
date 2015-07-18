<?php
/**
 * 
 * Model_user
 * @uses  
 * @author mcianci
 *
 */
class Model_User
{

    public $dbTable_User = "";
    public $dbTable_UserRow = "";
    
    function __construct() {
        $this->dbTable_User = new DbTable_User();
        $this->dbTable_UserRow = new DbTable_UserRow();
    }

    public function buscarLogin($login) 
    {
        $modelCpf = new Vtx_Validate_Cpf();
        $userRow = DbTable_User::getInstance()->fetchRow(array('Login = ?' => $login));
        $cpf = preg_replace('/[^0-9]/', '', $login);
        $size = (strlen($cpf) -2);
        
        if ($size == 9) { // é CPF
            if (!$modelCpf->isValid($cpf)) {
                return array(
                    'status' => false,
                    'cpf' => $cpf,
                    'cpfValid' => false
                );
            }
        } else {
            $cpf=12345678901; // cpf não válido
        }
        if (!$userRow) {
            return array(
                'status' => false,
                'cpf' => $login,
                'cpfValid' => $modelCpf->isValid($cpf),
                'messageError' => 'Usuário não existente.'
            );
        }
        return array(
            'status' => true,
            'messageSuccess' => 'Faça seu login',
            'userRow' => $userRow
        );
    }
    
    public function getUserByLoginAndEmail($login, $email)
    {
        $resRow = DbTable_Enterprise::getInstance()->getEnterpriseByUserEmailDefault($login, $email);
        if ( $resRow ) {
            $return = array(
                'status' => true, 
                'message' => 'Usuario existente no banco dados.',
                'lastInsertId' => $resRow->getId()
                    );

            } else {
            $return = array(
                'status' => false, 
                'message' => 'Usuario nao existente no banco dados.'
                    );
            
        }       
        return  $return;
    }    
    
    function createUser($data)
    {
        if (isset($data['set_login_cpf']) and $data['set_login_cpf'] == '1') {
            $data['login'] = $data['cpf'];
        }
        
        $verifylogin = DbTable_User::getInstance()->fetchRow(array(
            'Login = ?' => $data['login']
        ));
        if ( $verifylogin ) {
            return array(
                'status' => false, 
                'messageError' => 'Nome de usuário (login) em uso.'
                    );
        }
        if ( !($data['keypass'] == $data['keypass_confirm']) ) {
            return array(
                'status' => false, 
                'messageError' => 'Senha não confere.'
                    );
        }
        $data['password'] = $data['keypass'];
        $data = $this->_filterInputUser($data)->getUnescaped();
        $pass = Vtx_Util_String::hashMe(md5($data['password']));
        $userRowData = DbTable_User::getInstance()->createRow()
            ->setPositionId(isset($data['position_id'])?
                $data['position_id']:null
            )
            ->setEducationId(isset($data['education_id'])?
                $data['education_id']:null
            )
            ->setBornDate(isset($data['born_date'])?
                Vtx_Util_Date::format_iso($data['born_date']) : null
            )
            ->setGender(isset($data['gender'])?
                $data['gender']:null
            )
            ->setCpf(isset($data['cpf'])?
                $data['cpf']:null
            )
            ->setKeypass($pass['sha'])
            ->setSalt($pass['salt'])
            ->setFirstName($data['first_name'])
            ->setSurname(isset($data['surname'])?
                $data['surname']:null
            )
            ->setEmail($data['email'])
            ->setLogin($data['login'])
            ->setPasswordHint(isset($data['password_hint'])?
                $data['password_hint']:null
            );
        $userRowData->save();
        return array(
            'status' => true,
            'lastInsertId' => $userRowData->getId()
        );
    }

    function updateUser($userRowData,$data)
    {
        $verifylogin = DbTable_User::getInstance()->fetchRow(array(
            'Login = ?' => $data['login'],
            'Id != ?' => $userRowData->getId()
        ));
        if ( $verifylogin ) {
            return array(
                'status' => false, 
                'messageError' => 'Nome de usuário (login) em uso.'
                    );
        }
        if(isset($data['change_password'])) 
        {
            unset($data['change_password']);
            $data['password'] = $data['keypass'];
        }
        if (isset($data['password'])) 
        {
            if ($data['keypass'] != $data['keypass_confirm'] or empty($data['keypass'])) {
                return array(
                    'status' => false, 
                    'messageError' => 'Senha não confere.'
                        );
            }
            $pass = Vtx_Util_String::hashMe(md5($data['password']));
        }
        $data = $this->_filterInputUser($data)->getUnescaped();
        
        $userRowData
            ->setPositionId(isset($data['position_id'])?
                $data['position_id']:$userRowData->getPositionId()
            )
            ->setEducationId(isset($data['education_id'])?
                $data['education_id']:$userRowData->getEducationId()
            )
            ->setBornDate(isset($data['born_date'])?
                Vtx_Util_Date::format_iso($data['born_date']) : $userRowData->getBornDate()
            )
            ->setGender(isset($data['gender'])?
                $data['gender']:$userRowData->getGender()
            )
            ->setCpf(isset($data['cpf'])?
                $data['cpf']:$userRowData->getCpf()
            )
            ->setKeypass(isset($data['password'])? $pass['sha'] : $userRowData->getKeypass())
            ->setSalt(isset($data['password'])? $pass['salt'] : $userRowData->getSalt())
            ->setFirstName(isset($data['first_name'])? $data['first_name'] : $userRowData->getFirstName())
            ->setSurname(isset($data['surname'])? $data['surname'] : $userRowData->getSurname())
            ->setEmail(isset($data['email'])? $data['email'] : $userRowData->getEmail())
            ->setLogin(isset($data['login'])? $data['login'] : $userRowData->getLogin())
            ->setPasswordHint(isset($data['password_hint'])? $data['password_hint'] : $userRowData->getPasswordHint())
            ->setStatus(isset($data['status'])? $data['status'] : $userRowData->getLogin())
            ;
        //'13302', NULL, NULL, 'VANESSA ZULIAN', '', '000.001.340-40', 'contato@flordovale.com', '40ef7e139a734b368975ca4eb01bbb80b224e4dc241f5a767bb57d593d9e65128dfb7f97ba198bbcce89183d925f6a9e0927487cc1c8cfd1a5bfc80d58c06bef', 'deaaf66b0e537f2', '', NULL, NULL, NULL, '123 teste'

        $userRowData->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputUser($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
            ),
            array( //validates
                'first_name' => array(
                    'NotEmpty',
                    'messages' => array('Digite o Nome da candidata.'),
                    'presence' => 'required'
                ),
                'surname' => array('allowEmpty' => true),
                'email' => array('allowEmpty' => true),
                'login' => array(
                    'allowEmpty' => true
                ),
                'password' => array(),
                'position_id' => array('allowEmpty' => true),
                'education_id' => array('allowEmpty' => true),
                'born_date' => array('allowEmpty' => true),
                'gender' => array('allowEmpty' => true),
                'cpf' => array(
                    'allowEmpty' => true,
                    new Vtx_Validate_Cpf()
                ),
                'password_hint' => array('allowEmpty' => true),
                'status' => array('allowEmpty' => true)
            ),
            $params
        );

        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }
    
    
    public function createUserMigrate($ficha) 
    {
        $UserLocality = new Model_UserLocality();
        $Acl = new Model_Acl();
        
        // dados do usuário
        $registerUserData['login'] = $ficha['cpf'];
        $registerUserData['first_name'] = $ficha['first_name'];
        $registerUserData['surname'] = isset($ficha['surname'])?
            $ficha['surname'] : null;
        $registerUserData['email'] = isset($ficha['email'])?
                strtolower($ficha['email']) : "'null'";
        $registerUserData['keypass'] = $ficha['keypass'];
        $registerUserData['keypass_confirm'] = $ficha['keypass'];
        
        $hasUL = $UserLocality->getUserLocalityByEnterpriseId($ficha['enterprise_id']);
        
        if ($hasUL) {
            return array(
                'status' => true
            );
        }
        
        // start transaction externo
        Zend_Registry::get('db')->beginTransaction();
        try {
            // #################################
            
            $insertUser = $this->createUser($registerUserData);
            if (!$insertUser['status']) {
                throw new Vtx_UserException($insertUser['messageError']);
            }   

            $registerUserLocalityData['user_id'] = $insertUser['lastInsertId'];
                $registerUserLocalityData['enterprise_id'] = $ficha['enterprise_id'];
                $insertUserLocality = $UserLocality
                    ->createUserLocality($registerUserLocalityData);
                if (!$insertUserLocality['status']) {
                    throw new Vtx_UserException($insertUserLocality['messageError']);
                }
            $Acl->setUserRole($insertUser['lastInsertId'],Zend_Registry::get('config')->acl->roleEnterpriseId);
            
            // #################################
            // fim da transaction
            Zend_Registry::get('db')->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            Zend_Registry::get('db')->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            Zend_Registry::get('db')->rollBack();
            throw new Exception($e);
        }
        
  
    }

    public function createUserTransaction($registerRowData)
    {
        $userLocality = new Model_UserLocality();
        $aclModel = new Model_Acl();
        
        $registerUserData = $registerRowData['user'];
        $userLocalityRowData = $registerRowData['userLocality'];
        $roleRowData = $registerRowData['userRole'];
        
        // start transaction externo
        Zend_Registry::get('db')->beginTransaction();
        try {
            if (isset($roleRowData['role_id'])) {
                if(empty($roleRowData['role_id'])){
                    $errorRole = array(
                        'status' => false, 
                        'messageError' => 'Escolha o Perfil.'
                            );
                    throw new Vtx_UserException($errorRole['messageError']);
                }
                $roleRow = $aclModel->getRoleById($roleRowData['role_id']);
            }
            // 1.1 Insert Responsável pelo preenchimento - usuário do sistema
        if (isset($registerUserData['set_login_cpf']) and $registerUserData['set_login_cpf'] == '1') {
                $registerUserData['login'] = $registerUserData['cpf'];
            }
            $insertUser = $this->createUser($registerUserData);
            if (!$insertUser['status']) {
                throw new Vtx_UserException($insertUser['messageError']);
            }

            // 2.1 Insert Relação UserLocality
            if (isset($userLocalityRowData['regional_id'])) {
                if ($roleRow->getIsSystemAdmin() == 0){ // verificar se a role não é isSystemAdmin()
                    if(empty($userLocalityRowData['regional_id'])){
                        $errorRegional = array(
                            'status' => false, 
                            'messageError' => 'Escolha a Regional.'
                                );
                        throw new Vtx_UserException($errorRegional['messageError']);
                    }   
                } else {
                    $userLocalityRowData['regional_id'] = null;
                }
            }
            $registerUserLocalityData['user_id'] = $insertUser['lastInsertId'];
            $registerUserLocalityData['regional_id'] = $userLocalityRowData['regional_id'];
            $insertUserLocality = $userLocality
                ->createUserLocality($registerUserLocalityData);
            if (!$insertUserLocality['status']) {
                throw new Vtx_UserException($insertUserLocality['messageError']);
            }

            // Envia email com login/senha para o novo usuário.
            if (APPLICATION_ENV != 'development') {
                $dataMail['email'] = $registerUserData['email'];
                $dataMail['first_name'] = $registerUserData['first_name'];
                $dataMail['login'] = $registerUserData['login'];
                $dataMail['password'] = $registerUserData['keypass'];
                $this->sendMailUser($dataMail,'insert');
            }

            // 3.1 Insert User Role
            $aclModel->setUserRole($insertUser['lastInsertId'],$roleRowData['role_id']);
            // end transaction externo

            Zend_Registry::get('db')->commit();
            
            return array(
                'status' => true
            );
            
        } catch (Vtx_UserException $e) {
            Zend_Registry::get('db')->rollBack();
            return array(
                'status' => false, 
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            Zend_Registry::get('db')->rollBack();
            throw new Exception($e);
        }
    }

    public function updateUserTransaction($registerRowData, $userRow)
    {
        $userLocality = new Model_UserLocality();
        $aclModel = new Model_Acl();
        $userRoleModel = new Model_UserRole();
        $registerUserData = $registerRowData['user'];
        $userLocalityRowData = $registerRowData['userLocality'];
        $roleRowData = $registerRowData['userRole'];
        
        if (isset($registerUserData['set_login_cpf']) and $registerUserData['set_login_cpf'] == '1') {
            $registerUserData['login'] = $registerUserData['cpf'];
        }
        
        // start transaction externo
        Zend_Registry::get('db')->beginTransaction();
        try {
            if (isset($roleRowData['role_id'])) {
                if(empty($roleRowData['role_id'])){
                    $errorRole = array(
                        'status' => false, 
                        'messageError' => 'Escolha o Perfil.'
                            );
                    throw new Vtx_UserException($errorRole['messageError']);
                }
                $roleRow = $aclModel->getRoleById($roleRowData['role_id']);
            }
            // 1.1 Update User - usuário do sistema
            $updateUser = $this->updateUser($userRow,$registerUserData);
            if (!$updateUser['status']) {
                throw new Vtx_UserException($updateUser['messageError']);
            }

            // 2.1 Update Relação UserLocality
            if (isset($userLocalityRowData['regional_id'])) {
                if ($roleRow->getIsSystemAdmin() == 0){ // verificar se a role não é isSystemAdmin()
                    if(empty($userLocalityRowData['regional_id'])){
                        $errorRegional = array(
                            'status' => false, 
                            'messageError' => 'Escolha a Regional.'
                                );
                        throw new Vtx_UserException($errorRegional['messageError']);
                    }   
                } else {
                    $userLocalityRowData['regional_id'] = null;
                }
                
                $userLocalityRow = $userLocality->getUserLocalityByUserId($userRow->getId());
                $registerUserLocalityData['regional_id'] = $userLocalityRowData['regional_id'];
                $updateUserLocality = $userLocality
                    ->updateUserLocality($userLocalityRow,$registerUserLocalityData);
                if (!$updateUserLocality['status']) {
                    throw new Vtx_UserException($updateUserLocality['messageError']);
                }           
            }

            // Envia email com login/senha para o novo usuário.
            if (APPLICATION_ENV != 'development') {
                $dataMail['email'] = $registerUserData['email'];
                $dataMail['first_name'] = $registerUserData['first_name'];
                $dataMail['login'] = $registerUserData['login'];
                if (isset($registerUserData['keypass'])) {
                    $dataMail['password'] = $registerUserData['keypass'];
                }
                $this->sendMailUser($dataMail,'update');
            }

            // 3.1 Update User Role
            $userRoleRow = $userRoleModel->getUserRoleByUserId($userRow->getId());
            $aclModel->updateUserRole($userRoleRow,$roleRowData['role_id']);
            // end transaction externo

            Zend_Registry::get('db')->commit();
            
            return array(
                'status' => true
            );
            
        } catch (Vtx_UserException $e) {
            Zend_Registry::get('db')->rollBack();
            return array(
                'status' => false, 
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            Zend_Registry::get('db')->rollBack();
            throw new Exception($e);
        }
    }

    function getUserById($Id)
    {
        return $this->dbTable_User->fetchRow(array('Id = ?' => $Id));
    }
    
    public function getAllAppraiser($roleId= null, $filter = null, $count = null, $offset = null)
    {
        $query = $this->dbTable_User->getAllAppraiser($roleId, $filter, 'select');
        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
    }
     
    
    public function getAllAppraiserByRegionalServiceArea(
         $roleId, $regionalId, $filter = null, $count = null,
        $offset = null, $fetchReturn = 'paginator'
    ) {
        
        if ($fetchReturn == 'paginator') {
            $query = $this->dbTable_User->getAllAppraiserByRegionalServiceArea(
                $roleId, $regionalId, $filter, 'select'
            );
            return Zend_Paginator::factory($query)
                ->setItemCountPerPage($count? $count : null)
                ->setCurrentPageNumber($offset? $offset: 1);
        }

        return $this->dbTable_User->getAllAppraiserByRegionalServiceArea(
            $roleId, $regionalId, $filter, $fetchReturn
        );
    }

    function getAll($where = null, $orderBy = null, $count = null, $offset = null, $join = null, $filter = null)
    {
        $query = $this->dbTable_User
                ->select()
                ->setIntegrityCheck(false);
        $query->from(
                array('U' => 'User'),
                array('Id','PositionId','EducationId','FirstName','Surname',
                    'Login','Email','Status','Cpf','BornDate','Gender'),
                null
            )
            ->join(
                array('UR' => 'User_Role'), 
                'UR.UserId = U.Id',
                array('RoleId')
            )
            ->joinleft(
                array('UL' => 'UserLocality'), 
                'UL.UserId = U.Id',
                array('RegionalId','EnterpriseId')
            )
            ->joinleft(
                array('R' => 'Regional'), 
                'R.Id = UL.RegionalId',
                array('Description')
            )
                ;
        if ($join) {
            foreach ($join as $value) {
                $query->joinLeft(
                    $value['name'],
                    $value['cond'],
                    $value['cols'],
                    $value['schema']
                );
            }
        }
        $query->where("UR.RoleId != ?", Zend_Registry::get('config')->acl->roleSupremeAdminId);
        $query->where("UR.RoleId != ?", Zend_Registry::get('config')->acl->roleEnterpriseId);
        if ($where) {
            $query->where($where);
        }
        if (isset($filter['role_id']) and $filter['role_id']) {
            $query->where("UR.RoleId = ?", $filter['role_id']);
        }
        if (isset($filter['regional_id']) and $filter['regional_id']) {
            $query->where("UL.RegionalId = ?", $filter['regional_id']);
        }
        if (isset($filter['first_name']) and $filter['first_name']) {
            $query->where("CONCAT(U.FirstName,' ',U.Surname) LIKE(?)", '%'.$filter['first_name'].'%');
        }
        if (isset($filter['login']) and $filter['login']) {
            $query->where("U.Login LIKE (?)", '%'.$filter['login'].'%');
        }
        if (isset($filter['cpf']) and $filter['cpf']) {
            $query->where('U.Cpf = "'.preg_replace('/[^0-9]/', '', $filter['cpf']).'"');
        }
        
        if (!$orderBy) {
            $orderBy = 'U.FirstName ASC';
        }
        if ($orderBy) {
            $query->order($orderBy);
        }

        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
        //return $this->dbTable_User->fetchAll($where = null, $order = null, $count = null, $offset = null);
    }

    function getAllJoin($where = null, $order = null, $count = null , $page = null) {
        $join = array(
            array(
               'type'  => Zend_Db_Select::LEFT_JOIN,
               'name' => array('UL' => 'UserLocality'), 
               'cond' => 'UL.UserId = U.Id',
               'cols' => array('RegionalId','EnterpriseId'),
               'schema' => null
           ),
            array(
               'type'  => Zend_Db_Select::LEFT_JOIN,
               'name' => array('R' => 'Regional'), 
               'cond' => 'UL.RegionalId = R.Id',
               'cols' => array('Description', 'Status'),
               'schema' => null
           ),
        );
        return $this->getAll($where, $order, $count, $page, $join);
    }
 
    public function sendMailUser($data,$type) 
    {
        if ($type == 'insert') {
            $subjectType = 'cadastrado';
        } else {
            $subjectType = 'editado';
        }
        $from       = Zend_Registry::get('config')->util->emailSescoop;
        $subject    = "Mulher de Negócios 2014 :: Usuário $subjectType com sucesso.";
        $message    = 'Olá '.$data['first_name'].', <br /><br />Seu usuário foi '.$subjectType.' com sucesso.<br />Login: '.$data['login'].'<br />';
        if (isset($data['password'])) {
            $message    .= 'Senha: '.$data['password'];
        }
        $bcc        = '';
        
        $eQueue = new Model_EmailQueue();
         if (isset($data['password'])) {
            $eQueue->setEmailQueue($data['email'], $from, $subject, $message, '', 'ESPERA');
         }
    }

    function getAllUserByRegionalServiceArea($roleId= null, $regionalId= null, $filter = null, $fetch= null, $count = null, $offset = null, $orderBy = null)
    {
        if (isset($regionalId) and $regionalId) {
            $modelRegional = new Model_Regional();
            $regionalRow = $modelRegional->getRegionalById($regionalId);
            $filter['regional_national'] = $regionalRow->getNational();
        }
        $query = $this->dbTable_User->getAllAppraiserByRegionalServiceArea(
                $roleId, $regionalId, $filter, $fetch, $orderBy
            );
        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : 10)
            ->setCurrentPageNumber($offset? $offset: 1);
    }

    public function deleteUser($userRow)
    {   
        DbTable_User::getInstance()->getAdapter()->beginTransaction();
        try {
            
            /* Deleta todos os UserLocality */
            $whereDeleteUserLocality = array('UserId = ?' => $userRow->getId());
            DbTable_UserLocality::getInstance()->delete($whereDeleteUserLocality);
            
            /* Deleta todos as UserRole */
            $whereDeleteUserRole = array('UserId = ?' => $userRow->getId());
            DbTable_UserRole::getInstance()->delete($whereDeleteUserRole);
             
            
            $queryApE = DbTable_AppraiserEnterprise::getInstance()->select()
                ->from(
                    array('ApE' => 'AppraiserEnterprise'),
                    array('AppraiserEnterprise' => 'ApE.UserId')
                )
                ->where('ApE.UserId = ?', $userRow->getId());
            $objResultApE = DbTable_Question::getInstance()->fetchRow($queryApE);
            if ($objResultApE) {
                return array(
                    'status' => false,
                    'messageError' => 'Usuário não pode ser deletado, há empresas relacionadas ao mesmo.'
                );
            }
            
            $userRow->delete();
            DbTable_User::getInstance()->getAdapter()->commit();
            
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_User::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_User::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public static function isGestorNational() {
        $userLogged = Zend_Auth::getInstance()->getIdentity();
        
        if (!$userLogged) {
            return FALSE;
        }
        
        $aclModel = new Model_Acl();
        $userRoleId = $userLogged->getRoleId();
        $roleRow = $aclModel->getRoleById($userRoleId);
        if ($roleRow->getIsSystemAdmin() == 1){
            return TRUE;
        }
        
        //$roleSupremeAdminId = Zend_Registry::get('config')->acl->roleSupremeAdminId;
        $roleGestorId = Zend_Registry::get('config')->acl->roleGestorId;
        
        if ($userRoleId != $roleGestorId) {
            return FALSE;
        }
        
        $modelUserLocality = new Model_UserLocality();
        $modelRegional = new Model_Regional();
        $regionalId = $modelUserLocality->getUserLocalityByUserId($userLogged->getUserId())->getRegionalId();
        $regionalRow = $modelRegional->getRegionalById($regionalId);
        if ($regionalRow->getNational() == 'S') {
            return TRUE;
        }
        
        return FALSE;
        
    }

    public function getPasswordHintByCpf($data) 
    {
        //$modelCpf = new Vtx_Validate_Cpf();
        //$login = preg_replace('/[^0-9]/', '', $data['cpf']);
        $login = $data['cpf'];
        
        $userRow = DbTable_User::getInstance()->fetchRow(
            array(
                    'Login = ?' => $login
                )
        );
        
        if ($userRow) {
            return array(
                'status' => true,
                'messageSuccess' => $userRow->getPasswordHint()
            );
        }
        
        return array(
            'status' => false,
            'messageError' => 'Usuário não existente.'
        );
        
    }
    
}
