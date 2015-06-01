<?php

class Questionnaire_RegisterController extends Vtx_Action_Abstract
{
    protected $modelQuestionnaire;
    protected $modelEnterpriseCategoryAwardCompetition;

    public function init()
    {
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('index', array('json'))
            ->addActionContext('edit', array('json'))
            ->setAutoJsonSerialization(true)
            ->initContext();
        $this->userLogged = Zend_Auth::getInstance()->getIdentity();
        $this->City = new Model_City();
        $this->State = new Model_State();
        $this->Metier = new Model_Metier();
        $this->Position = new Model_Position();
        $this->Education = new Model_Education();
        $this->Enterprise = new Model_Enterprise();
        $this->Neighborhood = new Model_Neighborhood();
        $this->modelPresidentProgramType = new Model_PresidentProgramType();
        $this->modelEnterpriseCategoryAwardCompetition = new Model_EnterpriseCategoryAwardCompetition();
        $this->modelQuestionnaire = new Model_Questionnaire();
    }
    
    public function indexAction()
    {
        $Acl = Zend_Registry::get('acl'); 
        $auth = Zend_Auth::getInstance();
        $this->view->getAllStates = $this->State->getAll();
        $this->view->getAllPositions = $this->Position->getAll();
        $this->view->getAllEducations = $this->Education->getAll();
        $this->view->getAllMetier = $this->Metier->getAll();
        $this->view->getAllPresidentProgramType = $this->modelPresidentProgramType->getAll();
        $this->view->hasECAC = false;
        $this->view->editStatus = false;
        $this->view->subscriptionPeriodIsClosed = !$this->subscriptionPeriodIsOpen();
        
        // caso primeiro cadastro apos busca do login
        if ($this->_getParam('forward', null) == 'true' and !$this->_getParam('id')) {
            $this->view->registerPresidentData = array('cpf' => $this->_getParam('cpf', null));
            return;
        }
        
        
        if ($auth->hasIdentity()) {
            if ($Acl->isAllowed($auth->getIdentity()->getRole(), 'questionnaire:register', 'publisher')) {
                $this->view->editStatus = true;
                $logCadastroEmpresa['user_id_log'] = $this->userLogged->getUserId();
            }
        }
        
        if (!$this->getRequest()->isPost()) {
            $this->_helper->_layout->setLayout('new-qstn');
            return;
        }
        unset($this->view->getAllStates);
        unset($this->view->getAllPositions);
        unset($this->view->getAllEducations);
        unset($this->view->getAllMetier);
        unset($this->view->getAllPresidentProgramType);
        unset($this->view->hasECAC);
        unset($this->view->editStatus);

        if($this->view->subscriptionPeriodIsClosed){
            $this->view->messageError = 'Não é possível cadastrar uma nova candidata: As inscrições foram encerradas.';
            return;
        }
        
        $ficha = $this->_getAllParams();
        if (isset($logCadastroEmpresa)) {
            $ficha['log_cadastro_empresa'] = $logCadastroEmpresa;
        }

        $createEnterpriseTransaction = $this->Enterprise
            ->createEnterpriseTransaction($ficha);
        if (!$createEnterpriseTransaction['status']) {
            $this->view->messageError = $createEnterpriseTransaction['messageError'];
            $this->view->errorCode = $createEnterpriseTransaction['errorCode'];
            return;
        }

        $this->view->enterpriseIdKey = $enterpriseIdKey = $createEnterpriseTransaction['lastInsertIdKey'];
        $this->view->itemSuccess = true;

        $emailEnterprise = isset($ficha['enterprise']['email_default']) ? $ficha['enterprise']['email_default'] : '';
        $socialName = $ficha['enterprise']['social_name'];
        $cnpj = $ficha['enterprise']['cnpj'];

        if($this->view->itemSuccess && ($emailEnterprise == null || $emailEnterprise == '')){
            $stateId = $ficha['addressEnterprise']['state_id'];
            $this->sendWhiteListMail($stateId, $socialName, $cnpj);
        }

        $hasEligibility = $this->Enterprise->hasEligibilityRules($enterpriseIdKey);

        if (!$auth->hasIdentity()) {
            $this->view->loadUrlRegister = $this->view
                ->baseUrl('/questionnaire/register/success/itemInsertSuccess/true/cpf/' 
                        . $ficha['president']['cpf']
                        .'/hasEligibility/'.$hasEligibility
                );
            //$this->view->redirectUrlRegister = $this->view->baseUrl('/login');
            return;
        }
        
        if ($Acl->isAllowed($auth->getIdentity()->getRole(), 'questionnaire:register', 'publisher')) {
            $this->view->loadUrlRegister = $this->view
                ->baseUrl('/management/enterprise/success/itemInsertSuccess/true/social_name/'
                    .urlencode($ficha['enterprise']['social_name'])
                    .'/enterpriseIdKey/'.$enterpriseIdKey
                    .'/hasEligibility/'.$hasEligibility
                    );
            return;
        }
        
    }
    
