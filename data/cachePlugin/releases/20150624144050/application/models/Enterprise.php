<?php

/**
 * 
 * Model_Enterprise
 * @uses  
 *
 */
class Model_Enterprise {

    public $dbTable_Enterprise = "";

    function __construct() {
        $this->dbTable_Enterprise = new DbTable_Enterprise();
    }

    function getEnterpriseById($Id) {
        return $this->dbTable_Enterprise->fetchRow(array('Id = ?' => $Id));
    }

    public function getEnterpriseByIdKey($IdKey) {
        return $this->dbTable_Enterprise->fetchRow(array('IdKey = ?' => $IdKey));
    }

    public function getUserIdByIdKey($IdKey) {
        $enterpriseId = $this->getEnterpriseByIdKey($IdKey)->getId();
        $modelUserLocality = new Model_UserLocality();
        return $modelUserLocality->getUserLocalityByEnterpriseId($enterpriseId)->getUserId();
    }

    public function getIdKeyByUserId($userId) {
        $modelUserLocality = new Model_UserLocality();
        $enterpriseId = $modelUserLocality->getUserLocalityByUserId($userId)->getEnterpriseId();
        return $this->getEnterpriseById($enterpriseId)->getIdKey();
    }

    public function getDataUnionReportRegionalQuerys($arrayDbSelects) {
        $dbEnterprise = $this->dbTable_Enterprise;
        $query = $dbEnterprise->select()->union($arrayDbSelects)->order('1');
        /*
          echo $query;
          die();
         */
        return $dbEnterprise->fetchAll($query);
    }
    
    public function hasEligibilityRules($enterpriseIdKey) {
        $enterpriseRow = $this->getEnterpriseByIdKey($enterpriseIdKey);
        $creationDate = $enterpriseRow->getCreationDate();
        $annualRevenue = $enterpriseRow->getAnnualRevenue();
        $categoryAwardId = $enterpriseRow->getCategoryAwardId();
        $employeesQuantity = intval($enterpriseRow->getEmployeesQuantity(), 10);
        
        $dataArray = explode('-', $creationDate);
        
        
        $creationDateTS = Vtx_Util_Date::format_dateToTimeStamp($creationDate);
        $dtLimite = Vtx_Util_Date::format_dateToTimeStamp('2013-03-01');
        
        $cDTS = date('d/m/Y',$creationDateTS);
            $rule = '0';
        $dataInscricao = FALSE;
        if ($dtLimite < $creationDateTS) {
            $dataInscricao = TRUE; // indica data invalida
        }
        /*
            echo $dataInscricao;
        echo '<pre>';
        var_dump($dtLimite, $creationDateTS, $creationDate,$cDTS,$employeesQuantity);
        
        die;
        if ($categoryAwardId == 3 and $annualRevenue >= 2) {
            var_dump($annualRevenue, $categoryAwardId);
            if ($annualRevenue >= 2) {
                echo 'passou >= 2<br>';
            }
        }
        */
        switch ($categoryAwardId) {
            case '1': // Pequenos negócios; somente 1 ano ou mais
                 //
                if ($dataInscricao) {
                    $rule = '2'; // data invalida
                }
                //faturamento menos que 3,6mi ok
                // sempre TRUE
                break;
            case '2': // Produtora Rural; somente 1 ano ou mais
                //faturamento menos que 3,6mi ok
                if ($dataInscricao) {
                    $rule = '2'; // data invalida
                } 
                break;
            case '3': // Microempreendedora individual; 1 ano ou mais e faturamento acima de 60k
                  //1 empregado ou mais
                  //faturamento 60k ou menos que 3,6 milhoes ok

                if (($annualRevenue >= 2)  and $dataInscricao) { // annualRevenuePsmn
                    $rule = '1'; // fat e data invalida
                } else if ($dataInscricao) {
                    $rule = '2'; // data invalida
                } else if ($annualRevenue >= 2) {
                    $rule = '3';
                } 
                break;

            default:
                //$rule = FALSE;
                break;
        }
        return $rule;
        /*
        */
        
        
    }

