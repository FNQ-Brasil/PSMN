<?php
/**
 * 
 * Model_President
 * @uses  
 * @author mcianci
 *
 */
class Model_President
{

    public $dbTable_President = "";
    
    function __construct() {
        $this->dbTable_President = new DbTable_President();
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_President->fetchAll($where, $order, $count, $offset);
    }

    public function createPresident($data)
    {
        $data = $this->_filterInputPresident($data)->getUnescaped();
        $presidentRowData = DbTable_President::getInstance()->createRow()
            ->setEnterpriseId($data['enterprise_id'])
            ->setEducationId($data['education_id'])
            ->setPositionId($data['position_id'])
            ->setFindUsId($data['find_us_id'])
            ->setName($data['name'])
            ->setNickName(isset($data['nick_name'])? $data['nick_name'] : null)
            ->setCpf($data['cpf'])
            ->setPhone(isset($data['phone'])?$data['phone'] : null)
            ->setCellphone(isset($data['cellphone'])? $data['cellphone'] : null)
            ->setEmail(isset($data['email'])? $data['email'] : null)
            ->setBornDate(isset($data['born_date'])?
                Vtx_Util_Date::format_iso($data['born_date']) : null
            )
            ->setGender($data['gender'])
            ->setNewsletterEmail(isset($data['newsletter_email'])?
                $data['newsletter_email']:0)
            ->setNewsletterMail(isset($data['newsletter_mail'])?
                $data['newsletter_mail']:0)
            ->setNewsletterSms(isset($data['newsletter_sms'])?
                $data['newsletter_sms']:0)
            ->setAgree($data['agree'])
            ->setCreated($data['created'])
            ;
        $presidentRowData->save();
        return array(
            'status' => true,
            'lastInsertId' => $presidentRowData->getId()
        );
    }

    public function updatePresident($presidentRow, $data)
    {
        $data = $this->_filterInputPresident($data)->getUnescaped();
        $presidentRow
            ->setEnterpriseId(isset($data['enterprise_id'])? $data['enterprise_id']:$presidentRow->getEnterpriseId())
            ->setEducationId(isset($data['education_id'])? $data['education_id'] : $presidentRow->getEducationId())
            ->setPositionId(isset($data['position_id'])? $data['position_id'] : $presidentRow->getPositionId())
            ->setFindUsId(isset($data['find_us_id'])? $data['find_us_id'] : $presidentRow->getFindUsId())
            ->setName(isset($data['name'])? $data['name'] : $presidentRow->getName())
            ->setNickName(isset($data['nick_name'])? $data['nick_name'] : $presidentRow->getNickName())
            ->setCpf(isset($data['cpf'])? $data['cpf'] : $presidentRow->getCpf())
            ->setPhone(isset($data['phone'])? $data['phone'] : $presidentRow->getPhone())
            ->setCellphone(isset($data['cellphone'])? $data['cellphone'] : $presidentRow->getCellphone())
            ->setEmail(isset($data['email'])? $data['email'] : $presidentRow->getEmail())
            ->setBornDate(isset($data['born_date'])?
                Vtx_Util_Date::format_iso($data['born_date']) : $presidentRow->getBornDate()
            )
            ->setGender(isset($data['gender'])? $data['gender'] : $presidentRow->getGender())
            ->setNewsletterEmail(isset($data['newsletter_email'])? $data['newsletter_email'] : 0)
            ->setNewsletterMail(isset($data['newsletter_mail'])? $data['newsletter_mail'] : 0)
            ->setNewsletterSms(isset($data['newsletter_sms'])? $data['newsletter_sms'] : 0)
            ->setAgree(isset($data['agree'])? $data['agree'] : 0);
        $presidentRow->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputPresident($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'name' => array(),
                'cpf' => array('Digits')
            ),
            array( //validates
                'enterprise_id' => array(
                    'NotEmpty',
                    'presence' => 'required'
                ),
                'education_id' => array(
                    'NotEmpty',
                    'messages' => array('Escolha o Nivel de Escolaridade.'),
                    'presence' => 'required'
                ),
                'position_id' => array(
                    'NotEmpty',
                    'messages' => array('Escolha o Cargo.'),
                    'presence' => 'required'
                ),
                'find_us_id' => array(
                    'NotEmpty',
                    'messages' => array('Selecione o item como nos conheceu.'),
                    'presence' => 'required'
                ),
                'name' => array(
                    'NotEmpty',
                    'messages' => array('Digite o Nome da candidata.'),
                    'presence' => 'required'
                ),
                'nick_name' => array('allowEmpty' => true),
                'cpf' => array(
                    'NotEmpty',
                    'messages' => array('Digite o CPF.'),
                    new Vtx_Validate_Cpf()
                ),
                'email' => array('allowEmpty' => true),
                'phone' => array('allowEmpty' => true),
                'cellphone' => array('allowEmpty' => true),
                'born_date' => array(
                    'NotEmpty',
                    'messages' => array('Digite a Data de Nascimento.'),
                    'presence' => 'required',
                    new Zend_Validate_Date('dd/MM/yyyy')
                ),
                'gender' => array('allowEmpty' => true),
                'newsletter_email' => array('allowEmpty' => true),
                'newsletter_mail' => array('allowEmpty' => true),
                'newsletter_sms' => array('allowEmpty' => true),
                'agree' => array(
                    'NotEmpty',
                    'messages' => array('É necessário aceitar o regulamento'),
                    'presence' => 'required'
                ),
                'created' => array('allowEmpty' => true)
            ),
            $params,
            array()
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }

    public function getPresidentById($Id)
    {
        return $this->dbTable_President->fetchRow(array('Id = ?' => $Id));
    }
    
    public function getPresidentByEnterpriseId($enterpriseId)
    {
        return $this->dbTable_President->fetchRow(array('EnterpriseId = ?' => $enterpriseId));
    }
    
    public function isValidNewsletter($newsletterStatus,$presidentData) 
    {

        $email = isset($presidentData['newsletter_email'])? $presidentData['newsletter_email'] : null;
        $mail = isset($presidentData['newsletter_mail'])? $presidentData['newsletter_mail'] : null;
        $sms = isset($presidentData['newsletter_sms'])? $presidentData['newsletter_sms'] : null;
        $cellphone = isset($presidentData['cellphone'])? $presidentData['cellphone'] : null;
        
        if (
                ($newsletterStatus == '0') and 
                (!empty($email) or !empty($mail) or !empty($sms))
            ) {
            return array(
                'status' => false,
                'messageError' => 'Confirme que deseja receber informações do SEBRAE.'
            );
        }
        
        if ( ($newsletterStatus == '1') and !$cellphone and $cellphone ) {
            return array(
                'status' => false,
                'messageError' => 'Preencha seu celular, para receber SMS.'
            );
        }

        if (
                ($newsletterStatus == '1') and 
                (empty($email) and empty($mail) and empty($sms))
            ) {
            return array(
                'status' => false,
                'messageError' => 'Selecione o seu meio de contato preferido para receber informações do SEBRAE.'
            );
        }
        
        return array(
            'status' => true
        );
        
    }
}   