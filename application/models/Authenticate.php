<?php

class Model_Authenticate
{
    protected $messageErrorEmpty = 'Por favor, entre com login e senha válidos.';
    protected $messageErrorAuth = 'Login ou senha inválidos, tente novamente.';

    public function __call($method, $args)
    {
        if(!preg_match('~^(set|get)([A-Z_])(.*)$~',$method,$matches)){return;}
        $property=strtolower($matches[2]).$matches[3];if(!property_exists($this,
        $property)){throw new Exception("$property not exists");}if($matches[1]
        =='set'){$this->$property=$args[0];return $this;}return $this->$property;
    }

    public function identify($data)
	{
        
        $data = $this->_filterInputIdentify($data)->getUnescaped();

        $authAdapter = new Vtx_Auth_Adapter($data['username'], md5($data['password']));

        if (!Zend_Auth::getInstance()->authenticate($authAdapter)->isValid()) {
            throw new Vtx_UserException($this->getMessageErrorAuth());
        }

        $userRow = Zend_Auth::getInstance()->getIdentity();

        try {
            $roleRow = $userRow->findUserRole()->current()->findParentRole();
            $role = $roleRow->getDescription();
            $roleId = $roleRow->getId();
            
            /*
             * getCurrentStatus
             */
            /*
            $modelUserLocality = new Model_UserLocality();
            $modelUserLocality = new Model_UserLocality();
            $userLocalityRow = $modelUserLocality->getByUserId($userRow->getId());
            */
            
        } catch (Exception $e) {
            $role = 'guest';
            $roleId = Zend_Registry::get('config')->acl->roleGuestId;
        }
        
        $userAuthIdentify = new Model_UserAuth();
        $userAuthIdentify->setUserId($userRow->getId())
            ->setUsername($data['username'])
            ->setFirstName($userRow->getFirstName())
            ->setSurname($userRow->getSurname())
            ->setRole($role)
            ->setRoleId($roleId)
            ->setRoleLongDescription($roleRow->getLongDescription())
            ->setUserRow($userRow);

        if (!$userAuthIdentify->getEnterpriseStatus()) {
            Zend_Auth::getInstance()->clearIdentity();
            throw new Vtx_UserException('Empresa inativa');
        }   
        
        Zend_Auth::getInstance()->getStorage()->write($userAuthIdentify);

        $uri = 'questionnaire';
        
        //if ($roleId == )
        return $data['uri']? $data['uri'] : $uri;
        
        
	}
    
    protected function _filterInputIdentify($parameters)
    {
		$input = new Zend_Filter_Input(
            array('*' => 'StripTags', 'username' => 'StringTrim'),
            array(
                'username' => array(),
                'password' => array(),
                'uri' => array('allowEmpty' => true)
            ),
            $parameters,
            array('presence' => 'required')
        );
        if ($input->hasInvalid() or $input->hasMissing()) {
            throw new Vtx_UserException($this->messageErrorEmpty);
        }
		return $input;
    }
}