    public function createEnterprise($data) {
        $data['status'] = isset($data['status']) ? $data['status'] : 'A';
        $data = $this->_filterInputEnterprise($data)->getUnescaped();
        $verifyCnpj = DbTable_Enterprise::getInstance()->fetchRow(array(
            'Cnpj = ?' => $data['cnpj']
        ));
        if ($verifyCnpj and !empty($data['cnpj'])) {
            return array(
                'status' => false,
                'messageError' => 'CNPJ (' . $data['cnpj'] . ') já em uso.'
            );
        }
        $enterpriseRowData = DbTable_Enterprise::getInstance()
                ->createRow()
                ->setCategoryAwardId($data['category_award_id'])
                ->setCategorySectorId($data['category_sector_id'])
                ->setSocialName($data['social_name'])
                ->setFantasyName(isset($data['fantasy_name']) ? $data['fantasy_name'] : null)
                ->setStatus($data['status'])
                ->setCnpj($data['cnpj'])
                ->setStateRegistration(isset($data['state_registration']) ?
                                $data['state_registration'] : null
                )
                ->setDap(isset($data['dap']) ?
                                $data['dap'] : null
                )
                ->setRegisterMinistryFisher(isset($data['register_ministry_fisher']) ?
                                $data['register_ministry_fisher'] : null
                )
                ->setCreationDate(isset($data['creation_date']) ?
                                Vtx_Util_Date::format_iso($data['creation_date']) : null
                )
                ->setEmployeesQuantity(isset($data['employees_quantity']) ? $data['employees_quantity'] : null)
                ->setAnnualRevenue(isset($data['annual_revenue']) ?
                                Vtx_Util_Formatting::realToDecimal($data['annual_revenue']) : null
                )
                ->setCnae(isset($data['cnae']) ? $data['cnae'] : null)
                ->setPhone(isset($data['phone']) ? $data['phone'] : null)
                ->setSite(isset($data['site']) ? $data['site'] : null)
                ->setEmailDefault(isset($data['email_default']) ? $data['email_default'] : null)
                ->setCompanyHistory(isset($data['company_history']) ? $data['company_history'] : null)
        ;
        $enterpriseRowData->save();

        $sha = sha1(uniqid(rand(), true) . $enterpriseRowData->getId());
        $enterpriseRowData->setIdKey($sha);
        $enterpriseRowData->save();

        //Zend_Registry::get('db')->commit();
        return array(
            'status' => true,
            'lastInsertId' => $enterpriseRowData->getId(),
            'lastInsertIdKey' => $enterpriseRowData->getIdKey(),
            'row' => $enterpriseRowData
        );
    }

    public function updateEnterprise($enterpriseRowData, $data) {
        $data['status'] = isset($data['status']) ? $data['status'] : $enterpriseRowData->getStatus();
        
        
        $data = $this->_filterInputEnterprise($data)->getUnescaped();

        $verifyCnpj = DbTable_Enterprise::getInstance()->fetchRow(array(
            'Cnpj = ?' => $data['cnpj'],
            'IdKey != ?' => $enterpriseRowData->getIdKey(),
        ));
        if ($verifyCnpj and !empty($data['cnpj'])) {
            return array(
                'status' => false,
                'messageError' => 'CNPJ (' . $data['cnpj'] . ') já em uso.'
            );
        }
        $enterpriseRowData
                ->setCategoryAwardId(isset($data['category_award_id']) ?
                                $data['category_award_id'] : $enterpriseRowData->getCategoryAwardId()
                )
                ->setCategorySectorId(isset($data['category_sector_id']) ?
                                $data['category_sector_id'] : $enterpriseRowData->getCategorySectorId()
                )
                ->setSocialName(isset($data['social_name']) ?
                                $data['social_name'] : $enterpriseRowData->getSocialName()
                )
                ->setFantasyName(isset($data['fantasy_name']) ?
                                $data['fantasy_name'] : $enterpriseRowData->getFantasyName()
                )
                ->setStatus(isset($data['status']) ?
                                $data['status'] : $enterpriseRowData->getStatus()
                )
                ->setCnpj(isset($data['cnpj']) ?
                                $data['cnpj'] : $enterpriseRowData->getCnpj()
                )
                ->setStateRegistration(isset($data['state_registration']) ?
                                $data['state_registration'] : $enterpriseRowData->getStateRegistration()
                )
                ->setDap(isset($data['dap']) ?
                                $data['dap'] : $enterpriseRowData->getDap()
                )
                ->setRegisterMinistryFisher(isset($data['register_ministry_fisher']) ?
                                $data['register_ministry_fisher'] : $enterpriseRowData->getRegisterMinistryFisher()
                )
                ->setCreationDate(isset($data['creation_date']) ?
                                Vtx_Util_Date::format_iso($data['creation_date']) : $enterpriseRowData->getCreationDate()
                )
                ->setEmployeesQuantity(isset($data['employees_quantity']) ?
                                $data['employees_quantity'] : $enterpriseRowData->getEmployeesQuantity()
                )
                ->setAnnualRevenue(isset($data['annual_revenue']) ?
                                Vtx_Util_Formatting::realToDecimal($data['annual_revenue']) :
                                $enterpriseRowData->getAnnualRevenue()
                )
                ->setCnae(isset($data['cnae']) ? $data['cnae'] : $enterpriseRowData->getCnae())
                ->setEmailDefault(isset($data['email_default']) ?
                                $data['email_default'] : $enterpriseRowData->getEmailDefault()
                )
                ->setPhone(isset($data['phone']) ?
                                $data['phone'] : $enterpriseRowData->getPhone()
                )
                ->setSite(isset($data['site']) ?
                                $data['site'] : $enterpriseRowData->getSite()
                )
                ->setCompanyHistory(isset($data['company_history']) ?
                                $data['company_history'] : $enterpriseRowData->getCompanyHistory()
                )
        ;
        $enterpriseRowData->save();
        return array(
            'status' => true,
            'row' => $enterpriseRowData
        );
    }