    public function acompanhacadastroAction()
    {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $this->editAction();
    }
    
    /*
     * Função para editar os dados da empresa.
     */
    public function editAction()
    {
        $Acl = Zend_Registry::get('acl');
        
        $auth = Zend_Auth::getInstance();
        $User = new Model_User();
        $President = new Model_President();
        $PresidentProgram = new Model_PresidentProgram();
        $modelLogCadastroEmpresa = new Model_LogCadastroEmpresa();
        
        $UserLocality = new Model_UserLocality();
        $AddressEnterprise = new Model_AddressEnterprise();
        $AddressPresident = new Model_AddressPresident();
        $this->_helper->viewRenderer->setRender('index');
        
        $this->view->editStatus = false;
        $this->view->editByAdmin = false;

        if (  $this->_getParam('id_key') and (
                $Acl->isAllowed($this->userLogged->getRole(), 'questionnaire:register', 'publisher')
                or $Acl->isAllowed($this->userLogged->getRole(), 'questionnaire:register', 'acompanhacadastro')
            )
        ) {
            $enterpriseIdKey = $this->_getParam('id_key');
            $enterpriseRow = $this->Enterprise->getEnterpriseByIdKey($enterpriseIdKey);

            $userLocalityGetEnterprise = $UserLocality->getUserLocalityByEnterpriseId($enterpriseRow->getId());
            if (!$userLocalityGetEnterprise) {
                throw new Exception('Nenhum usuário relacionado nesta empresa.');
            }
            $this->view->editStatus = true;
            $this->view->enterpriseIdKey = $enterpriseIdKey;
            $userId = $userLocalityGetEnterprise->getUserId();
            $this->view->editByAdmin = true;
        } else {
            $userId = $this->userLogged->getUserId();
        }
        
        $userRow = $User->getUserById($userId);
        $userLocalityRow = $UserLocality
            ->getUserLocalityByUserId($userRow->getId());
        $enterpriseRow = $this->Enterprise
            ->getEnterpriseById($userLocalityRow->getEnterpriseId());
        $presidentRow = $President
            ->getPresidentByEnterpriseId($enterpriseRow->getId());
        $addressPresidentRow = $AddressPresident
            ->getAddressPresidentByPresidentId($presidentRow->getId());
        $presidentProgramRow = $PresidentProgram
            ->getAllPresidentProgramByPresidentId($presidentRow->getId());
        $addressEnterpriseRow = $AddressEnterprise
            ->getAddressEnterpriseByEnterpriseId($enterpriseRow->getId());
        $logCadastradoPor = $modelLogCadastroEmpresa
            ->getLogCadastroEmpresaByEnterpriseId($enterpriseRow->getId());
        $cadastroNome = 'Site';
        $cadastroCriadoEm = '00:00:00';
        if ($logCadastradoPor) {
            if ($logCadastradoPor->getUserIdLog() != $userLocalityRow->getUserId()) {
                $cadastroNome = $User->getUserById($logCadastradoPor->getUserIdLog())->getFirstName();
            }
            $cadastroCriadoEm = $logCadastradoPor->getCriadoEm();
        }
        $this->view->logCadastroEmpresa = array(
            'NomeCadastro'  => $cadastroNome,
            'CriadoEm'      => $cadastroCriadoEm,
        );
        
        if (!$enterpriseRow || !$presidentRow) {
            throw new Exception('Usuário inválido, não encontrado.');
        }

        $this->view->userIdview = $userId;
        $this->view->enterpriseIdview = $enterpriseRow->getId();
        
//        $modelQuest = new Model_Questionnaire();
//        
//        $arrTerminoEtapas = $modelQuest->terminoEtapas($enterpriseRow, $userId);
//        
//        var_dump($arrTerminoEtapas);
        
        
        $this->view->getAllStates = $this->State->getAll();

        if($addressEnterpriseRow){
            $this->view->getAllCities = $this->City->getAllCityByStateId($addressEnterpriseRow->getStateId());
            //$this->view->getAllNeighborhoods = $this->Neighborhood->getAllNeighborhoodByCityId($addressEnterpriseRow->getCityId());
            $this->view->getAllNeighborhoods = ($addressEnterpriseRow->getCityId()) ? $this->Neighborhood->getAllNeighborhoodByCityId($addressEnterpriseRow->getCityId()):null;
        }

        if($addressPresidentRow){
            $this->view->getAllCitiesPresident = $this->City->getAllCityByStateId($addressPresidentRow->getStateId());
            //$this->view->getAllNeighborhoodsPresident = $this->Neighborhood->getAllNeighborhoodByCityId($addressPresidentRow->getCityId());
            $this->view->getAllNeighborhoodsPresident = ($addressPresidentRow->getCityId()) ? $this->Neighborhood->getAllNeighborhoodByCityId($addressPresidentRow->getCityId()):null;
        }
        
        $this->view->getAllPositions = $this->Position->getAll();
        $this->view->getAllEducations = $this->Education->getAll();
        $this->view->getAllMetier = $this->Metier->getAll();
        $this->view->getAllPresidentProgramType = $this->modelPresidentProgramType->getAll();

        
        $this->view->hasECAC = $this->modelEnterpriseCategoryAwardCompetition
            ->hasECAC($enterpriseRow->getId(),Zend_Registry::get('configDb')->competitionId);

        $hasntEmail = $enterpriseRow->getHasntEmail();
        $hasntEmail = isset($hasntEmail) ? $hasntEmail : 1;

        $this->view->registerEnterpriseData = array(
            'cnpj'                  => $enterpriseRow->getCnpj(),
            'category_award_id'     => $enterpriseRow->getCategoryAwardId(),
            'category_sector_id'    => $enterpriseRow->getCategorySectorId(),
            'state_registration'    => $enterpriseRow->getStateRegistration(),
            'dap'                   => $enterpriseRow->getDap(),
            'register_ministry_fisher'=> $enterpriseRow->getRegisterMinistryFisher(),
            'company_history'        => $enterpriseRow->getCompanyHistory(),
            'site'                  => $enterpriseRow->getSite(),
            'status'                => $enterpriseRow->getStatus(),
            'social_name'           => $enterpriseRow->getSocialName(),
            'fantasy_name'          => $enterpriseRow->getFantasyName(),
            'creation_date'         => $enterpriseRow->getCreationDate(),
            'employees_quantity'    => $enterpriseRow->getEmployeesQuantity(),
            'phone'                 => $enterpriseRow->getPhone(),
            'email_default'         => $enterpriseRow->getEmailDefault(),
            'annual_revenue'        => $enterpriseRow->getAnnualRevenue(),
            'cnae'                  => $enterpriseRow->getCnae(),
            'nirf'                  => $enterpriseRow->getNirf(),
            'farm_size'             => $enterpriseRow->getFarmSize(),
            'hasnt_email'           => $hasntEmail
        );

        if($addressEnterpriseRow){
            $this->view->registerAddressEnterpriseData = array(
                'cep'               => $addressEnterpriseRow->getCep(),
                'state_id'          => $addressEnterpriseRow->getStateId(),
                'city_id'           => $addressEnterpriseRow->getCityId(),
                'name_full_log'     => $addressEnterpriseRow->getStreetNameFull(),
                'street_number'     => $addressEnterpriseRow->getStreetNumber(),
                'street_completion' => $addressEnterpriseRow->getStreetCompletion(),
                'neighborhood_id'   => $addressEnterpriseRow->getNeighborhoodId()
            );
        }

        $this->view->registerPresidentData = array(
            'enterprise_id'     => $presidentRow->getEnterpriseId(),
            'education_id'      => $presidentRow->getEducationId(),
            'position_id'       => $presidentRow->getPositionId(),
            'find_us_id'        => $presidentRow->getFindUsId(),
            'nick_name'         => $presidentRow->getNickName(),
            'cellphone'         => $presidentRow->getCellphone(),
            'newsletter_email'  => $presidentRow->getNewsletterEmail(),
            'newsletter_mail'   => $presidentRow->getNewsletterMail(),
            'newsletter_sms'    => $presidentRow->getNewsletterSms(),
            'agree'             => $presidentRow->getAgree(),
            'name'              => $presidentRow->getName(),
            'cpf'               => $presidentRow->getCpf(),
            'email'             => $presidentRow->getEmail(),
            'phone'             => $presidentRow->getPhone(),
            'born_date'         => $presidentRow->getBornDate(),
            'gender'            => $presidentRow->getGender(),
        );

        if($addressPresidentRow){
            $this->view->registerAddressPresidentData = array(
                'cep'               => $addressPresidentRow->getCep(),
                'state_id'          => $addressPresidentRow->getStateId(),
                'city_id'           => $addressPresidentRow->getCityId(),
                'name_full_log'     => $addressPresidentRow->getStreetNameFull(),
                'street_number'     => $addressPresidentRow->getStreetNumber(),
                'street_completion' => $addressPresidentRow->getStreetCompletion(),
                'neighborhood_id'   => $addressPresidentRow->getNeighborhoodId()
            );
        }

        $this->view->registerPresidentProgramData = $presidentProgramRow;
        
        $this->view->registerUserData = array(
            'first_name'    => $userRow->getFirstName(),
            'surname'       => $userRow->getSurname(),
            'password_hint' => $userRow->getPasswordHint(),
        );
        
        $this->view->hasEligibility = $hasEligibility = $this->Enterprise->hasEligibilityRules($enterpriseRow->getIdKey());
        
        if ($this->_getParam('forward',null) == true) {
            return;
        }
        
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $ficha = $this->_getAllParams();

        $ficha['log_empresa']['user_id_log'] = $this->userLogged->getUserId();
        unset($this->view->editStatus);
        unset($this->view->getAllStates);
        unset($this->view->getAllCities);
        unset($this->view->getAllNeighborhoods);
        unset($this->view->getAllCitiesPresident);
        unset($this->view->getAllNeighborhoodsPresident);
        unset($this->view->getAllPositions);
        unset($this->view->getAllEducations);
        unset($this->view->getAllMetier);
        unset($this->view->getAllPresidentProgramType);
        unset($this->view->hasECAC);
        unset($this->view->registerEnterpriseData);
        unset($this->view->registerAddressEnterpriseData);
        unset($this->view->registerPresidentData);
        unset($this->view->registerAddressPresidentData);
        unset($this->view->registerPresidentProgramData);
        unset($this->view->registerUserData);
        unset($this->view->editByAdmin);
        // $this->view->registerData = $ficha;
        
        $updateEnterpriseTransaction = $this->Enterprise
            ->updateEnterpriseTransaction(
                $ficha,
                $enterpriseRow,
                $addressEnterpriseRow,
                $presidentRow,
                $addressPresidentRow,
                $userRow );
        if (!$updateEnterpriseTransaction['status']) {
            $this->view->messageError = $updateEnterpriseTransaction['messageError'];
            $this->view->errorCode = $updateEnterpriseTransaction['errorCode'];
            return;
        }
        
        $this->view->itemSuccess = true;

        $emailEnterprise = isset($ficha['enterprise']['email_default']) ? $ficha['enterprise']['email_default'] : '';
        $socialName = $ficha['enterprise']['social_name'];
        $cnpj = $ficha['enterprise']['cnpj'];

        if($this->view->itemSuccess && ($emailEnterprise == null || $emailEnterprise == '')){
            $stateId = $ficha['addressEnterprise']['state_id'];
            $this->sendWhiteListMail($stateId, $socialName, $cnpj);
        }

        $hasEligibility = $this->Enterprise->hasEligibilityRules($enterpriseRow->getIdKey());
        if ($Acl->isAllowed($auth->getIdentity()->getRole(), 'questionnaire:register', 'publisher')) {
            $this->view->loadUrlRegister = $this->view
                ->baseUrl('/management/enterprise/success/itemEditSuccess/true/social_name/'
                    .urlencode($ficha['enterprise']['social_name'])
                    .'/enterpriseIdKey/'.$enterpriseIdKey
                    .'/hasEligibility/'.$hasEligibility
                    );
            return;
        }
        
        if ($Acl->isAllowed($auth->getIdentity()->getRole(), 'questionnaire:register', 'index')) {
            $this->view->loadUrlRegister = $this->view->baseUrl('/questionnaire/register/success/itemEditSuccess/true/hasEligibility/'.$hasEligibility);
            return;
        }
    }

