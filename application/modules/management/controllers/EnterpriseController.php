<?php

/**
 * Controller_Enterprise
 * @uses
 *
 */
class Management_EnterpriseController extends Vtx_Action_Abstract {
    protected $Enterprise;
    protected $EmailMessage;
    protected $WinningNotification;
    protected $modelQuestionnaire;
    protected $ApeEvaluationVerificador;

    public function init() {
        $this->_helper->getHelper('ajaxContext')
                ->addActionContext('do-premio-eligibility', array('json'))
                ->addActionContext('comunicacao', array('json'))
                /* Classificar para candidata */
                ->addActionContext('do-classificar', array('json'))
                ->addActionContext('do-desclassificar', array('json'))
                /* Classificar para verifição */
                ->addActionContext('do-classificar-verificacao', array('json'))
                ->addActionContext('do-desclassificar-verificacao', array('json'))
                /* Classificar para final */
                ->addActionContext('do-classificar-finalista', array('json'))
                ->addActionContext('do-desclassificar-finalista', array('json'))
                ->addActionContext('verify', array('json'))
                ->initContext();

        $this->modelCompetition = new Model_Competition;
        $this->Enterprise = new Model_Enterprise();
        $this->Eligibility = new Model_Eligibility;
        $this->aclModel = Zend_Registry::get('acl');
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->modelUser = new Model_User;
        $this->dbTableUser = new DbTable_User;
        $this->Education = new Model_Education;
        $this->modelPosition = new Model_Position;
        $this->EmailMessage = new Model_EmailMessage;
        $this->WinningNotification = new Model_WinningNotification;
        $this->State = new Model_State;
        $this->modelQuestionnaire = new Model_Questionnaire();
        $this->AppraiserVerificador = new Model_ApeEvaluationVerificador();        
        $this->ApeEvaluationVerificador = new Model_ApeEvaluationVerificador();
        
        
        $this->autoavaliacaoId = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;
        $this->roleAppraiserId = Zend_Registry::get('config')->acl->roleAppraiserId;
        $this->roleVerificadorId = Zend_Registry::get('config')->acl->roleVerificadorId;
        $this->showAppraisersFilter = false;
        $this->competitionId = Zend_Registry::get('configDb')->competitionId;
        //$this->pointsRanking = new Model_PointsRanking();
    }

