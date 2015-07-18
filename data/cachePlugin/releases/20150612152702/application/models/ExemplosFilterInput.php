<?php


class Model_teste
{
    /**
     * http://framework.zend.com/manual/en/zend.filter.input.html
     * http://akrabat.com/zend-framework/zend_filter_input-zend_validate-messages/
     * 
     * new Zend_Filter_Input($filters, $validators, $data, $options);
     */
    /*
        $input = new Zend_Filter_Input(
            array(
     *          '*' => 'StripTags', 'username' => 'StringTrim', 'platform' => 'StringTrim'
     *      ),
            array(
     *          '*' => array(), 'carrier' => 'Digits'
     *      ),
     *      $parameters,
            array('presence' => 'required')
        );
     * 
     * 
     * 
             $input = new Zend_Filter_Input(
            array(
                '*' => array('StripTags', 'StringTrim'),
                'longDescription' => array(
                    'Alpha',
                    array('StringToLower', 'encoding' => 'UTF-8')
                )
            ),
            array(
                'roleName' => array('NotEmpty'),
                'longDescription' => array('NotEmpty')
            ),
            $params,
            array('presence' => 'required')
        );
     * 
     * 
       $input = new Zend_Filter_Input(
            array(
     *          '*' => 'StripTags', 'license' => 'StringTrim'
     *      ),
            array(
     *          '*' => array()
     *      ),
     *      $parameters,
     *      array('presence' => 'required')
        );
     * 

        $validators = array(
            'license' => array('Digits', 'allowEmpty' => false, 'presence' => 'required')
        );
        $input = new Zend_Filter_Input(
            array(
                '*' => array('StripTags', 'StringTrim')
             ), $validators, $params
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Exception(Zend_Json::encode($input->getMessages(), true));
        }
     
     * 
     * 
     * 
        $validators = array(
            'templateId' => array(
                'Digits', 'allowEmpty' => false, 'presence' => 'required'
            ),
            'templateParametrosJson' => array(
                'allowEmpty' => false, 'presence' => 'required'
            ),
            'to' => array(
                'EmailAddress', 'allowEmpty' => true
            ),
        );
        $input = new Zend_Filter_Input(
            array('*' => array('StripTags', 'StringTrim')),
            $validators,
            $params
        );
     * 
     * 
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'longDescription' => array(
                    'Alpha',
                    array('StringToLower', 'encoding' => 'UTF-8'),
                    new Sescoop_Filter_Transliterate() //custom filter
                )
            ),
            array( //validates
                'roleName' => array(
                    'NotEmpty',
                    'messages' => array('xxxx')
                ),
                'longDescription' => array('NotEmpty')
            ),
            $params,
            array('presence' => 'required')
     * 
        $validators = array(
            'month' => array(
                'Alnum',        
                array(
                    'Between', array(1, 12)
                ),
                'messages' => array(            
                    array(
                        Zend_Validate_Alnum::STRING_EMPTY => "A month value is required", 
                        Zend_Validate_Alnum::NOT_ALNUM => "Month must only consist of numbers or letters"
                    ),            
                    'Month must be between 1 and 12'        
                )
            )
        );
     * 
     * 
        $validators['email'] = array(
            'NotEmpty', 'EmailAddress', 'presence' => 'required',
            'messages' => array($this->error['email'], $this->error['emailInvalid'])
        );
		$input = new Zend_Filter_Input(
            array('*' => array('StripTags', 'StringTrim')),
            $validators, $params
        );
     * 
    */
    /*
     *  EXEMPLO full
     * 
     */
    protected $error = array(
        'termo' => 'Para finalizar la compra, es necesario aceptar los términos y condiciones.',
        'politica' => 'Para finalizar la compra, es necesario aceptar la política de privacidad.',
        'email' => 'Para finalizar la compra, es necesario correo electrónico.',
        'emailInvalid' => 'Por favor, informa un correo electrónico válido.',
        'emailExists' => 'Correo electrónico ya cadastrodo.',
        'cancel' => 'Tu suscripción ha sido cancelada.'
    );

    public function add($params)
    {
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        try {
            $params = $this->_filterInputIdentify($params)->getUnescaped();
            $preRegister = $this->preRegister($params);
            Zend_Db_Table::getDefaultAdapter()->commit();
            return $preRegister;
        } catch (Vtx_UserException $e) {
            Zend_Db_Table::getDefaultAdapter()->rollBack();
            return array('status' => false, 'error' => $e->getMessage());
        }
    }

    protected function _filterInputIdentify($params)
    {
        $validators = array(
            'aceitoTermo' => array(
                'NotEmpty', 'messages' => array($this->error['termo']),
                'presence' => 'required'
            ),
            'aceitoPolitica' => array(
                'NotEmpty', 'messages' => array($this->error['politica']),
                'presence' => 'required'
            ),
            'aceitoInfo' => array('allowEmpty' => true),
            'aceitoPromo' => array('allowEmpty' => true),
            'package' => array('allowEmpty' => false, 'presence' => 'required'),
            'phone' => array('allowEmpty' => false, 'presence' => 'required'),
            'password' => array('allowEmpty' => false, 'presence' => 'required')
        );

        if (!empty($params['aceitoInfo']) or !empty($params['aceitoPromo'])) {
            $validators['email'] = array(
                'NotEmpty', 'messages' => array($this->error['email']),
                'presence' => 'required'
            );
            $emailValidator = new Zend_Validate_EmailAddress();
        }

        if (empty($params['aceitoInfo']) and empty($params['aceitoPromo'])) {
            $params['email'] = date('Ymdhis') . '@titans.com';
            $validators['email'] = array('allowEmpty' => true);
        }

        
        
        $input = new Zend_Filter_Input(
            array('*' => array('StripTags', 'StringTrim')),
            $validators,
            $params
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        
        if (isset($emailValidator) and !$emailValidator->isValid($params['email'])) {
            throw new Vtx_UserException($this->error['emailInvalid']);
        }

        if ((!empty($params['aceitoInfo']) or !empty($params['aceitoPromo']))
                and DbTable::getInstance('PacoteAquisicao')
                    ->getPacoteAquisicaoByDsEmail($params['email'])->count()
        ) {
            throw new Vtx_UserException($this->error['emailExists']);
        }

        return $input;
    }
    
    
    

}