    public function sendWhiteListMail($stateId, $socialName, $cnpj) {
        $context = 'register_without_email_notification';
        $searches = array(':date',':name',':cnpj');
        $replaces = array(date('d/m/Y'), $socialName, $cnpj);
        $recipients = self::getWhiteListEmailRecipient($stateId);

        return Manager_EmailMessage::createByEmailDefinitionWithRecipients($context, $searches, $replaces, $recipients);
    }

    public function getWhiteListEmailRecipient($stateId){
        $modelStateEmailManager = new Model_StateManagerEmail();
        $stateEmails = $modelStateEmailManager->getByStateId($stateId);

        $recipients = array();

        foreach($stateEmails as $stateEmail) {
            $recipients[] = $stateEmail->getEmail();
        }

        return $recipients;
    }

    public function successAction() // success
    {
        $this->_helper->layout()->disableLayout();
        $params = $this->_getAllParams();
        
        if (isset($params['hasEligibility']) and $params['hasEligibility']) {
            $this->view->hasEligibility = $params['hasEligibility'];
        }
        
        if(isset($params['itemInsertSuccess']))
        {
            $this->view->itemInsertSuccess = $params['itemInsertSuccess'];
            $this->view->cpf = isset($params['cpf'])? $params['cpf'] : '';
        }
        
        if (isset($params['itemEditSuccess'])) 
        {
            $this->view->itemEditSuccess = $params['itemEditSuccess'];
        }
        //$this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isPost()) {
            return;
        }
    }
 
    /**
     * Action para selecao e busca do CNAE da empresa
     * 
     * @author esilva
     * @return boolean
     */
    public function cnaeAction()
    {        
        $this->_helper->layout()->disableLayout();
        $modelCNAE = new Model_CNAE();
        
        //action form
        $this->view->formAction = $this->_getParam('envia');
        
        $this->view->result = '';
        
        $fullTextSearch = $this->_getParam('busca');
        
        //dados do formulario enviados
        if ($this->view->formAction == 's') 
        {
            $this->view->palavraBusca = $fullTextSearch;
            //var_dump('cnae: ',$modelCNAE->searchCNAE($fullTextSearch));
            $this->view->result = $modelCNAE->searchCNAE($fullTextSearch);
        }

        return true;
        
    }

    public function verifyAction()
    {
        $token = $this->_getParam('token');
        $competitionId = $this->modelQuestionnaire->getCurrentExecution()->getCompetitionId();

        $this->view->success = '';

        if($this->modelEnterpriseCategoryAwardCompetition->getECACByTokenAndYear($token, $competitionId)){
            $this->modelEnterpriseCategoryAwardCompetition->updateECACVerifiedByToken($token);
            $this->view->success = true;
        }
        else{
            $this->view->success = false;
        }
    }

    private function subscriptionPeriodIsOpen(){
        return $this->modelQuestionnaire->subscriptionPeriodIsOpenFor(null,$this->userLogged);
    }
}