    public function indexAction() {
        
        if (!isset($this->tipoRelatorio)) {
            $this->tipoRelatorio = 'inscricoes';
        }

        $State = new Model_State;
        $City = new Model_City;
        $model_Metier = new Model_Metier;
        $dbTable_Regional = new DbTable_Regional;
        $modelUserLocality = new Model_UserLocality;
        $modelRegional = new Model_Regional;
        $Neighborhood = new Model_Neighborhood;
        $Regiao = new Model_Regiao;
        $modelEnterpriseCategoryAward = new Model_EnterpriseCategoryAward;
        $enterprise = new Model_Enterprise();
        $ApeEvaluationVerificador = new Model_ApeEvaluationVerificador();
                
        $format = $this->_getParam('format');
        $this->view->getAllEducations = $this->Education->getAll();
        if ($format == 'csv') {
            //$this->view->getAllPosition = $this->modelPosition->getAll();
            $this->_dorelatorio();
            $this->incluirJoinPontuacao = '1';
            $this->incluirJoinRegional = '1';
        }
        
        set_time_limit(720); //6 minutos

        $ns = new Zend_Session_Namespace('respond');
        $ns->enterpriseUserId = null;
        //constroi url para CSV
        $this->view->urlParaGerarCsv = $this->_construirUrlParaCsv();

        $this->view->getAllEnterpriseCategoryAward = $modelEnterpriseCategoryAward->getAll();
        $this->view->getAllRegiao = $Regiao->getAll();
        $this->view->getAllMetier = $model_Metier->getAll();

        $this->view->isRanking = isset($this->view->isRanking) ? $this->view->isRanking : false;
        $this->filterAdditional = isset($this->filterAdditional) ?
        $this->filterAdditional : $this->_getParam('filter');

        $filter = $this->filterAdditional;
        $this->view->filter = $filter;
        $this->view->hasFilterRegional = true;
        $this->view->hasFilterStatus = true;

        $this->view->getAllStates = $State->getAll();
        if (isset($filter['state_id']) and !empty($filter['state_id'])) {
            $this->view->getAllCities = $City->getAllCityByStateId($filter['state_id']);
        }
        if (isset($filter['city_id']) and !empty($filter['city_id'])) {
            $this->view->getAllNeighborhoods = $Neighborhood->getAllNeighborhoodByCityId($filter['city_id']);
        }

        $this->view->competitionId = Zend_Registry::get('configDb')->competitionId;
        $this->view->getAllCompetition = $this->modelCompetition->getAllCompetition();

        $userLoggedRow = $this->userAuth;
        //$roleRow = $this->aclModel->getRoleById($userLoggedRow->getRoleId(), false);

        $page = $this->_getParam('page');
        $count = $this->_getParam('count', 10);

        $this->orderBy = isset($this->orderBy) ? $this->orderBy : null;
        $orderBy = $this->view->orderBy = $this->_getParam('orderBy', $this->orderBy);

        $filter['appraiser_id'] = isset($filter['appraiser_id']) ? $filter['appraiser_id'] : null;
        $filter['incluir_join_pontuacao'] = isset($this->incluirJoinPontuacao) ? $this->incluirJoinPontuacao : '0';
        $filter['incluir_join_regional'] = isset($this->incluirJoinRegionalForce) ?($this->incluirJoinRegionalForce) : (isset($this->incluirJoinRegional)?$this->incluirJoinRegional : '0') ;

        $this->view->regionalOption = 'Minha';

        // List Coop by Regional
        $regionalId = $modelUserLocality->getUserLocalityByUserId($userLoggedRow->getUserId())->getRegionalId();

        $this->view->getAllRegional = $getAllRegional = $dbTable_Regional
                ->getAllRegionalByOneRegionalServiceArea(null, $regionalId, 'all', $filter); // $this->roleAppraiserId
        if (isset($filter['regional_id']) and $filter['regional_id']) {
            $regionalId = $filter['regional_id'];
        }

        if ($this->showAppraisersFilter) {
            //$filterAp['status'] = 'A'; ,$filterAp
            $this->view->getAllAppraisers = $this->dbTableUser->getAllAppraiserByRegionalServiceArea(null, $regionalId, array('appraiser_status' => 'able'));
        }

        if (!$format) {
            return;
        }

        $groupBy = in_array($this->tipoRelatorio, array('inscricoes', 'ranking', 'classificadas','finalistas')) ? 'enterprise_id' : null;

        $this->regionalId = $regionalId;
        $this->filter = $filter;
        $this->paramsBuscaServiceArea = ($regionalId) ?
                $modelRegional->getServiceAreaByRegionalId($regionalId) :
                array(0 => null, 1 => null);
        $fetchReturn = isset($this->fetchReturnForce) ? $this->fetchReturnForce : (isset($this->fetchReturn) ? $this->fetchReturn : 'paginator' );

        if($this->tipoRelatorio != 'inscricoes' || $format == 'csv'){
            
            $this->view->getAllEnterprise = $this->Enterprise->getAllByColAE(
                $this->paramsBuscaServiceArea[0], 
                $this->paramsBuscaServiceArea[1], 
                $this->autoavaliacaoId, 
                $count, $page, $filter, $orderBy, 
                $format, $fetchReturn, 
                $this->tipoRelatorio, $groupBy
            );
        } else {
            $loggedUserId = $this->userAuth->getUserId();
            $this->view->getAllEnterprise = $this->Enterprise->getPaginatorForSubscriptions($loggedUserId, $filter, $count, $page);
        }

        $this->view->modelEntCategoryAward = new Model_EnterpriseCategoryAwardCompetition();
                
    }

    /**
     * renderiza CSV para Listagem empresas
     *
     * usa view /enterprise/index.csv.phtml
     */
    private function _dorelatorio() {
        $this->fetchReturn = isset($this->fetchReturnForce) ? $this->fetchReturnForce : 'all';
        ini_set("memory_limit", "1200M");

        $filename = 'PSMN' . time() . '.csv';

        $getActionName = $this->getRequest()->getActionName();

        $this->_helper->getHelper('contextSwitch')
                ->addContext('csv', array('suffix' => 'csv',
                    'headers' => array(
                        'Content-Encoding' => 'UTF-8',
                        'Content-Type' => 'application/csv;charset=UTF-8',
                        'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                        'Pragma' => 'no-cache', 'Expires' => '0'
                    )
                ))
                ->addActionContext($getActionName, array('csv'))
                ->initContext();
    }

    public function filesAction() {

    }

    public function rankingAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $filter['devolutiva'] = '2';
        $this->filterAdditional = $filter;
        $this->incluirJoinPontuacao = '1';
        $this->incluirJoinRegionalForce = '1';
        $this->orderBy = 'NegociosTotal DESC';
        $this->temAvaliador = true;
        $this->showAppraisersFilter = true;
        $this->view->isRanking = true;
        $this->tipoRelatorio = 'ranking';
        $this->_helper->viewRenderer->setRender('index');
        self::indexAction();

        $limit = $this->_getParam('count', 10);
        $offset = $this->_getParam('page', 1);

        $format = $this->_getParam('format');
        if (!$format || $format == 'csv') {
            return;
        }

        if (isset($this->temAvaliador) and $this->temAvaliador) {
            unset($this->filter['cpf']);
            $this->view->getAllAppraiser = $this->modelUser->getAllAppraiserByRegionalServiceArea(
                    $this->roleAppraiserId, $this->regionalId, $this->filter, null, null, 'all'
            );
        }

