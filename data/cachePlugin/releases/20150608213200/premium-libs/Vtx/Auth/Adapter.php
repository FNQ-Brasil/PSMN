<?php

/**
 * 
 * Vtx_Auth_Adapter Auth_Adapter
 * @uses Zend_Auth_Adapter_Interface
 * @author tsouza
 *
 */
class Vtx_Auth_Adapter implements Zend_Auth_Adapter_Interface
{
    /**
     * Username
     *
     * @var string
     */
    protected $username = null;

    /**
     * Password
     *
     * @var string
     */
    protected $password = null;         
    
    public function __construct($username, $password)
    {
        return $this->setUsername($username)->setPassword($password);
    }
    
    public function __call($method, $args)
    {
        if(!preg_match('~^(set|get)([A-Z_])(.*)$~',$method,$matches)){return;}
        $property=strtolower($matches[2]).$matches[3];if(!property_exists($this,
        $property)){throw new Exception("$property not exists");}if($matches[1]
        =='set'){$this->$property=$args[0];return $this;}return $this->$property;
    }

    /**
     * Authenticate
     *
     * Authenticate the username and password
     *
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $codeError = Zend_Auth_Result::FAILURE;

        $userRow = DbTable_User::getInstance()->fetchRow(
            array('Login = ?' => $this->getUsername())
        );

        if (!$userRow) {
            return new Zend_Auth_Result($codeError, null, array('Authentication error'));
        }

        $hashedPassword = Vtx_Util_String::hashMe(
            $this->getPassword(), $userRow->getSalt()
        );

        if ($hashedPassword['sha'] != $userRow->getKeypass()) {
            return new Zend_Auth_Result($codeError, null, array('Authentication error'));
        }

        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $userRow, array());
    }

}