    protected function _filterInputEnterprise($params) {
        $input = new Zend_Filter_Input(
                array(//filters
            '*' => array('StripTags', 'StringTrim'),
            'cnpj' => array(
                array('Digits')
            ),
            'employees_quantity' => array(
                array('Digits')
            )
                ), array(//validates
            'social_name' => array(
                'NotEmpty',
                'messages' => array('Digite a Razão Social'),
                'presence' => 'required'),
            'fantasy_name' => array(
                'NotEmpty',
                'messages' => array('Digite o Nome Fantasia'),
                'presence' => 'required'),
            'status' => array('allowEmpty' => true),
            'cnpj' => array(
                'allowEmpty' => true,
                new Vtx_Validate_Cnpj()
            ),
            'creation_date' => array(
                'NotEmpty',
                new Zend_Validate_Date('dd/MM/yyyy')
            ),
            'employees_quantity' => array(
                'NotEmpty',
                'messages' => array('Número de colaboradores: Digite apenas números.'),
                'Digits'
            ),
            'annual_revenue' => array(
                'NotEmpty',
                'messages' => array('Escolha o Faturamento.'),
                'presence' => 'required'
            ),
            'cnae' => array('allowEmpty' => true),
            'email_default' => array('allowEmpty' => true),
            'phone' => array(
                'NotEmpty',
                'messages' => array('Digite o telefone.'),
                'presence' => 'required'
            ),
            'site' => array('allowEmpty' => true),
            'company_history' => array(
                'NotEmpty',
                'messages' => array('Digite o Resumo da empresa.'),
                'presence' => 'required'),
            'category_sector_id' => array(
                'NotEmpty',
                'messages' => array('Escolha a Categoria setorial.'),
                'presence' => 'required'),
            'category_award_id' => array(
                'NotEmpty',
                'messages' => array('Escolha a Categoria do Premio.'),
                'presence' => 'required'
            ),
            'state_registration' => array('allowEmpty' => true),
            'dap' => array('allowEmpty' => true),
            'register_ministry_fisher' => array('allowEmpty' => true),
                ), $params
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
            Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }

    public function deleteEnterprise($enterpriseRow) {
        DbTable_Enterprise::getInstance()->getAdapter()->beginTransaction();
        try {
            $enterpriseId = $enterpriseRow->getEnterpriseId();
            $objResultEnterprise = true;
            if (!$objResultEnterprise) {
                $enterpriseRow->delete();
                DbTable_Enterprise::getInstance()->getAdapter()->commit();
                DbTable_Enterprise::getInstance()->reorder($enterpriseId);
            } else {
                return array(
                    'status' => false,
                    'messageError' => 'Há critérios para este bloco.'
                );
            }
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Enterprise::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false,
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Enterprise::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function getAllWithRa($questionnaireId = null, $count = null, $offset = null, $filter = null, $orderBy = null) {
        $query = $this->dbTable_Enterprise->getAllWithRa($questionnaireId, 'select', $filter, $orderBy);

        return Zend_Paginator::factory($query)
                        ->setItemCountPerPage($count ? $count : 10)
                        ->setCurrentPageNumber($offset ? $offset : 1);
    }

    public function getAllByColAE(
    $valuesAddress, $colAddress, $questionnaireId, $count = null, $offset = null, $filter = null, $orderBy = null, $format = 'html', $fetchReturn = 'paginator', $tipoRelatorio = 'inscricoes'
    ) {
        
        if ($fetchReturn == 'paginator') {
            
            $query = $this->dbTable_Enterprise->getAll(
                    $valuesAddress, $colAddress, $questionnaireId, 'select', $filter, $orderBy, $format, $tipoRelatorio
            );
            
            
            return Zend_Paginator::factory($query)
                            ->setItemCountPerPage($count ? $count : null)
                            ->setCurrentPageNumber($offset ? $offset : 1);
        }

        $data = $this->dbTable_Enterprise->getAll(
                $valuesAddress, $colAddress, $questionnaireId, $fetchReturn, $filter, $orderBy, $format, $tipoRelatorio
        );
        return $data;
    }

    public function sendMail($to, $firstName, $login, $pass) {
        $from = Zend_Registry::get('config')->util->emailSescoop;
        $bcc = Zend_Registry::get('config')->util->emailBcc; // 'projeto.sescoop.fnq@vorttex.com.br';
        $subject = 'Mulher de Negócios 2014 :: Cadastro efetuado com sucesso.';
        $message = 'Olá ' . $firstName . ', <br /><br />Seu usuário foi ativado com sucesso.<br />Login: ' . $login . '<br />Senha: ' . $pass;

        //Vtx_Util_Mail::send($to,$from,$subject,$message,$bcc);

        $eQueue = new Model_EmailQueue();
        $eQueue->setEmailQueue($to, $from, $subject, $message, $bcc);
    }

    public function sendMailEdit($to, $firstName, $login, $pass) {
        $from = Zend_Registry::get('config')->util->emailSescoop;
        $bcc = 'projeto.sescoop.fnq@vorttex.com.br';
        $subject = 'Mulher de Negócios 2014 :: Alteração efetuada com sucesso.';
        $message = 'Olá ' . $firstName . ', <br /><br />';
        $message .= 'Seus dados foram atualizados com sucesso.<br />';
        $message .= 'Login:' . $login . '<br />';
        if (isset($pass)) {
            $message .= 'Nova Senha: ' . $pass;
        }

        //Vtx_Util_Mail::send($to,$from,$subject,$message,$bcc);
        $eQueue = new Model_EmailQueue();
        $eQueue->setEmailQueue($to, $from, $subject, $message, $bcc);
    }

    public function createEnterpriseTransaction($ficha) {
        $AddressEnterprise = new Model_AddressEnterprise();
        $AddressPresident = new Model_AddressPresident();
        $President = new Model_President();
        $PresidentProgram = new Model_PresidentProgram();
        $User = new Model_User();
        $UserLocality = new Model_UserLocality();
        $Acl = new Model_Acl();
        $modelEntCatAwardCompetition = new Model_EnterpriseCategoryAwardCompetition();
        $modelLogCadastroEmpresa = new Model_LogCadastroEmpresa();
        //$Eligibility = new Model_Eligibility();
        // dados da Empresa
        $registerEnterpriseData = $ficha['enterprise'];
        $registerEnterpriseData['status'] = 'A';

        // dados endereço da Empresa
        $registerAddressEnterpriseData = $ficha['addressEnterprise'];

        // dados da presidente da Empresa
        $registerPresidentData = $ficha['president'];
        $registerPresidentData['gender'] = 'F';
        $registerPresidentData['created'] = date('Y-m-d');

        // dados endereço da Empresa
        $registerAddressPresidentData = $ficha['addressPresident'];
        // dados do program do presidente da Empresa
        $registerPresidentProgramData = isset($ficha['presidentProgram']) ?
                $ficha['presidentProgram'] : null;

        // dados do usuário
        $registerUserData = $ficha['user'];
        $registerUserData['login'] = $registerPresidentData['cpf'];
        $registerUserData['first_name'] = $registerPresidentData['name'];
        $registerUserData['surname'] = isset($registerPresidentData['nick_name']) ?
                $registerPresidentData['nick_name'] : null;
        $registerUserData['email'] = $registerPresidentData['email'];
        $registerUserData['status'] = 'A';

        $registerLogCadastro = isset($ficha['log_cadastro_empresa']) ? $ficha['log_cadastro_empresa'] : false;

        // start transaction externo
        Zend_Registry::get('db')->beginTransaction();
        try {

            // Validação Categoria do Premio
            $insertCategoryAward = $this->validCategoryAward($registerEnterpriseData);
            if (!$insertCategoryAward['status']) {
                throw new Vtx_UserException($insertCategoryAward['messageError'], 10);
            }

            // 1.1 Insert Empresa
            $insertEnterprise = $this->createEnterprise($registerEnterpriseData);
            if (!$insertEnterprise['status']) {
                throw new Vtx_UserException($insertEnterprise['messageError'], 10);
            }

            // elegibilidade para questionario de diagnostico
            //$Eligibility->doDiagnosticoEligibility($insertEnterprise['row']);
            // 1.2 Insert Endereço da Empresa
            $registerAddressEnterpriseData['enterprise_id'] = $insertEnterprise['lastInsertId'];
            $insertAddressEnterprise = $AddressEnterprise
                    ->createAddressEnterprise($registerAddressEnterpriseData);
            if (!$insertAddressEnterprise['status']) {
                throw new Vtx_UserException($insertAddressEnterprise['messageError'], 10);
            }

            // validação dos campos NewsLetter da Candidata (President)
            $newsLetterValid = $President->isValidNewsletter($ficha['newsletter'], $registerPresidentData);
            if (!$newsLetterValid['status']) {
                throw new Vtx_UserException($newsLetterValid['messageError']);
            }

            // 2.1 Insert Presidente da Empresa
            $registerPresidentData['enterprise_id'] = $insertEnterprise['lastInsertId'];
            $insertPresident = $President->createPresident($registerPresidentData);
            if (!$insertPresident['status']) {
                throw new Vtx_UserException($insertPresident['messageError']);
            }

            $registerECAC = array();
            $registerECAC['enterprise_id'] = $insertEnterprise['lastInsertId'];
            $registerECAC['competition_id'] = Zend_Registry::get('configDb')->competitionId;
            $registerECAC['category_award_id'] = $registerEnterpriseData['category_award_id'];

            $insertECAC = $modelEntCatAwardCompetition->createECAC($registerECAC);
            if (!$insertECAC['status']) {
                throw new Vtx_UserException($insertECAC['messageError']);
            }

            // 2.2 Insert Endereço da Presidente
            $registerAddressPresidentData['president_id'] = $insertPresident['lastInsertId'];
            $insertAddressPresident = $AddressPresident
                    ->createAddressPresident($registerAddressPresidentData);
            if (!$insertAddressPresident['status']) {
                throw new Vtx_UserException($insertAddressPresident['messageError']);
            }

            // 2.3 Programa do Presidente da Empresa
            if ($registerPresidentProgramData) {
                $presidentId = $insertPresident['lastInsertId'];
                $createPresidentProgram = $PresidentProgram->createPresidentProgramByPresidentId($registerPresidentProgramData, $presidentId);
                if (!$createPresidentProgram['status']) {
                    throw new Vtx_UserException($createPresidentProgram['messageError']);
                }
            }

            // 3.1 Insert Responsável pelo preenchimento - usuário do sistema
            if (isset($registerUserData['set_login_cpf']) and $registerUserData['set_login_cpf'] == '1') {
                $registerUserData['login'] = $registerUserData['cpf'];
            }
            $insertUser = $User->createUser($registerUserData);
            if (!$insertUser['status']) {
                throw new Vtx_UserException($insertUser['messageError']);
            }

            // 4.1 Insert Relação UserLocality
            $registerUserLocalityData['user_id'] = $insertUser['lastInsertId'];
            $registerUserLocalityData['enterprise_id'] = $insertEnterprise['lastInsertId'];
            $insertUserLocality = $UserLocality
                    ->createUserLocality($registerUserLocalityData);
            if (!$insertUserLocality['status']) {
                throw new Vtx_UserException($insertUserLocality['messageError']);
            }

            // Envia email com login/senha pro responsavel pelo cadastro
            $this->sendMail($registerEnterpriseData['email_default'], $registerUserData['first_name'], $registerUserData['login'], $registerUserData['keypass']);

            // 5.1 Insert User Role
            $Acl->setUserRole($insertUser['lastInsertId'], Zend_Registry::get('config')->acl->roleEnterpriseId);

            // 6.1 Log Cadastro da Empresa
            if (!$registerLogCadastro) {
                $registerLogCadastro['user_id_log'] = $insertUser['lastInsertId'];
            }
            $logCadastroEmpresa['user_id_log'] = $registerLogCadastro['user_id_log'];
            $logCadastroEmpresa['enterprise_id'] = $insertEnterprise['lastInsertId'];
            $logCadastroEmpresa['programa_id'] = Zend_Registry::get('configDb')->competitionId;
            $logCadastroEmpresa['acao'] = 'aceite';
            $insertlogCadastroEmpresa = $modelLogCadastroEmpresa
                    ->createLogCadastroEmpresa($logCadastroEmpresa);
            if (!$insertlogCadastroEmpresa['status']) {
                throw new Vtx_UserException($insertlogCadastroEmpresa['messageError']);
            }


            // end transaction externo
            Zend_Registry::get('db')->commit();
            return array(
                'status' => true,
                'lastInsertIdKey' => $insertEnterprise['lastInsertIdKey'],
            );

            //throw new Vtx_UserException("Chegou aqui - gravacao ate o fim codigo");
        } catch (Vtx_UserException $e) {
            Zend_Registry::get('db')->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage(), 'errorCode' => $e->getCode()
            );
        } catch (Exception $e) {
            Zend_Registry::get('db')->rollBack();
            throw new Exception($e);
        }
    }

    private function validCategoryAward($enterprise) {
        if (!isset($enterprise['category_award_id'])) {
            return array(
                'status' => false, 'messageError' => 'Escolha a Categoria do premio'
            );
        }
        if ($enterprise['category_award_id'] == 2) {
            if (
                    empty($enterprise['cnpj']) and
                    empty($enterprise['state_registration']) and
                    empty($enterprise['dap']) and
                    empty($enterprise['register_ministry_fisher'])
            ) {
                return array(
                    'status' => false, 'messageError' => 'Prencha um dos campos: CNPJ, IE, DAP ou Registro no Ministério da Pesca.'
                );
            }
        }
        if (($enterprise['category_award_id'] == 1) or ($enterprise['category_award_id'] == 3)) {
            if (
                    empty($enterprise['cnpj'])
            ) {
                return array(
                    'status' => false, 'messageError' => 'Prencha o CNPJ.'
                );
            }
        }
        return array(
            'status' => true
        );
    }

    public function updateEnterpriseTransaction(
    $ficha, $enterpriseRow, $addressEnterpriseRow, $presidentRow, $addressPresidentRow, $userRow
    ) {
        $User = new Model_User();
        $President = new Model_President();
        $PresidentProgram = new Model_PresidentProgram();
        //$Eligibility = new Model_Eligibility();
        $AddressEnterprise = new Model_AddressEnterprise();
        $AddressPresident = new Model_AddressPresident();
        $modelEntCatAwardCompetition = new Model_EnterpriseCategoryAwardCompetition();
        $modelLogCadastroEmpresa = new Model_LogCadastroEmpresa();
        // dados Empresa
        $registerEnterpriseData = $ficha['enterprise'];

        // dados endereço da empresa
        $registerAddressEnterpriseData = $ficha['addressEnterprise'];
        $registerAddressPresidentData = $ficha['addressPresident'];
        $registerAddressEnterpriseData['enterprise_id'] = $enterpriseRow->getId();
        $registerAddressPresidentData['president_id'] = $presidentRow->getId();
        // dados do presidente da empresa
        $registerPresidentData = $ficha['president'];
        $registerPresidentData['enterprise_id'] = $enterpriseRow->getId();

        // dados do programa do presidente da empresa
        $registerPresidentProgramData = isset($ficha['presidentProgram']) ?
                $ficha['presidentProgram'] : null;

        // dados do usuário
        $registerUserData = isset($ficha['user']) ? $ficha['user'] : array();
        $registerUserData['first_name'] = $registerPresidentData['name'];
        $registerUserData['surname'] = $registerPresidentData['nick_name'];
        $registerUserData['email'] = $registerPresidentData['email'];
        $registerUserData['login'] = $registerPresidentData['cpf'];

        $registerLogCadastro = $ficha['log_empresa'];

        $presidentId = $presidentRow->getId();

        // start transaction externo
        Zend_Registry::get('db')->beginTransaction();
        try {
            $programaId = Zend_Registry::get('configDb')->competitionId;

            // Validação Categoria do Premio
            $updateCategoryAward = $this->validCategoryAward($registerEnterpriseData);
            if (!$updateCategoryAward['status']) {
                throw new Vtx_UserException($updateCategoryAward['messageError'], 10);
            }

            // 1.1 Empresa
            $updateEnterprise = $this->updateEnterprise($enterpriseRow, $registerEnterpriseData);
            if (!$updateEnterprise['status']) {
                throw new Vtx_UserException($updateEnterprise['messageError'], 10);
            }

            // elegibilidade para questionario de diagnostico
            //$Eligibility->doDiagnosticoEligibility($updateEnterprise['row']);
            // 1.2 Endereço da Empresa
            $updateAddressEnterprise = $AddressEnterprise->updateAddressEnterprise($addressEnterpriseRow, $registerAddressEnterpriseData);
            if (!$updateAddressEnterprise['status']) {
                throw new Vtx_UserException($updateAddressEnterprise['messageError'], 10);
            }

            // validação dos campos NewsLetter da Candidata (President)
            $newsLetterValid = $President->isValidNewsletter($ficha['newsletter'], $registerPresidentData);
            if (!$newsLetterValid['status']) {
                throw new Vtx_UserException($newsLetterValid['messageError']);
            }

            // 2.1 Presidente da Empresa
            $registerPresidentData['agree'] = $presidentRow->getAgree();
            $updatePresident = $President->updatePresident($presidentRow, $registerPresidentData);
            if (!$updatePresident['status']) {
                throw new Vtx_UserException($updatePresident['messageError']);
            }

            // 2.2 Endereço da Presidente (candidata)
            $updateAddressPresident = $AddressPresident->updateAddressPresident($addressPresidentRow, $registerAddressPresidentData);
            if (!$updateAddressPresident['status']) {
                throw new Vtx_UserException($updateAddressPresident['messageError']);
            }

            // 2.3 Programa do Presidente da Empresa
            if ($registerPresidentProgramData) {
                $PresidentProgram->deleteAllPresidentProgramByPresidentId($presidentId);
                $createPresidentProgram = $PresidentProgram->createPresidentProgramByPresidentId($registerPresidentProgramData, $presidentId);
                if (!$createPresidentProgram['status']) {
                    throw new Vtx_UserException($createPresidentProgram['messageError']);
                }
            }

            $enterpriseId = $enterpriseRow->getId();
            $hasCurrentECAC = $modelEntCatAwardCompetition->hasECAC($enterpriseId, $programaId);
            if (!$hasCurrentECAC) {
                $registerECAC = array();
                $registerECAC['enterprise_id'] = $enterpriseId;
                $registerECAC['competition_id'] = $programaId;
                $registerECAC['category_award_id'] = $registerEnterpriseData['category_award_id'];

                $insertECAC = $modelEntCatAwardCompetition->createECAC($registerECAC);
                if (!$insertECAC['status']) {
                    throw new Vtx_UserException($insertECAC['messageError']);
                }

                //  Log Cadastro da Empresa - ACEITE
                $logCadastroEmpresa['user_id_log'] = $registerLogCadastro['user_id_log'];
                $logCadastroEmpresa['enterprise_id'] = $enterpriseId;
                $logCadastroEmpresa['programa_id'] = $programaId;
                $logCadastroEmpresa['acao'] = 'aceite';
                $insertlogCadastroEmpresa = $modelLogCadastroEmpresa
                        ->createLogCadastroEmpresa($logCadastroEmpresa);
                if (!$insertlogCadastroEmpresa['status']) {
                    throw new Vtx_UserException($insertlogCadastroEmpresa['messageError']);
                }
            }

            // 3.1 Usuário (nome + sobrenome)
            $updateUser = $User->updateUser($userRow, $registerUserData);
            if (!$updateUser['status']) {
                throw new Vtx_UserException($updateUser['messageError']);
            }
            // Envia email com login/senha pro responsavel pelo cadastro
            if (APPLICATION_ENV != 'development') {
                $pass = isset($registerUserData['keypass']) ?
                        $registerUserData['keypass'] : null;
                $this->sendMailEdit(
                        $registerEnterpriseData['email_default'], $registerUserData['first_name'], $registerUserData['login'], $pass
                );
            }

            //  Log Cadastro da Empresa - EDICAO CADASTRO
            $logCadastroEmpresa['user_id_log'] = $registerLogCadastro['user_id_log'];
            $logCadastroEmpresa['enterprise_id'] = $enterpriseId;
            $logCadastroEmpresa['programa_id'] = $programaId;
            $logCadastroEmpresa['acao'] = 'edicao_cadastro';
            $insertlogCadastroEmpresaCad = $modelLogCadastroEmpresa
                    ->createLogCadastroEmpresa($logCadastroEmpresa);
            if (!$insertlogCadastroEmpresaCad['status']) {
                throw new Vtx_UserException($insertlogCadastroEmpresaCad['messageError']);
            }

            // update
            // end transaction externo
            Zend_Registry::get('db')->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            Zend_Registry::get('db')->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage(), 'errorCode' => $e->getCode()
            );
        } catch (Exception $e) {
            Zend_Registry::get('db')->rollBack();
            throw new Exception($e);
        }
    }

    public function getAllEnterpriseByRegionalServiceArea($regionalId, $count = null, $offset = null, $filter = null) {
        $modelServiceArea = new Model_ServiceArea();
        $dbTable_Enterprise = new DbTable_Enterprise();

        $sa = $modelServiceArea->getAllServiceAreaByRegionalId($regionalId);

        $indice = $sa['indice'];
        $getAllEnterpriseQuery = $dbTable_Enterprise->getAllByColAE($sa['value'][$indice], $indice, null, 'select', $filter);

        return Zend_Paginator::factory($getAllEnterpriseQuery)
                        ->setItemCountPerPage($count ? $count : null)
                        ->setCurrentPageNumber($offset ? $offset : 1);
    }

    public function getEnterpriseByUserId($userId) {
        return DbTable_Enterprise::getInstance()->getEnterpriseByUserId($userId);
    }

    public function regeneratePassword($enterpriseIdKey) {
        $enterpriseRow = $this->getEnterpriseByIdKey($enterpriseIdKey);
        $userRow = $enterpriseRow->getUserRow();
        $userId = $userRow->getId();
        $updatePass = $this->setPassword($userId, $userRow);

        if ($updatePass['status']) {

            $resEnvio = $this->sendEmailLostPassword(
                    $userRow->getEmail(), $updatePass['userObj']->getNomeCompleto(), $updatePass['userObj']->getLogin(), $updatePass['senhaRandon']
            );
            if ($resEnvio) {
                return 'senha_enviada_para_email_cadastrado';
            }
            return 'senha_nao_enviada_para_email_cadastrado';
        }
        return 'problema_password_nao_atualizado';
    }

    /**
     * 
     * Esqueci minha Senha
     * 
     * @param type $data
     * @return string
     */
    public function lostPassword($data) {
        //select usuario cadastrado, via CNPJ e email        

        $email = $data['user']['email'];
        $login = $data['user']['login'];

        $UserLost = new Model_User();


        //verifica se Login e Email existem
        $resultUser = $UserLost->getUserByLoginAndEmail($login, $email);

        if ($resultUser['status'] == true) {
            //Login e Email existem
            //se existir é feito update nos campos Keypass e Salt
            //echo "usuario existe banco dados";
            //altero pass do banco dados e preparo para enviar por email ao cliente
            $updatePass = $this->setPassword($resultUser['lastInsertId']);

            //se alteracao de senha esta correta, faz envio email para cliente
            if ($updatePass['status']) {

                $resEnvio = $this->sendEmailLostPassword($updatePass['userObj']->getEmail(), $updatePass['userObj']->getFirstName(), $updatePass['userObj']->getLogin(), $updatePass['senhaRandon']);
                if ($resEnvio) {
                    $msg = "senha_enviada_para_email_cadastrado";
                } else {
                    $msg = "senha_nao_enviada_para_email_cadastrado";
                }
            } else {
                $msg = "problema_password_nao_atualizado";
            }
        } else {
            //Login e Email nao existem

            $msg = "usuario_nao_existe";
        }

        //chama envio email com senha nova para o cleinte
        /**
          @return boolean;
         */
        return $msg;
    }

    /**
     * altera password do usuario
     * 
     * @param type $IdUser
     * @return type
     */
    public function setPassword($IdUser) {

        $senhaRandon = substr(uniqid(), -8); //pass com 8 caracteres
        $senhaToMd5 = md5($senhaRandon);
        $pass = Vtx_Util_String::hashMe($senhaToMd5);

        $keypass = $pass['sha'];
        $salt = $pass['salt'];

        $User = new Model_User();
        $userRowData = $User->getUserById($IdUser);

        $userRowData->setKeypass($keypass);
        $userRowData->setSalt($salt);
        $userRowData->save();

        return array('status' => true, 'senhaRandon' => $senhaRandon, 'userObj' => $userRowData);
    }

    public function sendEmailLostPassword($to, $firstName, $login, $pass) {
        $from = Zend_Registry::get('config')->util->emailSescoop;
        $bcc = Zend_Registry::get('config')->util->emailBcc; // 'projeto.sescoop.fnq@vorttex.com.br';
        $subject = 'Mulher de Negócios 2014 :: Nova senha.';
        $message = 'Olá ' . $firstName . ', <br /><br />Nova senha cadastrada.<br />Login: ' . $login . '<br />Senha: ' . $pass;

        $eQueue = new Model_EmailQueue();
        $eQueue->setEmailQueue($to, $from, $subject, $message, '', 'ESPERA');

        $resultSend = true;
        return $resultSend;
    }

}