        if(isset($filter['competition_id'])) {
            $userId = $this->userAuth->getUserId();

            if($format == 'csv'){
                $this->view->getAllEnterprise = $this->Enterprise->getAllForStateCandidates($userId, $filter);
            } else {
                $this->view->getAllEnterprise = $this->Enterprise->getPaginatorForStateCandidates($userId, $filter, $limit, $offset);
            }
        } else {
            $this->view->getAllEnterprise = array();
        }
    }

    public function candidatasNacionalAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $filter['devolutiva'] = '2';
        $this->filterAdditional = $filter;
        $this->incluirJoinPontuacao = '1';
        $this->orderBy = 'NegociosTotal DESC';
        $this->temAvaliador = true;
        $this->showAppraisersFilter = true;
        $this->view->isRanking = true;
        $this->tipoRelatorio = 'candidatas-nacional';
        $this->_helper->viewRenderer->setRender('index');
        self::indexAction();

        $format = $this->_getParam('format');
        if (!$format) {
            return;
        }

        if (isset($this->temAvaliador) and $this->temAvaliador) {
            unset($this->filter['cpf']);
            $this->view->getAllAppraiser = $this->modelUser->getAllAppraiserByRegionalServiceArea(
                    $this->roleAppraiserId, $this->regionalId, $this->filter, null, null, 'all'
            );
        }
    }

    public function classificadasAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $filter['devolutiva'] = '2';
        $this->filterAdditional = $filter;
        $this->incluirJoinPontuacao = '1';
        $this->orderBy = 'NegociosTotal DESC';
        $this->temAvaliador = true;
        $this->view->isRanking = true;
        $this->tipoRelatorio = 'classificadas';
        $this->showAppraisersFilter = false;
        $this->_helper->viewRenderer->setRender('index');
        self::indexAction();

        $format = $this->_getParam('format');
        if (!$format) {
            return;
        }
        //verificadores
        unset($this->filter['cpf']);

        $this->filter['appraiser_status'] = 'able';

        $this->view->allCheckers = $this->modelUser->getAllAppraiserByRegionalServiceArea(
                $this->roleVerificadorId, $this->regionalId, $this->filter, null, null, 'all'
        );
    }

    public function classificadasNacionalAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $filter['devolutiva'] = '2';
        $this->filterAdditional = $filter;
        $this->incluirJoinPontuacao = '1';
        $this->orderBy = 'NegociosTotal DESC';
        $this->temAvaliador = true;
        $this->view->isRanking = true;
        $this->tipoRelatorio = 'classificadas-nacional';
        $this->showAppraisersFilter = false;
        $this->_helper->viewRenderer->setRender('index');
        self::indexAction();

        $format = $this->_getParam('format');
        if (!$format) {
            return;
        }
        //verificadores
        unset($this->filter['cpf']);

        $this->filter['appraiser_status'] = 'able';

        $this->view->allCheckers = $this->modelUser->getAllAppraiserByRegionalServiceArea(
                $this->roleVerificadorId, $this->regionalId, $this->filter, null, null, 'all'
        );
    }

    public function finalistasAction() {
        $this->incluirJoinRegionalForce = true;
        $filter = $this->_getParam('filter', null);
        $filter['devolutiva'] = '2';
        $this->filterAdditional = $filter;
        $this->incluirJoinPontuacao = '1';
        $this->orderBy = 'NegociosTotal DESC';
        $this->temAvaliador = true;
        $this->view->isRanking = true;
        $this->tipoRelatorio = 'finalistas';
        $this->showAppraisersFilter = false;
        $this->view->getAllApeEvaluationVerificador = $this->ApeEvaluationVerificador->getEnterpriseScoreAppraiserAnwserVerificadorData(1, null);
                
        $this->_helper->viewRenderer->setRender('index');        
        
        self::indexAction();

        $format = $this->_getParam('format');
        if (!$format) {
            return;
        }
    }

    public function finalistasNacionalAction() {
        
     //   $this->incluirJoinRegionalForce = false;

        $filter = $this->_getParam('filter', null);
        $filter['devolutiva'] = '2';
        $this->filterAdditional = $filter;
        $this->incluirJoinPontuacao = '1';
        $this->orderBy = 'NegociosTotal DESC';
        $this->temAvaliador = true;
        $this->view->isRanking = true;
        $this->tipoRelatorio = 'finalistas-nacional';
        $this->showAppraisersFilter = false;
        //$this->view->getAllApeEvaluationVerificador = $this->ApeEvaluationVerificador->getEnterpriseScoreAppraiserAnwserVerificadorData(1, null);
        
        $this->_helper->viewRenderer->setRender('index');
        
        self::indexAction();

        if(isset($filter['competition_id'])) {
            $userId = $this->userAuth->getUserId();
            
            $this->view->getAllEnterprise = $this->Enterprise->getAllForNationalCandidates($userId, $filter);
           foreach($this->view->getAllEnterprise as $key => $value){
               
               $IdEntrepriseNacional = $value['Id'];
               $CompetitionId = $filter['competition_id'];
               $QtdePontosFortes =  $this->ApeEvaluationVerificador->getEnterpriseCheckerEnterprisePontosFortes($IdEntrepriseNacional, $CompetitionId);
               $PontosFinal = $this->view->getAllApeEvaluationVerificador->getPontosFinal();
               $this->view->getAllEnterprisePontosFortes = $QtdePontosFortes;
               $this->view->getAllEnterprisePontosFinal = $PontosFinal;               
           }         
        } else {
            $this->view->getAllEnterprise = array();
        }
    }

    public function reportCategoriaAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $format = $this->_getParam('format');

        $render = ($format == 'csv') ? 'report-categoria' : 'index';
        $this->_helper->viewRenderer->setRender($render);

        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }

        if (!$format) {
            self::indexAction();
            return;
        }

        $this->filterAdditional = $filter;
        $this->fetchReturn = 'all';
        $this->tipoRelatorio = 'report-categoria';
        self::indexAction();

        $this->view->getAllEnterprise = $this->Enterprise->getAllForSectorsReport($this->userAuth->getUserId(), $filter);

        $this->view->dataReport = $this->view->getAllEnterprise;
    }

    public function reportCategoriaPremioAction() {


        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $filter['devolutiva'] = '3';
        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }
        $this->filterAdditional = $filter;
        $format = $this->_getParam('format');
        $render = ($format == 'csv') ? 'report-categoria-premio' : 'index';
        $this->_helper->viewRenderer->setRender($render);

        if (!$format) {
            self::indexAction();
            return;
        }

        $this->filterAdditional = $filter;

        $this->fetchReturn = 'all';
        $this->tipoRelatorio = 'report-categoria-premio';
        self::indexAction();
        $this->view->dataReport = $this->view->getAllEnterprise;
    }

    public function reportDigitadorAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }
        $format = $this->_getParam('format', null);
        $render = ($format == 'csv') ? 'report-digitador' : 'index';
        $this->_helper->viewRenderer->setRender($render);

        if (!$format) {
            self::indexAction();
            return;
        }

        $this->filterAdditional = $filter;

        $this->fetchReturn = 'all';
        $this->tipoRelatorio = 'report-digitador';
        self::indexAction();
        $this->view->dataReport = $this->view->getAllEnterprise;
    }

    public function reportRegionalAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $format = $this->_getParam('format', null);
        $render = ($format == 'csv') ? 'report-regional' : 'index';
        $this->_helper->viewRenderer->setRender($render);
        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }
        if (!$format) {
            self::indexAction();
            return;
        }

        if ($filter['mostrarRegionais'] == '2') {
            $this->fetchReturn = 'all';
            $this->tipoRelatorio = 'report-regional-estados';
        } else {
            $this->fetchReturnForce = 'select';
            $this->tipoRelatorio = 'report-regional-bairros';
        }

        self::indexAction();

        $this->filterAdditional = $filter;

        $this->view->dataReport = $this->Enterprise->getAllForRegionalsReport($this->userAuth->getUserId(), $filter);
        return;
    }

    public function insertAction() {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $paramsArray = array('itemSuccess' => 'true');
        $this->_forward('index', 'register', 'questionnaire', $params = $paramsArray);
    }

    public function editAction() {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $paramsArray = array();
        $this->_forward('edit', 'register', 'questionnaire', $paramsArray);
    }

    public function viewAction() {
        $this->_helper->viewRenderer->setRender('questionnaire');
        $enterpriseId = $this->_getParam('id');
        $enterpriseRow = $this->Enterprise->getEnterpriseById($enterpriseId);
        $this->view->enterpriseRow = $enterpriseRow;
        $tmpEligibility = $this->Enterprise->getEligibilityByEnterpriseId($enterpriseId)->getEligibility();
        $this->view->eligibilityCode = ($tmpEligibility) ? $tmpEligibility : '';
    }

    public function successAction() { // success
        $this->_helper->layout()->disableLayout();
        $params = $this->_getAllParams();

        if (isset($params['itemInsertSuccess'])) {
            $this->view->itemInsertSuccess = $params['itemInsertSuccess'];
            $this->view->social_name = $params['social_name'];
        }

        if (isset($params['itemEditSuccess'])) {
            $this->view->itemEditSuccess = $params['itemEditSuccess'];
            $this->view->social_name = $params['social_name'];
        }
        $this->view->enterpriseIdKey = $params['enterpriseIdKey'];
        //$this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isPost()) {
            return;
        }
    }

    public function appraiserAction() {

    }

    public function doClassificarAction() {
        $check = $this->_getParam('checked');

        $data = array(
            "enterprise_id_key" => $this->_getParam('idKey'),
            "classificar" => ($check == 'true') ? '1' : '0',
            "user_id" => $this->userAuth->getUserId(),
            "programa_id" => Zend_Registry::get('configDb')->competitionId,
            "enterprise_id_key" => $this->_getParam('idKey'),
            "etapa" => $this->_getParam('etapa', 'estadual'),
        );

        if ($data["programa_id"] != Zend_Registry::get('configDb')->competitionId) {
            die;
        }

        switch ($data['etapa']) {
            case 'nacional':
                /* @TODO só pode gestor nacional */
                unset($data['classificar']);
                $data['classificar_nacional'] = ($check == 'true') ? '1' : '0';
                break;
            case 'nacional-fase2':
                /* @TODO só pode gestor nacional */
                unset($data['classificar']);
                $data['classificar_fase2_nacional'] = ($check == 'true') ? '1' : '0';
                break;
            case 'nacional-fase3':
                /* @TODO só pode gestor nacional */
                unset($data['classificar']);
                $data['classificar_fase3_nacional'] = ($check == 'true') ? '1' : '0';
                break;
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $EnterpriseProgramaRank = new Model_EnterpriseProgramaRank();
        $EnterpriseProgramaRank->doRank($data);
        $this->view->itemSuccess = true;
    }

    public function doDesclassificarAction() {
        $check = $this->_getParam('checked');
        $data = array(
            "enterprise_id_key" => $this->_getParam('idKey'),
            "desclassificar" => ($check == 'true') ? '1' : '0',
            "justificativa" => $this->_getParam('justificativa'),
            "user_id" => $this->userAuth->getUserId(),
            "programa_id" => Zend_Registry::get('configDb')->competitionId,
            "etapa" => $this->_getParam('etapa', 'estadual'),
        );

        if ($data["programa_id"] != Zend_Registry::get('configDb')->competitionId) {
            die;
        }

        switch ($data['etapa']) {
            case 'nacional':
                /* @TODO só pode gestor nacional */
                unset($data['desclassificar']);
                unset($data['justificativa']);
                $data['desclassificar_nacional'] = ($check == 'true') ? '1' : '0';
                $data['motivo_desclassificado_nacional'] = $this->_getParam('justificativa');
                break;
            case 'nacional-fase2':
                /* @TODO só pode gestor nacional */
                unset($data['desclassificar']);
                unset($data['justificativa']);
                $data['desclassificar_fase2_nacional'] = ($check == 'true') ? '1' : '0';
                $data['motivo_desclassificado_fase2_nacional'] = $this->_getParam('justificativa');
                break;
            case 'nacional-fase3':
                /* @TODO só pode gestor nacional */
                unset($data['desclassificar']);
                unset($data['justificativa']);
                $data['desclassificar_fase3_nacional'] = ($check == 'true') ? '1' : '0';
                $data['motivo_desclassificado_fase3_nacional'] = $this->_getParam('justificativa');
                break;
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $EnterpriseProgramaRank = new Model_EnterpriseProgramaRank();
        $EnterpriseProgramaRank->doRank($data);
        $this->view->itemSuccess = true;
    }

    public function doClassificarVerificacaoAction() {
        /* @TODO ao classificar, verificar a empresa pode */
        $check = $this->_getParam('checked');
        $currentCompetitionId = Zend_Registry::get('configDb')->competitionId;
        $data = array(
            "enterprise_id_key" => $this->_getParam('idKey'),
            "classificado_verificacao" => ($check == 'true') ? '1' : '0',
            "user_id" => $this->userAuth->getUserId(),
            "programa_id" => $currentCompetitionId,
        );
        if ($data["programa_id"] != $currentCompetitionId) {
            die;
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $EnterpriseProgramaRank = new Model_EnterpriseProgramaRank();
        $EnterpriseProgramaRank->doRank($data);
        $this->view->itemSuccess = true;
    }

    public function doClassificarFinalistaAction() {
        /* @TODO ao classificar, verificar a empresa pode */
        $check = ($this->_getParam('checked') == 'true') ? '1' : '0';
        $data = array(
            "enterprise_id_key" => $this->_getParam('idKey'),
            "user_id" => $this->userAuth->getUserId(),
            "programa_id" => Zend_Registry::get('configDb')->competitionId,
        );
        if ($data["programa_id"] != Zend_Registry::get('configDb')->competitionId) {
            die;
        }

        $gold_index = 'classificado_ouro';
        $silver_index = 'classificado_prata';
        $bronze_index = 'classificado_bronze';

        if($this->_getParam('etapa') === 'nacional'){
            $gold_index .= '_nacional';
            $silver_index .= '_nacional';
            $bronze_index .= '_nacional';
        }

        switch ($this->_getParam('posicao')) {
            case 1:
                $data[$gold_index] = $check;
                break;
            case 2:
                $data[$silver_index] = $check;
                break;
            case 3:
                $data[$bronze_index] = $check;
                break;
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $EnterpriseProgramaRank = new Model_EnterpriseProgramaRank();
        $EnterpriseProgramaRank->doRank($data);
        $this->view->itemSuccess = true;
    }

    public function doDesclassificarVerificacaoAction() {
        /* @TODO ao classificar, verificar a empresa pode */
        $check = $this->_getParam('checked');
        $data = array(
            "enterprise_id_key" => $this->_getParam('idKey'),
            "desclassificado_verificacao" => ($check == 'true') ? '1' : '0',
            "user_id" => $this->userAuth->getUserId(),
            "programa_id" => Zend_Registry::get('configDb')->competitionId,
            "motivo_desclassificado_verificacao" => $this->_getParam('justificativa')
        );

        if ($data["programa_id"] != Zend_Registry::get('configDb')->competitionId) {
            die;
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $EnterpriseProgramaRank = new Model_EnterpriseProgramaRank();
        $EnterpriseProgramaRank->doRank($data);
        $this->view->itemSuccess = true;
    }

    public function doDesclassificarFinalistaAction() {
        /* @TODO ao classificar, verificar a empresa pode */
        $check = $this->_getParam('checked');
        $data = array(
            "enterprise_id_key" => $this->_getParam('idKey'),
            "desclassificado_final" => ($check == 'true') ? '1' : '0',
            "user_id" => $this->userAuth->getUserId(),
            "programa_id" => Zend_Registry::get('configDb')->competitionId,
            "motivo_desclassificado_final" => $this->_getParam('justificativa')
        );

        if ($data["programa_id"] != Zend_Registry::get('configDb')->competitionId) {
            die;
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $EnterpriseProgramaRank = new Model_EnterpriseProgramaRank();
        $EnterpriseProgramaRank->doRank($data);
        $this->view->itemSuccess = true;
    }

    public function doPremioEligibilityAction() {
        $enterpriseId = $this->_getParam('id');
        $eligibility = $this->_getParam('eligibility');

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $Eligibility = new Model_Eligibility;
        $premioEligibility = $Eligibility->setPremioEligibility($enterpriseId, $eligibility);

        $this->view->eligibility = $premioEligibility;
        $this->view->itemSuccess = true;
    }

    public function deleteAction() {
        $this->_helper->viewRenderer->setRender('index');
        $enterpriseId = $this->_getParam('id');
        $enterpriseRow = $this->Enterprise->getEnterpriseById($enterpriseId);
        if (!$enterpriseRow) {
            throw new Exception('Enterprise invalid.');
        }
        $delete = $this->Enterprise->deleteEnterprise($enterpriseRow);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
            return;
        }
        $this->view->itemSuccess = true;
    }

    public function cadastroAction() {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');

        $this->view->menu = $this->_getParam('menu', true);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $dados = $this->_getAllParams();
        $buscaLogin = $this->modelUser->buscarLogin($dados['username']);

        if (!$buscaLogin['status']) {
            $this->view->menu = false;
            $paramsArray = array(
                'forward' => true,
                'insertAdm' => true,
                'cpf' => ($buscaLogin['cpf']) ? $buscaLogin['cpf'] : null
            );
            //$this->view->messageError = 'CPF não encontrado';
            $this->_forward('index', 'register', 'questionnaire', $paramsArray);
            return;
        }

        if ($buscaLogin['status']) {
            $enterpriseIdKey = $this->Enterprise
                            ->getEnterpriseByUserId($buscaLogin['userRow']->getId())->getIdKey();
            $this->_redirect('management/enterprise/edit/id_key/' . $enterpriseIdKey);
            /*
              $this->view->menu = false;
              $paramsArray = array(
              'forward' => true,
              'insertAdm' => false,
              'id_key' => $this->Enterprise->getEnterpriseByUserId($buscaLogin['userRow']->getId())->getIdKey(),
              'id' => $this->Enterprise->getEnterpriseByUserId($buscaLogin['userRow']->getId())->getId(),
              );
              $this->_forward('edit', 'register', 'questionnaire', $paramsArray);
             */
            return;
        }
    }

    public function reportInscricoesAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $format = $this->_getParam('format', null);
        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }
        $render = ($format == 'csv') ? 'report-inscricoes' : 'index';
        $this->_helper->viewRenderer->setRender($render);

        if (!$format) {
            self::indexAction();
            return;
        }

        $this->filterAdditional = $filter;

        $this->fetchReturn = 'all';
        $this->tipoRelatorio = 'report-inscricoes';
        self::indexAction();
        $this->view->dataReport = $this->view->getAllEnterprise;

        $this->view->dataCategoriaReport = $this->Enterprise->getAllByColAE(
                $this->paramsBuscaServiceArea[0], $this->paramsBuscaServiceArea[1], $this->autoavaliacaoId,
                null, null, $this->filter, null, $format, 'all', 'report-inscricoes-categoria'
        );
    }

    public function reportGlobalAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $filter['show_only'] = 'candidatas';
        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }
        $format = $this->_getParam('format', null);
        $render = ($format == 'csv') ? 'report-global' : 'index';
        $this->_helper->viewRenderer->setRender($render);
        if (!$format) {
            self::indexAction();
            return;
        }

        $cacheQuestion = new Vtx_Cache_MPE_QuestionarioCache();
        $Qstn = new Model_Questionnaire();
        $alternativesByQuestion = $allAlternativeIds = array();

        $blockId = isset($filter['qstn']) ? $filter['qstn'] : Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
        //$this->view->showRadar = $blockId==Model_Devolutive::BLOCO_GESTAOEMPRESA? true : false;
        $this->view->showRadar = false;
        $questions = $cacheQuestion->BlocoECriterios($blockId, $Qstn->getCurrentQstnRow());
        $this->view->questions = $questions = $questions[$blockId];

        foreach ($questions['Criterions'] as $criterion) {
            foreach ($criterion['Questions'] as $questionId => $question) {
                $alternativesQuestion = $cacheQuestion->alternativasEQuestoes(
                        $question['QuestionId'], new Model_Alternative
                );
                $alternativesByQuestion[$questionId] = $alternativesQuestion;
                foreach ($alternativesQuestion as $alternative) {
                    $allAlternativeIds[] = $alternative['Id'];
                }
            }
        }

        $this->filterAdditional = $filter;
        $this->filterAdditional['alternativesId'] = $allAlternativeIds;
        $this->view->alternativesByQuestion = $alternativesByQuestion;

        $this->fetchReturn = 'pairs';
        $this->fetchReturnForce = 'pairs';
        $this->tipoRelatorio = 'report-global-respostas';
        self::indexAction();

        $this->view->qtRespostasAlternativa = $this->view->getAllEnterprise;

        if (!$this->view->showRadar) {
            return;
        }

        $dataCriterios = $this->Enterprise->getAllByColAE(
                $this->paramsBuscaServiceArea[0], $this->paramsBuscaServiceArea[1], $this->autoavaliacaoId,
                null, null, $this->filter, null, $format, 'assoc', 'report-global-criterios'
        );

        $dataCriteriosPorcent = array();
        $this->view->dataCriterios = $dataCriterios['total'];
        $this->view->pontuacaoMaxima = $pontuacaoMaxima = Vtx_Util_Array::pontuacaoMaximaCriteriosGestao();
        $i = 0;
        foreach ($this->view->dataCriterios as $criterio => $pontuacao) {
            if ($i <> 0) {
                $dataCriteriosPorcent[] = ((double) ($pontuacao) / (double) $pontuacaoMaxima[$i]) * 100;
            }
            $i++;
        }
        $this->view->dataCriteriosPoncent = $dataCriteriosPorcent;
    }

    public function acompanhacadastroAction() {
        $this->view->enterpriseIdKey = $enterpriseIdKey = $this->_getParam('enterprise-id-key', $this->_getParam('enterprise_id_key'));
        $paramsArray = array(
            'enterprise_id_key' => $enterpriseIdKey
        );
        $this->_forward('acompanhacadastro', 'register', 'questionnaire', $paramsArray);
    }

    /**
     * constroi url para CSV
     * @return string
     */
    private function _construirUrlParaCsv() {
        $params = $this->_getAllParams();
        $params['format'] = 'csv';
        unset($params['page']);
        unset($params['listagem']);

        return http_build_query($params);
    }

    public function reportStatusAppraiserAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $format = $this->_getParam('format', null);
        $render = ($format == 'csv') ? 'report-status-appraiser' : 'index';
        $this->_helper->viewRenderer->setRender($render);
        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }
        $this->showAppraisersFilter = true;
        if (!$format) {
            self::indexAction();
            return;
        }

        $this->filterAdditional = $filter;

        $this->fetchReturn = 'all';
        $this->tipoRelatorio = 'report-status-appraiser';
        self::indexAction();
        $this->view->dataReport = $this->view->getAllEnterprise;
        $this->view->dataStatusAppraiserReport = $this->Enterprise->getAllByColAE(
                $this->paramsBuscaServiceArea[0], $this->paramsBuscaServiceArea[1], $this->autoavaliacaoId,
                null, null, $this->filter, null, $format, 'all', 'report-status-appraiser'
        );
    }

    public function reportStatusVerificadorAction() {
        $this->incluirJoinRegionalForce = false;
        $filter = $this->_getParam('filter', null);
        $this->_helper->viewRenderer->setRender('index');
        $format = $this->_getParam('format', null);
        if (isset($filter['competition_id']) and $filter['competition_id'] != $this->competitionId) {
            throw new Exception('access denied');
            return;
        }
        $render = ($format == 'csv') ? 'report-status-verificador' : 'index';
        if ($format == 'csv') {
            $this->_helper->viewRenderer->setRender($render);
        }

        if (!$format) {
            self::indexAction();
            return;
        }

        $this->filterAdditional = $filter;

        $this->showAppraisersFilter = true;
        $this->fetchReturn = 'all';
        $this->tipoRelatorio = 'report-status-verificador';
        self::indexAction();
        $this->view->dataReport = $this->view->getAllEnterprise;
        $this->view->dataStatusAppraiserReport = $this->Enterprise->getAllByColAE(
                $this->paramsBuscaServiceArea[0], $this->paramsBuscaServiceArea[1], $this->autoavaliacaoId,
                null, null, $this->filter, null, $format, 'all', 'report-status-verificador'
        );
    }

    public function comunicacaoAction() {
        // Rodar essa query em no banco durante o deploy
        // insert into Role_Resource_Privilege (RoleId, ResourceId, Privilege) values (1, 47, 'comunicacao');
        // insert into Role_Resource_Privilege (RoleId, ResourceId, Privilege) values (34, 47, 'comunicacao');

        $filter = $this->_getParam('filter');
        $filter['user_id'] = $this->userAuth->getUserId();
        if(!isset($filter['competition_id'])) $filter['competition_id'] = Zend_Registry::get('configDb')->competitionId;

        $this->view->states = $this->State->getByUserLocality($filter['user_id']);
        $this->view->competition = $this->modelCompetition->getByYear(2014);
        $this->view->filter = $filter;

        if($this->getRequest()->isPost()) {
            $this->createWinningNotificationEmailMessages();
        } else {
            if(isset($filter['state_id'])) {
                $this->view->stateId = $filter['state_id'];
                $this->view->competitionId = $filter['competition_id'];

                $winnersFilter = $enterprisesFilter = $filter;
                $winnersFilter['only_winners'] = $enterprisesFilter['exclude_winners'] = true;

                $this->view->defaultMessage = $this->winningNotificationDefaultMessage($this->view->competitionId);
                $this->view->hasFinalists = $this->State->hasFinalists($this->view->stateId, $this->view->competitionId);
                $this->view->winners = $this->Enterprise->getAllForParticipationNotification($winnersFilter);
                $this->view->enterprises = $this->Enterprise->getAllForParticipationNotification($enterprisesFilter);
            }
        }
    }

    private function createWinningNotificationEmailMessages() {
        $this->_helper->layout()->disableLayout();

        $competitionId = $this->_getParam('competition_id', Zend_Registry::get('configDb')->competitionId);
        $stateId = $this->_getParam('state_id');

        $config = $this->winningNotificationEmailConfigurations($competitionId);
        $enterprise_ids = $this->_getParam('enterprise_ids', array());
        $message = $this->winningNotificationBodyMessage($stateId, $competitionId);
        $body = str_replace(array(':date',':body'), array(date('d/m/Y'), $message), $config['Layout']);

        $recipients = array();
        $notificationEnterprises = array();

        foreach($enterprise_ids as $enterprise_id) {
            $e = $this->Enterprise->getEnterpriseById($enterprise_id);
            if($e){
                $emailDefault = $e->getEmailDefault();

                if($emailDefault and $emailDefault != ''){
                    $recipients[] = array('Name' => $e->getFantasyName(), 'Address' => $e->getEmailDefault());
                    $notificationEnterprises[] = array('EnterpriseId' => $enterprise_id);
                }
            }
        }

        if(!empty($recipients)){
            $emailMessage = $this->EmailMessage->createWithRecipients($config['Context'], $config['SenderName'],
                $config['SenderAddress'], $config['Subject'], $body, $recipients);

            $responsibleId = $this->userAuth->getUserId();

            $this->WinningNotification->createWithEnterprises($emailMessage->getId(), $stateId, $competitionId,
                $responsibleId, $notificationEnterprises);
        }
    }

    private function winningNotificationBodyMessage($stateId, $competitionId){
        $message = str_replace("\n",'<br/>', $this->_getParam('message'));
        $finalists = $this->State->getByStateWithFinalists($stateId, $competitionId);

        if(empty($finalists)) return $message;

        $message .= '<ul>';
        foreach($finalists as $enterprise) {
            if($enterprise->getClassificadoOuro() == 1) $badge = 'Ouro';
            if($enterprise->getClassificadoPrata() == 1) $badge = 'Prata';
            if($enterprise->getClassificadoBronze() == 1) $badge = 'Bronze';
            $message .= ("<li>Troféu $badge: ".$enterprise->getFantasyName().'</li>');
        }
        $message .= '</ul>';

        return $message;
    }

    private function winningNotificationEmailConfigurations($competitionId){
        $context = 'winning_notification';
        $definitions = Zend_Registry::get('email_definitions');
        $winningNotificationDefinitions = Zend_Registry::get('email_definitions')->$context;

        $nextCompetitionId = $competitionId + 1;

        $searches = array(':currentEdition',':nextEdition');
        $replaces = array($competitionId, $nextCompetitionId);

        $body = str_replace($searches, $replaces, $winningNotificationDefinitions->body);

        return array(
            'Context' => $context,
            'SenderName' => 'PSMN',
            'SenderAddress' => 'mulherdenegocios@fnq.org.br',
            'Subject' => '[Prêmio Sebrae Mulher de Negócios] - Comunicação Resultados da Etapa Estadual 2014',
            'Layout' => $definitions->default->layout,
            'Body' => $body
        );
    }

    private function winningNotificationDefaultMessage($competitionId){
        $config = $this->winningNotificationEmailConfigurations($competitionId);
        return str_replace('<br/>',"\n",$config['Body']);
    }

    public function verifyAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $enterpriseIdKey = $this->_getParam('enterprise-id-key');
        $enterprise = $this->Enterprise->getEnterpriseByIdKey($enterpriseIdKey);
        $competitionId = $this->modelQuestionnaire->getCurrentExecution()->getCompetitionId();

        $modelEnterpriseCategoryAwardCompetition = new Model_EnterpriseCategoryAwardCompetition();
        $modelEnterpriseCategoryAwardCompetition->updateECACVerifiedByEnterpriseIdAndYear($enterprise->getId(), $competitionId);
    }
}
