<?php

class DbTable_Enterprise extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Enterprise';
    protected $_id = 'Id';
    protected $_sequence = true;
    protected $_rowClass = 'DbTable_EnterpriseRow';

    protected $camposEnterprise = array('IdKey',
        'CategoryAwardId',
        'SocialName','FantasyName','Status','Cnpj',
        'CreationDate','EmailDefault', 'Cnae',
        'Phone', 'EmployeesQuantity',
        'CategoriaId' => 'E.CategorySectorId',
        'Telefone' => 'Phone', 'AnnualRevenue', 'Site','CompanyHistory','StateRegistration','Dap','RegisterMinistryFisher','OcbRegister','Nirf','FarmSize'
		
    );

    protected $menosCamposEnterprise = array('IdKey',
        'SocialName','FantasyName','Cnpj',
        'CreationDate','EmailDefault','Phone','Status', 'Cnae',
        'EmployeesQuantity', 'AnnualRevenue', 'CategoriaId' => 'E.CategorySectorId',
        'Telefone' => 'Phone'
    );

    protected $_dependentTables = array(
        'DbTable_Address',
        'DbTable_Answer',
        'DbTable_Contact',
        'DbTable_Execution',
        'DbTable_Responsability',
        'DbTable_UserLocality'
    );

    /*
     * Get All Enterprise By Address Enterprise, columns: StateId or CityId or NeighborhoodId.
     *
     * $colAddress: Nome da Coluna: StateId or CityId or NeighborhoodId.
     * $valuesAddress: valores do $colAddress <array>
     *
     */
    public function getAll(
        $valuesAddress, $colAddress, $questionnaireId = null, $fetch = 'all',
        $filter = null, $orderBy = null, $format = null, $tipoRelatorio = 'inscricoes',
        $groupBy = null
    ) {
        
               
        $configDb = Zend_Registry::get('configDb');

        $filter = $this->setDefaultFiltersValue($filter);
        
        $competitionId = (isset($filter['competition_id']) and $filter['competition_id'])?
            $filter['competition_id'] : $configDb->competitionId;
        
        /*
        $currentAutoavaliacaoId = ($configDb->qstn->currentAutoavaliacaoId)?
                $configDb->qstn->currentAutoavaliacaoId:'"null"';
        */
        $currentAutoavaliacaoId = $questionnaireId = DbTable_Questionnaire::getInstance()->getQuestionnaireIdByCompetitionId($competitionId);
        /*
         *  pegar blockIdEmpreendedoris pelo competitionId.
         * encapsular
         */
        switch ($competitionId) {
            case 2014:
            case 2012:
                $currentBlockIdEmpreendedorismo = "null";
                break;
            case 2013:
                $currentBlockIdEmpreendedorismo = ($configDb->qstn->currentBlockIdEmpreendedorismo)? $configDb->qstn->currentBlockIdEmpreendedorismo:'"null"';
                break;
            default:
                $currentBlockIdEmpreendedorismo = ($configDb->qstn->currentBlockIdEmpreendedorismo)? $configDb->qstn->currentBlockIdEmpreendedorismo:'"null"';
                break;
        }
        

        $incluirJoinPontuacao = (isset($filter['incluir_join_pontuacao']) and $filter['incluir_join_pontuacao'] == '1')?true:false;
        $camposEmpresa = ($incluirJoinPontuacao)?$this->camposEnterprise:$this->menosCamposEnterprise;


        $incluirJoinRegional = (isset($filter['incluir_join_regional']) and $filter['incluir_join_regional'] == '1')?true:false;

                $camposEnderecoEmpresa = ($format == 'csv') ?
            array('StreetNameFull', 'StreetNumber', 'StreetCompletion','Cep') : null;

        $query = $this->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(array('E' => 'Enterprise'), $camposEmpresa)
            ->join(
                array('USL' => 'UserLocality'), 'USL.EnterpriseId = E.Id',null
            )
            ->join(
                array('P' => 'President'), 'P.EnterpriseId = E.Id',
                array('NomeCompletoUser'=> new Zend_Db_Expr("P.Name"),
                    'CpfUser'=>'Cpf','TelefoneContato'=>'Phone',
                    'DataNascimentoUser'=>'BornDate','SexoUser'=>'Gender',
                    'CelularUser'=>'Cellphone',
                    'PositionId','EducationId','EmailUser'=>'Email','IdUser'=>'Id'
                , 'PresidentCreated' => 'Created','FindUsId'
                )
            )
            ->joinleft(array('U2' => 'User'),'U2.Id = USL.UserId', array('LoginUser'=>'Login'))
            ->join(array('AE' => 'AddressEnterprise'),
                'AE.EnterpriseId = E.Id', $camposEnderecoEmpresa
            )
            ->joinleft(array('C' => 'City'), 'C.Id = AE.CityId',  array('CityName' => 'Name')) //
            ->joinleft(array('S' => 'State'), 'S.Id = AE.StateId', array('Uf'))
            ->joinleft(array('N' => 'Neighborhood'), 'N.Id = AE.NeighborhoodId', array('NeighName' => 'Name')); //

        $query->joinLeft(array('ECA' => 'EnterpriseCategoryAward'), 'ECA.Id = E.CategoryAwardId',
            array('DescriptionEca' => 'Description'))
            ->join(array('ECAC' => 'EnterpriseCategoryAwardCompetition'), 'ECAC.EnterpriseId = E.Id AND ECAC.CompetitionId = '.$competitionId,
                null)
            ->joinleft( array('ER' => 'EnterpriseReport'),
                'ER.EnterpriseId = E.Id AND ER.CompetitionId = '. $competitionId,
                array('ReportId'=>'Id','ReportTitle'=>'Title')) // ,'CompetitionId','Report'
            ->joinleft(
                array('EPR' => 'EnterpriseProgramaRank'),
                "EPR.EnterpriseIdKey = E.IdKey AND EPR.ProgramaId = $competitionId",
                array('Classificar','Desclassificar','Justificativa')
            )
            ->joinleft(
                array('EXA' => 'Execution'),
                'EXA.UserId = USL.UserId AND EXA.QuestionnaireId = ' . $currentAutoavaliacaoId
                .' AND EXA.ProgramaId = '.$competitionId,
                array('PA' => 'Progress')
            )
            ->joinLeft(array('ECS' => 'EnterpriseCategorySector'), 'ECS.Id = E.CategorySectorId',array('DescriptionCategorySector' => 'Description'))
        ;
            
        $this->appendVerifiedJoin($query);

        //avaliadores
        $query = $this->_queryAvaliadores($query, $filter, $competitionId, $tipoRelatorio);

        if ($format == 'csv') {
            $query = $this->_queryCSV($query, $competitionId);
        }

        if ($incluirJoinPontuacao) {
            $query
                ->joinleft(array('Pos' => 'Position'),'Pos.Id = P.PositionId', array('CargoPresident'=>'Description'))
                ->joinleft(array('Edu' => 'Education'),'Edu.Id = P.EducationId', array('Education'=>'Description'))

                /*
                 *
                ->joinLeft(array('PP' => 'PresidentProgram'),'PP.PresidentId = P.Id AND PresidentProgramTypeId = 1',
                    array('1'=>'Empretec')
                )
                */
                ->joinleft(
                    array('EP' => 'ExecutionPontuacao'),
                    'EP.ExecutionId = EXA.Id',
                    array('NegociosTotal')
                );

            /*
                somente para 2013 traz PontosEmpreendedorismo
             **/
            $queryBeg = FALSE;
            if ( $competitionId == 2013) {
                $queryBeg = $this->getAdapter()
                    ->select()
                    ->from(array('BEG'=>'BlockEnterpreneurGrade'),
                        new Zend_Db_Expr("AVG(Points) * 4"));
                $queryBeg->where("BEG.CompetitionId = (?)",$competitionId);
                $queryBeg->where('BEG.UserId = USL.UserId');
                $queryBeg->where("BEG.QuestionnaireId = (?)", $currentAutoavaliacaoId);
                $queryBeg->where("BEG.BlockId = (?)", $currentBlockIdEmpreendedorismo);
                $query->columns(array('PontosEmpreendedorismo'=>new Zend_Db_Expr("($queryBeg)")));
            }
        } // fim incluir join ############

        if ($incluirJoinRegional) {
            /*
            $queryRegional = "(SELECT
                            CASE
                                WHEN (
                                        SELECT Rcity.Description FROM `ServiceArea` AS `SAcity`
                                        JOIN `Regional` AS `Rcity` ON SAcity.RegionalId = Rcity.Id AND Rcity.National = 'N'
                                        WHERE SAcity.CityId = AE.CityId
                                    ) is not null
                                THEN (
                                        SELECT Rcity.Description FROM `ServiceArea` AS `SAcity`
                                        JOIN `Regional` AS `Rcity` ON SAcity.RegionalId = Rcity.Id AND Rcity.National = 'N'
                                        WHERE SAcity.CityId = AE.CityId
                                    )
                                WHEN (
                                        SELECT Rstate.Description FROM `ServiceArea` AS `SAstate`
                                        JOIN `Regional` AS `Rstate` ON SAstate.RegionalId = Rstate.Id AND Rstate.National = 'N' AND Rstate.Estadual is not null
                                        WHERE SAstate.StateId = AE.StateId
                                    ) is not null
                                THEN (
                                        SELECT Rstate.Description FROM `ServiceArea` AS `SAstate`
                                        JOIN `Regional` AS `Rstate` ON SAstate.RegionalId = Rstate.Id AND Rstate.National = 'N' AND Rstate.Estadual is not null
                                        WHERE SAstate.StateId = AE.StateId
                                    )
                                ELSE ''
                                END)";
            $query->columns(array('Regional' =>new Zend_Db_Expr($queryRegional)));
            */
            $query = $this->_queryGetRegionalByAddressEnterprise($query);
        }

        if ($questionnaireId) {
            
            $query->joinLeft(
                array('EXE' => 'Execution'),
                'USL.UserId = EXE.UserId AND EXE.QuestionnaireId = '.$questionnaireId
                .' AND EXE.ProgramaId = '.$competitionId,
               array('DevolutivePath','EvaluationPath','FinalScore')
            );
        }
        
        if (isset($filter['regional_id']) and $filter['regional_id']) {
            $regionalId = $filter['regional_id'];
            $query->join(
                array('SA' => 'ServiceArea'),
                "SA.RegionalId = $regionalId AND (
                    SA.StateId = AE.StateId
                    OR SA.CityId = AE.CityId
                    OR SA.NeighborhoodId = AE.NeighborhoodId)",
                null
            );
        }

        if ($colAddress) {
            $query->where("AE.$colAddress in (?)", $valuesAddress);
        }

        switch ($tipoRelatorio) {
            case 'ranking': $filter['verified_subscription'] = 1; break;
            case 'report-categoria':
            case 'report-digitador':
            case 'report-regional-bairros':
            case 'report-regional-cidades':
            case 'report-regional-estados':
                $filter['devolutiva'] = null;
                break;
        }
       
        if (isset($filter['candidatura']) and $filter['candidatura']) {
            switch ($filter['candidatura']) {
                case 'C': //'candidatas'
                    $filter['devolutiva'] = 2;
                    break;
                case 'I': //'candidatas'
                    $query->where('EXE.DevolutivePath is null');
                    break;
                // case 3:  'inscritas' break;
            }
        }


        if (isset($filter['devolutiva']) and $filter['devolutiva']) {
            switch ($filter['devolutiva']) {
                case 2: //'candidatas'
                    $query->where('EXE.DevolutivePath is not null');
                    break;
                // case 3:  'inscritas' break;
            }
        }

        if (isset($filter['president_name']) and $filter['president_name']) {
            $query->where("P.Name LIKE (?) OR P.NickName LIKE (?)", '%'.$filter['president_name'].'%');
        }
        if (isset($filter['education_id']) and $filter['education_id']) {
            $query->where('P.EducationId = ?',$filter['education_id']);
        }
        if (isset($filter['category_award_id']) and $filter['category_award_id']) {
            $query->where('E.CategoryAwardId = ?',$filter['category_award_id']);
        }
        if (isset($filter['category_sector_id']) and $filter['category_sector_id']) {
            $query->where('E.CategorySectorId = ?', $filter['category_sector_id']);
        }
        if (isset($filter['annual_revenue']) and $filter['annual_revenue']) {
            $query->where('E.AnnualRevenue = (?)',$filter['annual_revenue']);
        }
        if ($tipoRelatorio != 'report-status-appraiser') { // nao usar esse where no relatorio de status da avaliacao
            if (isset($filter['appraiser_id']) and $filter['appraiser_id']) {
                $query->where('ApE.UserId = (?) or ApESec.UserId = (?) or ApETer.UserId = (?)',$filter['appraiser_id']);
            }
        }
        if ( isset($filter['employees_quantity']) and $filter['employees_quantity'] ) {
            $eq = trim($filter['employees_quantity']);
            $query->where('E.EmployeesQuantity = (?)', $eq );
        }
        if (isset($filter['cpf']) and $filter['cpf']) {
            $query->where('P.Cpf LIKE "%'.preg_replace('/[^0-9]/', '', $filter['cpf']).'%"');
        }
        if (isset($filter['metier_id']) and $filter['metier_id']) {
            $query->where('E.MetierId = (?)',$filter['metier_id']);
        }
        if (isset($filter['status']) and $filter['status']) {
            $query->where('E.Status = (?)', $filter['status']);
        }
        if (isset($filter['coop_name']) and $filter['coop_name']) {
            $query->where("E.SocialName LIKE (?) OR E.FantasyName LIKE (?)", '%'.$filter['coop_name'].'%');
        }
        if (isset($filter['cnpj']) and $filter['cnpj']) {
            $query->where('E.Cnpj LIKE "%'.preg_replace('/[^0-9]/', '', $filter['cnpj']).'%"');
        }
        if (isset($filter['faixa']) and $filter['faixa']) {
            $faixa = Vtx_Util_Array::faixaIdadePSMN($filter['faixa']);
            $query->where(new Zend_Db_Expr(
                "FLOOR(DATE_FORMAT(NOW(),'%Y')-DATE_FORMAT(P.Borndate,'%Y')) BETWEEN " . preg_replace('/[^0-9]/', '', $faixa[1]) . ' AND '                 . preg_replace('/[^0-9]/', '', $faixa[2])
            ));
        }
        if (isset($filter['state_id']) and $filter['state_id']) {
            $query->where("AE.StateId = (?)", $filter['state_id']);
        }
        if (isset($filter['city_id']) and $filter['city_id']) {
            $query->where("AE.CityId = (?)", $filter['city_id']);
        }
        if (isset($filter['neighborhood_id']) and $filter['neighborhood_id']) {
            $query->where("AE.NeighborhoodId = (?)", $filter['neighborhood_id']);
        }

        switch ($tipoRelatorio) {
            case 'report-global-respostas':
                $query = $this->_queryReportGlobalRespostas($query, $filter, $competitionId);
                break;
            case 'report-global-criterios':
                $query = $this->_queryReportGlobalCriterios($query);
                break;
            case 'lista-avaliador':
            case 'lista-avaliador-nacional':
                $query = $this->_queryListaAvaliador($query, $questionnaireId, $competitionId);
                break;
            case 'report-regional-bairros':
                $query = $this->_queryReportRegional($query, 'bairros');
                break;
            case 'report-regional-cidades':
                $query = $this->_queryReportRegional($query, 'cidades');
                break;
            case 'report-regional-estados':
                $query = $this->_queryReportRegional($query, 'estados');
                break;
            case 'report-categoria':
                $query = $this->_queryReportCategoria($query);
                break;
            case 'report-categoria-premio':
                $query = $this->_queryReportCategoriaPremio($query);
                break;
            case 'report-inscricoes':
                $query = $this->_queryReportInscricoes($query, $competitionId);
                break;
            case 'report-inscricoes-categoria':
                $query = $this->_queryReportInscricoesCategoria($query, $competitionId);
                break;
            case 'report-digitador':
                $query = $this->_queryReportDigitador($query, $competitionId);
                $orderBy = null;
                break;
           // case 'classificadas-nacional':
            case 'classificadas':
                $query = $this->_queryClassificadas($query, $queryBeg , $filter, $competitionId);
                $orderBy = null;
                break;
            case 'classificadas-nacional':
            	$query = $this->_queryClassificadasNacional($query, $queryBeg , $filter, $competitionId);
            	$orderBy = null;
            	break;
            case 'finalistas':
                $query = $this->_queryFinalistas($query, $queryBeg, $filter, $competitionId);
                $orderBy = null;
                break;
            case 'finalistas-nacional':
                $query = $this->_queryFinalistasNacional($query, $queryBeg, $filter, $competitionId);
                $orderBy = null;
                break;
            case 'report-status-appraiser':
                $query = $this->_queryReportStatusAppraiser($query, $competitionId,$filter);
                $orderBy = 'U.FirstName ASC';
                break;
            case 'report-status-verificador':
                $query = $this->_queryReportStatusChecker($query, $competitionId);
                $orderBy = 'U.FirstName ASC';
                break;

            case 'checker-list': //marianam
                $query = $this->_queryCheckerList($query, $questionnaireId, $filter, $competitionId);
                break;
            case 'candidatas-nacional':
                $query = $this->_queryCandidatasNacional($query, $queryBeg, $filter, $competitionId);
                break;
        }

        if($groupBy){
            $groupByString = $this->groupByStringFor($groupBy);
            if($groupByString) $query->group($groupByString);
        }

        if ($orderBy) {
            $query->order($orderBy);
        }
        /*
        if ($orderBy == 'NegociosTotal DESC') {
            $query->order('PontosEmpreendedorismo DESC');
        }
        */

//         echo $query->__toString(); die;

        
      
        $retorno = $this->fetch($query, $fetch);
        
        return $retorno;
    }

    public function getAllForParticipationNotification($filter) {
        $stateId = isset($filter['state_id']) ? $filter['state_id'] : null;
        $userId = $filter['user_id'];
        $competitionId = $filter['competition_id'];

        $onlyWinners = isset($filter['only_winners']);
        $excludeWinners = isset($filter['exclude_winners']);

        $ecacJoinCondition = "ECAC.CompetitionId = $competitionId and E.Id = ECAC.EnterpriseId";
        $eprJoinColumns = array('ClassificadoOuro', 'ClassificadoPrata', 'ClassificadoBronze');
        $eprJoinCondition = 'EPR.EnterpriseIdKey = E.IdKey and EPR.ProgramaId = ECAC.CompetitionId ';
        $ecaJoinColumns = array('Premio' => 'Description');
        $exeJoinCondition = 'EXE.UserId = PU.Id and EXE.ProgramaId = ECAC.CompetitionId';
        $ceJoinCondition = 'CE.EnterpriseId = E.Id and CE.CheckerTypeId = 1 and CE.ProgramaId = ECAC.CompetitionId';
        $stateJoinCondition = $stateId ? "S.Id = $stateId" : null;
        $wnJoinCondition = 'WN.Id = WNE.WinningNotificationId and WN.CompetitionId = ECAC.CompetitionId
          and WN.StateId = S.Id';
        $emJoinColumns = array('NotificationStatus' => 'Status', 'NotificationSentAt' => 'SentAt');
        $wnuJoinColumns = array('ResponsibleFirstName' => 'FirstName', 'ResponsibleSurname' => 'Surname');

        if($onlyWinners or $excludeWinners){
            $operator = $onlyWinners ? '>' : '=';
            $eprJoinCondition .= "and (EPR.ClassificadoOuro + EPR.ClassificadoPrata + EPR.ClassificadoBronze) $operator 0";
        }
        exit;
        
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('E' => $this->_name), array('Id','IdKey','FantasyName','Cnpj'))
            ->joinInner(array('PUL' => 'UserLocality'), 'PUL.EnterpriseId = E.Id', null)
            ->joinInner(array('PU' => 'User'), 'PU.Id = PUL.UserId', null)
            ->joinInner(array('S' => 'State'), $stateJoinCondition, array('Uf'))
            ->joinInner(array('U' => 'User'), "U.Id = $userId", null)
            ->joinInner(array('UL' => 'UserLocality'), 'UL.UserId = U.Id', null)
            ->joinInner(array('SA' => 'ServiceArea'), 'SA.RegionalId = UL.RegionalId', null)
            ->joinLeft(array('N' => 'Neighborhood'), 'N.Id = SA.NeighborhoodId', null)
            ->joinLeft(array('C' => 'City'), 'C.Id = N.CityId or C.Id = SA.CityId', null)
            ->joinInner(array('ECAC' => 'EnterpriseCategoryAwardCompetition'), $ecacJoinCondition, null)
            ->joinInner(array('AE' => 'AddressEnterprise'), 'AE.StateId = S.Id and AE.EnterpriseId = E.Id', null)
            ->joinInner(array('EPR' => 'EnterpriseProgramaRank'), $eprJoinCondition, $eprJoinColumns)
            ->joinInner(array('ECA' => 'EnterpriseCategoryAward'), 'ECA.Id = E.CategoryAwardId', $ecaJoinColumns)            
            ->joinLeft(array('EXE' => 'Execution'), $exeJoinCondition, array('DevolutivePath'))
            ->joinLeft(array('EP' => 'ExecutionPontuacao'), 'EP.ExecutionId = EXE.Id', array('NegociosTotal'))
            ->joinLeft(array('CE' => 'CheckerEnterprise'), $ceJoinCondition, array('QtdePontosFortes'))
            ->joinLeft(array('WNE' => 'WinningNotificationEnterprise'), 'WNE.EnterpriseId = E.Id', null)
            ->joinLeft(array('WN' => 'WinningNotification'), $wnJoinCondition, null)
            ->joinLeft(array('EM' => 'EmailMessage'), 'EM.Id = WN.EmailMessageId', $emJoinColumns)
            ->joinLeft(array('WNU' => 'User'), 'WNU.Id = WN.ResponsibleId', $wnuJoinColumns)
            ->where("E.EmailDefault is not null and E.EmailDefault <> ''")
        ;

        for($i=1 ; $i < 4 ; $i++){
            $apeJoinColumns = array("StatusA$i" => 'Status', "PontosA$i" => 'Pontos');
            $apeJoinCondition = "APE$i.EnterpriseId = E.Id and APE$i.AppraiserTypeId = $i
              and APE$i.ProgramaId = ECAC.CompetitionId";

            $query->joinLeft(array("APE$i" => 'AppraiserEnterprise'), $apeJoinCondition, $apeJoinColumns);
        }

        $query->group('E.Id');

        return $this->fetchAll($query);
    }

    public function getQueryForSubscriptions($loggedUserId, $filter){
        $filter = $this->setDefaultFiltersValue($filter);

        $query = $this->getBaseQuery($filter);

        $erJoinCondition = "ER.EnterpriseId = E.Id and ER.CompetitionId = ECAC.CompetitionId";
        $erJoinColumns = array('ReportId' => 'Id', 'ReportTitle' => 'Title');

        $query
            ->joinLeft(array('ER' => 'EnterpriseReport'), $erJoinCondition, $erJoinColumns)
        ;

        $this->appendQueryFilters($query, $loggedUserId, $filter);

        $query->group('E.Id');

        return $query;
    }

    private function getBaseQuery($filter){
        $competitionId = $filter['competition_id'];
        if(!$competitionId) $competitionId = date('Y');

        $ecacJoinCondition = "ECAC.CompetitionId = $competitionId and E.Id = ECAC.EnterpriseId";
        $ecacJoinColumns = array('SubscriptionVerified' => new Zend_Db_Expr('(max(ECAC.Verified))'));
        $ecaJoinColumns = array('CategoryDescription' => 'Description');
        $exeJoinCondition = 'EXE.UserId = U.Id and EXE.ProgramaId = ECAC.CompetitionId';
        $presidentJoinColumns = array('PresidentPhone' => 'Phone', 'PresidentCellphone' => 'Cellphone');

        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('E' => $this->_name))
            ->joinInner(array('AE' => 'AddressEnterprise'), 'AE.EnterpriseId = E.Id', null)
            ->joinInner(array('P' => 'President'), 'P.EnterpriseId = E.Id', $presidentJoinColumns)
            ->joinInner(array('UL' => 'UserLocality'), 'UL.EnterpriseId = E.Id', null)
            ->joinInner(array('U' => 'User'), "U.Id = UL.UserId", null)
            ->joinInner(array('ECAC' => 'EnterpriseCategoryAwardCompetition'), $ecacJoinCondition, $ecacJoinColumns)
            ->joinInner(array('ECA' => 'EnterpriseCategoryAward'), 'ECA.Id = E.CategoryAwardId', $ecaJoinColumns)
            ->joinLeft(array('EXE' => 'Execution'), $exeJoinCondition, array('DevolutivePath','Progress'))
            ->joinInner(array('S' => 'State'), 'S.Id = AE.StateId', array('Uf'))
        ;

        return $query;
    }

    public function getQueryForStateCandidates($loggedUserId, $filter){
        $filter = $this->setDefaultFiltersValue($filter);

        $eprJoinCondition = 'EPR.EnterpriseIdKey = E.IdKey and EPR.ProgramaId = ECAC.CompetitionId';
        $eprJoinColumns = array('Classificar', 'Desclassificar', 'Justificativa');

        $query = $this->getBaseQuery($filter);

        $query
            ->joinLeft(array('EPR' => 'EnterpriseProgramaRank'), $eprJoinCondition, $eprJoinColumns)
            ->joinLeft(array('EP' => 'ExecutionPontuacao'), 'EP.ExecutionId = EXE.Id', array('NegociosTotal'))
        ;

        for($i=1 ; $i < 4 ; $i++){
            $apeJoinColumns = array("Appraiser".$i."Id" => 'UserId');
            $apeJoinCondition = "APE$i.EnterpriseId = E.Id and APE$i.AppraiserTypeId = $i
              and APE$i.ProgramaId = ECAC.CompetitionId";

            $query->joinLeft(array("APE$i" => 'AppraiserEnterprise'), $apeJoinCondition, $apeJoinColumns);
        }

        $this->appendQueryFilters($query, $loggedUserId, $filter);

        $query->where('EXE.DevolutivePath IS NOT NULL');
		

        $query->group('E.Id');

        $query->order('EP.NegociosTotal DESC');

        return $query;
    }

    public function getAllForNationalCandidates($loggedUserId, $filter){        
        $competitionId = $filter['competition_id'];
        if(!$competitionId) $competitionId = date('Y');

        $ecacJoinCondition = "ECAC.CompetitionId = $competitionId and E.Id = ECAC.EnterpriseId";
        $eprJoinCondition = 'EPR.EnterpriseIdKey = E.IdKey and EPR.ProgramaId = ECAC.CompetitionId
            and EPR.ClassificarFase2Nacional = 1';
        $eprJoinColumns = array('ClassificadoOuroNacional', 'ClassificadoPrataNacional', 'ClassificadoBronzeNacional',
            'DesclassificarFase2Nacional', 'MotivoDesclassificadoFase2Nacional');
        $ecaJoinColumns = array('Premio' => 'Description');
        $exeJoinCondition = 'EXE.UserId = U.Id and EXE.ProgramaId = ECAC.CompetitionId';

        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('E' => $this->_name), array('Id','IdKey','FantasyName','Cnpj'))
            ->joinInner(array('AE' => 'AddressEnterprise'), 'AE.EnterpriseId = E.Id', null)
            ->joinInner(array('P' => 'President'), 'P.EnterpriseId = E.Id', null)
            ->joinInner(array('UL' => 'UserLocality'), 'UL.EnterpriseId = E.Id', null)
            ->joinInner(array('U' => 'User'), "U.Id = UL.UserId", null)
            ->joinInner(array('ECAC' => 'EnterpriseCategoryAwardCompetition'), $ecacJoinCondition, null)
            ->joinInner(array('EPR' => 'EnterpriseProgramaRank'), $eprJoinCondition, $eprJoinColumns)
            ->joinInner(array('ECA' => 'EnterpriseCategoryAward'), 'ECA.Id = E.CategoryAwardId', $ecaJoinColumns)
            ->joinLeft(array('EXE' => 'Execution'), $exeJoinCondition, array('DevolutivePath'))
            ->joinLeft(array('EP' => 'ExecutionPontuacao'), 'EP.ExecutionId = EXE.Id', array('NegociosTotal'))
            ->joinInner(array('S' => 'State'), 'S.Id = AE.StateId', array('Uf'))
        ;

        for($i=4 ; $i < 7 ; $i++){
            $apeJoinColumns = array("StatusA$i" => 'Status', "PontosA$i" => 'Pontos');
            $apeJoinCondition = "APE$i.EnterpriseId = E.Id and APE$i.AppraiserTypeId = $i
              and APE$i.ProgramaId = ECAC.CompetitionId";

            $query->joinLeft(array("APE$i" => 'AppraiserEnterprise'), $apeJoinCondition, $apeJoinColumns);
        }

        $this->appendQueryFilters($query, $loggedUserId, $filter);

        $query->group('E.Id');

        return $this->fetchAll($query);
    }

    public function getQueryForRegionalsReport($loggedUserId, $filter){
        $filter = $this->setDefaultFiltersValue($filter);

        $relatedRegionalsRestriction = $this->getRelatedRegionalsRestrictionForRegionalJoinAccordWithUserPermission(
            $loggedUserId,
            $filter['regional_id']
        );

        
        $esaJoinCond = 'ESA.StateId = AE.StateId OR ESA.CityId = AE.CityId OR ESA.NeighborhoodId = AE.NeighborhoodId';

        $query = $this->getReportBaseQuery($filter)
            ->joinLeft(array('ESA' => 'ServiceArea'), $esaJoinCond, null)
            ->joinLeft(array('R' => 'Regional'), 'R.Id = ESA.RegionalId', null)
            ->joinLeft(array('R2' => 'Regiao'), 'R2.Id = R.RegiaoId', null);

        $query->reset(Zend_Db_Select::COLUMNS)->columns(
            "S.Uf,R2.Descricao AS Region,R.Description AS Regional," . $this->getReportBaseSelectColumns()
        );

        if($filter['mostrarRegionais'] == '2') $resultsPanoramaFilter = "R.Estadual IS NOT NULL OR R.National = 'S'";
        else $resultsPanoramaFilter = "R.Estadual IS NULL AND R.National = 'N'";

        $query->where("E.Status = 'A'")
            ->where("R.Status = 'A'")
            ->where($resultsPanoramaFilter)
        ;

        if($relatedRegionalsRestriction) $query->where("R.Id IN ($relatedRegionalsRestriction)");

        $this->appendQueryFilters($query, $loggedUserId, $filter, true);

        $query->group('R.Id');

        $query->order(array('Uf','Region','Regional'));

        return $query;
    }

    private function getReportBaseQuery($filter){
        $ceJoinCond = "CE.EnterpriseId = E.Id AND CE.ProgramaId = ECAC.CompetitionId AND CE.Status = 'C'";
        $eprJoinCond = 'EPR.EnterpriseIdKey = E.IdKey AND EPR.ProgramaId = ECAC.CompetitionId';

        $query = $this->getBaseQuery($filter)
            ->joinLeft(array('CE' => 'CheckerEnterprise'), $ceJoinCond, null)
            ->joinLeft(array('EPR' => 'EnterpriseProgramaRank'), $eprJoinCond, null)
        ;

        return $query;
    }

    private function getReportBaseSelectColumns(){
        return "
            COUNT(E.Id) AS Subscriptions,
            SUM(IF(EXE.DevolutivePath IS NOT NULL and EXE.DevolutivePath <> '', 1, 0)) AS Candidates,
            COUNT(CE.Id) AS 'Verifieds',
            SUM(coalesce(EPR.ClassificadoBronze,0)) + SUM(coalesce(EPR.ClassificadoBronzeNacional,0)) AS 'BronzeMedals',
            SUM(coalesce(EPR.ClassificadoPrata,0)) + SUM(coalesce(EPR.ClassificadoPrataNacional,0)) AS 'SilverMedals',
            SUM(coalesce(EPR.ClassificadoOuro,0)) + SUM(coalesce(EPR.ClassificadoOuroNacional,0)) AS 'GoldMedals'
        ";
    }

    public function getQueryForSectorsReport($loggedUserId, $filter){
        $filter = $this->setDefaultFiltersValue($filter);

        $relatedRegionalsRestriction = $this->getRelatedRegionalsRestrictionForRegionalJoinAccordWithUserPermission(
            $loggedUserId,
            $filter['regional_id']
        );

        $query = $this->getReportBaseQuery($filter)
            ->joinInner(array('ECS' => 'EnterpriseCategorySector'), 'ECS.Id = E.CategorySectorId', null)
        ;

        $query->reset(Zend_Db_Select::COLUMNS)->columns(
            "ECS.Description AS Category," . $this->getReportBaseSelectColumns()
        );

        $query->where("E.Status = 'A'");

        if($relatedRegionalsRestriction) {
            $query->where("
                EXISTS (
                    SELECT *
                    FROM ServiceArea AS ESA
                    INNER JOIN Regional AS ER ON ER.Id = ESA.RegionalId
                    WHERE ER.Status = 'A'
                    AND (
                        ESA.StateId = AE.StateId
                        OR ESA.CityId = AE.CityId
                        OR ESA.NeighborhoodId = AE.NeighborhoodId
                    )
                    AND ER.Id IN ($relatedRegionalsRestriction)
                )
            ");
        }

        $this->appendQueryFilters($query, $loggedUserId, $filter, true);

        $query->group('ECS.Id');

        $query->order('ECS.Description');

        return $query;
    }

    private function appendVerifiedJoin($query) {
        $joinColumns = array('SubscriptionVerified' => new Zend_Db_Expr('(max(VS.Verified))'));

        $query->joinLeft(array('VS' => 'EnterpriseCategoryAwardCompetition'), 'VS.EnterpriseId = E.Id', $joinColumns);
    }

    private function appendQueryFilters($query, $loggedUserId, $filter, $ignoreRegionalFilter=false) {

        if(in_array('annual_revenue', array_keys($filter)) && $filter['annual_revenue'] != '')
            $query->where('E.AnnualRevenue = ?', $filter['annual_revenue']);

        if(in_array('category_sector_id', array_keys($filter)) && $filter['category_sector_id'] != '')
            $query->where('E.CategorySectorId = ?', $filter['category_sector_id']);

        if(in_array('education_id', array_keys($filter)) && $filter['education_id'] != '')
            $query->where('P.EducationId = ?', $filter['education_id']);

        if(in_array('category_award_id', array_keys($filter)) && $filter['category_award_id'] != '')
            $query->where('E.CategoryAwardId = ?', $filter['category_award_id']);

        if(!$ignoreRegionalFilter){
            $regionalId = null;
            if(in_array('regional_id', array_keys($filter)) && $filter['regional_id'] != ''){
                $regionalId = $filter['regional_id'];
            }
            $this->appendUserRegionalJoins($query, $loggedUserId, $regionalId);
        }

        if(in_array('metier_id', array_keys($filter)) && $filter['metier_id'] != '')
            $query->where('E.MetierId = ?', $filter['metier_id']);

        if(in_array('state_id', array_keys($filter)) && $filter['state_id'] != '')
            $query->where("AE.StateId = ?", $filter['state_id']);

        if(in_array('city_id', array_keys($filter)) && $filter['city_id'] != '')
            $query->where("AE.CityId = ?", $filter['city_id']);

        if(in_array('neighborhood_id', array_keys($filter)) && $filter['neighborhood_id'] != '')
            $query->where("AE.NeighborhoodId = ?", $filter['neighborhood_id']);

        if(in_array('status', array_keys($filter)) && $filter['status'] != '')
            $query->where('E.Status = ?', $filter['status']);

        if(in_array('verified_subscription', array_keys($filter)) && $filter['verified_subscription'] != '')
            $query->where('ECAC.Verified = ?', $filter['verified_subscription']);

        if(in_array('coop_name', array_keys($filter)) && $filter['coop_name'] != '')
            $query->where("E.SocialName LIKE ? OR E.FantasyName LIKE ?", '%'.$filter['coop_name'].'%');

        if(in_array('candidatura', array_keys($filter)) && $filter['candidatura'] != ''){
            if($filter['candidatura'] == 'C')
                $query->where('EXE.DevolutivePath is not null');
            else
                $query->where('EXE.DevolutivePath is null');
        }

        if(in_array('cnpj', array_keys($filter)) && $filter['cnpj'] != ''){
            $clean_cnpj = preg_replace('/[^0-9]/', '', $filter['cnpj']);
            $query->where('E.Cnpj LIKE "%'.$clean_cnpj.'%"');
        }

        if(in_array('president_name', array_keys($filter)) && $filter['president_name'] != ''){
            $query->where("P.Name LIKE ? OR P.NickName LIKE ?", '%'.$filter['president_name'].'%');
        }

        if(in_array('cpf', array_keys($filter)) && $filter['cpf'] != ''){
            $clean_cpf = preg_replace('/[^0-9]/', '', $filter['cpf']);
            $query->where('P.Cpf LIKE "%'.$clean_cpf.'%"');
        }

        if(in_array('employees_quantity', array_keys($filter)) && $filter['employees_quantity'] != ''){
            $clean_employees_quantity = trim($filter['employees_quantity']);
            $query->where('E.EmployeesQuantity = ?', $clean_employees_quantity);
        }

		 if(in_array('appraiser_id', array_keys($filter)) && $filter['appraiser_id'] != ''){
			 $appraiser_id= $filter['appraiser_id'] ; 
			 $query->where('APE1.UserId = ?', $appraiser_id);
			 $query->ORwhere('APE2.UserId = ?', $appraiser_id);
			 $query->ORwhere('APE3.UserId = ?', $appraiser_id);

		 }


        if(in_array('faixa', array_keys($filter)) && $filter['faixa'] != ''){
            $range = Vtx_Util_Array::faixaIdadePSMN($filter['faixa']);
            $start = preg_replace('/[^0-9]/', '', $range[1]);
            $end = preg_replace('/[^0-9]/', '', $range[2]);

            $query->where(new Zend_Db_Expr(
                "FLOOR(DATE_FORMAT(NOW(),'%Y')-DATE_FORMAT(P.Borndate,'%Y')) BETWEEN $start AND $end"
            ));
        }
    }

    private function appendUserRegionalJoins($query, $userId, $filterRegionalId=null) {
        $regionalRestriction = $this->getRelatedRegionalsRestrictionForRegionalJoinAccordWithUserPermission(
            $userId,
            $filterRegionalId
        );

        if($regionalRestriction) {
            $joinCond = '(AE.StateId = LUR.StateId or AE.CityId = LUR.CityId or AE.NeighborhoodId = LUR.NeighborhoodId)';
            $where = "LUR.RegionalId in ($regionalRestriction)";
            $query->joinLeft(array('LUR' => 'ServiceAreaCache'), $joinCond, null)->where($where);
        }
    }

    private function getRelatedRegionalsRestrictionForRegionalJoinAccordWithUserPermission($userId, $filterRegionalId){
        $filterRegionalId = $this->getRegionalFilterAccordWithUserPermission($userId, $filterRegionalId);

        if($filterRegionalId) {
            return $this->getRelatedRegionalRestrictionForRegionalJoin($filterRegionalId);
        } else {
            return null;
        }
    }

    private function getRegionalFilterAccordWithUserPermission($userId, $requestedRegionalIdFilter){
        $filterRegionalId = intval($requestedRegionalIdFilter);

        $regionalModel = new Model_Regional();
        $userRegional = $regionalModel->getRegionalByUser($userId);
        if(!$userRegional) throw new Exception('access denied');
        $isAdmin = $userRegional->getNational() === 'S';
        $userRegionalId = $userRegional->getId();

        if(!$isAdmin){
            if($filterRegionalId == null) $filterRegionalId = $userRegionalId;
            $this->validateUserPrivilegesAtRegional($userRegionalId, $filterRegionalId);
        }

        return $filterRegionalId;
    }

    private function validateUserPrivilegesAtRegional($userRegionalId, $filterRegionalId){
        $regionalIdList = $this->getRelatedRegionalIdList($userRegionalId);

        foreach($regionalIdList as $regionalId){
            if($regionalId === $filterRegionalId) return;
        }

        throw new Exception('access denied');
    }

    private function getRelatedRegionalRestrictionForRegionalJoin($filterRegionalId){
        $regionalIdList = $this->getRelatedRegionalIdList($filterRegionalId);
        return join(',', $regionalIdList);
    }

    private function getRelatedRegionalIdList($regionalId){
        $regionalIdList = array();
        $regionalTable = new DbTable_Regional();
        $regionalList = $regionalTable->getAllRegionalByOneRegionalServiceArea(null, $regionalId);

        foreach($regionalList as $regional) $regionalIdList[] = intval($regional->getId());

        return $regionalIdList;
    }

    private function setDefaultFiltersValue($filter){
        $attrNames = array('annual_revenue','category_sector_id','education_id','category_award_id','regional_id',
            'metier_id','state_id','city_id','neighborhood_id','status','category_award_id','verified_subscription',
            'coop_name','cnpj','president_name','cpf','employees_quantity','faixa', 'competition_id','appraiser_id');

        foreach($attrNames as $attrName){
            if(!isset($filter[$attrName])){
                $filter[$attrName] = '';
            }
        }

        return $filter;
    }

    protected function _queryCandidatasNacional($query, $queryBeg, $filter, $competitionId)
    {
        $query
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'PontosGeral' => $this->_sqlPontosGeral($comBonus = true),
                'MediaPontos' => $this->_sqlMediaGeral(),
                'IdKey', 'SocialName', 'FantasyName',
                'DiagnosticoEligibility', 'Cnpj','S.Uf',
                'CpfUser'=>'P.Cpf', 'E.EmailDefault', 'Phone',
                'PA' => 'EXA.Progress',
                'EPR.MotivoDesclassificadoVerificacao','EPR.Classificar','EPR.Desclassificar','EPR.Justificativa',
                'EPR.ClassificadoVerificacao', 'EPR.DesclassificadoVerificacao',
                'EPR.DesclassificadoFinal','EPR.MotivoDesclassificadoFinal',
                'EPR.ClassificadoOuro','EPR.ClassificadoPrata','EPR.ClassificadoBronze',
                'DescriptionEca' => 'ECA.Description',
                'AppraiserId' => 'ApE.UserId',
                'AppraiserIdSec' => 'ApESec.UserId',
                'AppraiserIdTer' => 'ApETer.UserId',
                'AppraiserStatus'=>'ApE.Status',
                'AppraiserStatusSec' => 'ApESec.Status',
                'AppraiserStatusTer' => 'ApETer.Status',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'Tipo' => 'ApE.AppraiserTypeId',
                'TipoSec' => 'ApESec.AppraiserTypeId',
                'TipoTer' => 'ApETer.AppraiserTypeId',
                'EXE.DevolutivePath','EXE.EvaluationPath', 'EXE.FinalScore',
                'FirstNameAvaliadorTer'=>'UTer.FirstName','LoginAvaliadorTer'=>'UTer.Login',
                'FirstNameAvaliadorSec'=>'USec.FirstName','LoginAvaliadorSec'=>'USec.Login',
                'FirstNameAvaliadorPri'=>'U.FirstName','LoginAvaliadorPri'=>'U.Login',
                'NegociosTotal' => 'EP.NegociosTotal',               
                'EPR.MotivoDesclassificadoNacional',
                'EPR.ClassificarNacional',
                'EPR.DesclassificarNacional',
				'EnterpriseId' => 'ApE.EnterpriseId',
				'PontuacaoFinal' => 'PontuacaoFinal',
				'NotaAutoAvalVerificador' => 'NotaAutoAvalVerificador',
				'NotaQuestVisitaVerificador' => 'NotaQuestVisitaVerificador',
				'QtdPontosFortes' => 'QtdPontosFortes',
				
				'PontosVerificador_estadual' => 'PontosVerificador_estadual',
				'NegociosTotal_estadual' => 'NegociosTotal_estadual',
				'MediaPontos_estadual' => 'MediaPontos_estadual',
            ));
        if ($queryBeg) {
            $query->columns(array('PontosEmpreendedorismo'=>new Zend_Db_Expr("($queryBeg)")));
        }
        //->columns(array('Regional' =>new Zend_Db_Expr($queryRegional)))
        $query->join(array('CE' => 'CheckerEnterprise'),
            'CE.EnterpriseId = E.Id AND CE.CheckerTypeId = 1 AND CE.ProgramaId ='.$competitionId,
            array('CheckerId' => 'UserId', 'CheckerStatus' => 'Status', 'QtdePontosFortes')
        )
            ->joinleft(
                array('CheckerUsr' => 'User'), 'CheckerUsr.Id = CE.UserId',
                array('FirstNameChecker' => 'FirstName', 'LoginChecker' => 'Login')
            )
            /*
            ->where("(ApESec.Status is not null or ApETer.Status is not null or ApE.Status is not null)")
            */
            ->where("EXE.ProgramaId = ?", $competitionId)
            ->where('EPR.ClassificadoOuro = 1')
        ;

        return $query;
    }

    protected function _queryGetRegionalByAddressEnterprise($query)
    {
        $query
            ->joinLeft(array('SAcity' => 'ServiceArea'), 'SAcity.CityId = AE.CityId',null)
            ->joinLeft(array('Rcity' => 'Regional'), 'Rcity.Id = SAcity.RegionalId AND Rcity.National = "N" AND Rcity.Estadual is null',
                array('RegionalCity'=>'Rcity.Description'))
            ->joinLeft(array('SAstate' => 'ServiceArea'), 'SAstate.StateId = AE.StateId',null)
            ->joinLeft(array('Rstate' => 'Regional'), 'Rstate.Id = SAstate.RegionalId AND Rstate.National = "N" AND Rstate.Estadual is null',
                array('RegionalState'=>'Rstate.Description'))
            ->joinLeft(array('SAneighborhood' => 'ServiceArea'), 'SAneighborhood.NeighborhoodId = AE.NeighborhoodId',null)
            ->joinLeft(array('Rneighborhood' => 'Regional'), 'Rneighborhood.Id = SAneighborhood.RegionalId AND Rneighborhood.National = "N" AND Rneighborhood.Estadual is null',
                array('RegionalNeighborhood'=>'Rneighborhood.Description'))
        ;
        return $query;
    }


    protected function _queryCSV($query, $programaId)
    {
        $query
            ->join(
                array('AU' => 'AddressPresident'),
                'AU.PresidentId = P.Id',
                array('UsStreetNameFull'=>'StreetNameFull','UsStreetNumber'=>'StreetNumber',
                    'UsStreetCompletion'=>'StreetCompletion','UsCep'=>'Cep')
            )
            ->join(array('SUs' => 'State'), 'SUs.Id = AU.StateId', array('UsUf'=> 'Uf'))
            ->joinLeft(array('CUs' => 'City'), 'CUs.Id = AU.CityId', array('UsCityName' => 'Name'))
            ->joinLeft(array('NUs' => 'Neighborhood'), 'NUs.Id = AU.NeighborhoodId', array('UsNeighName' => 'Name'))

            ->joinLeft(array('LCE1'=>'LogCadastroEmpresa'), 'LCE1.EnterpriseId = E.Id AND LCE1.Acao = "aceite" AND LCE1.ProgramaId = '.$programaId,
                array('LogInscricao'=>'LCE1.CriadoEm','DigitadorLogInscricaoUserIdLog'=>'LCE1.UserIdLog')
            )
            ->joinLeft(array('UsLCE1'=>'User'), 'UsLCE1.Id = LCE1.UserIdLog',
                array(
                    'DigitadorLogInscricao'=> new Zend_Db_Expr('concat(UsLCE1.FirstName,UsLCE1.Surname)')
                )
            )
            ->joinLeft(array('LCE2'=>'LogCadastroEmpresa'), 'LCE2.EnterpriseId = E.Id AND LCE2.Acao = "devolutiva" AND LCE2.ProgramaId = '.$programaId,
                array('LogGerouDevolutiva'=>'LCE2.CriadoEm','DigitadorLogGerouDevolutivaUserIdLog'=>'LCE2.UserIdLog')
            )
            ->joinLeft(array('UsLCE2'=>'User'), 'UsLCE2.Id = LCE2.UserIdLog',
                array('DigitadorLogGerouDevolutiva'=>new Zend_Db_Expr('concat(UsLCE2.FirstName,UsLCE2.Surname)'))
            );
        return $query;
    }

    protected function _queryAvaliadores($query, $filter, $competitionId, $tipoRelatorio)
    {
		$avaliadoresNumero = array(1, 2, 3);
        if (
        in_array(
            $tipoRelatorio,
            array('candidatas-nacional', 'lista-avaliador-nacional', 'classificadas-nacional'))
        ) {
            $avaliadoresNumero = array(4, 5, 6);
        }
        $query
            ->joinleft(array('ApE' => 'AppraiserEnterprise'),
                "ApE.EnterpriseId = E.Id AND ApE.AppraiserTypeId = {$avaliadoresNumero[0]} AND ApE.ProgramaId = {$competitionId}"
                . (
                (isset($filter['appraiser_id']) and $filter['appraiser_id']) ?
                    " AND ApE.UserId = {$filter['appraiser_id']} and ApE.Status != 'C'" : ''
                ),
                array('AppraiserId' => 'UserId')
            )
            ->joinleft(array('U' => 'User'),'U.Id = ApE.UserId',
                null
            )
            // Avaliador Secundario
            ->joinleft( array('ApESec' => 'AppraiserEnterprise'),
                "ApESec.EnterpriseId = E.Id AND ApESec.AppraiserTypeId = {$avaliadoresNumero[1]} AND ApESec.ProgramaId = {$competitionId}"
                . (
                (isset($filter['appraiser_id']) and $filter['appraiser_id']) ?
                    " AND ApESec.UserId = {$filter['appraiser_id']} and ApESec.Status != 'C'" : ''
                ),
                array('AppraiserIdSec' => 'UserId')
            )
            ->joinleft( array('USec' => 'User'), 'USec.Id = ApESec.UserId',
                null
            )
            // Avaliador Terciario
            ->joinleft( array('ApETer' => 'AppraiserEnterprise'),
                "ApETer.EnterpriseId = E.Id AND ApETer.AppraiserTypeId = {$avaliadoresNumero[2]} AND ApETer.ProgramaId = {$competitionId}"
                . (
                (isset($filter['appraiser_id']) and $filter['appraiser_id']) ?
                    " AND ApETer.UserId = {$filter['appraiser_id']} and ApETer.Status != 'C'" : ''
                ),
                array('AppraiserIdTer' => 'UserId')
            )
            ->joinleft( array('UTer' => 'User'), 'UTer.Id = ApETer.UserId',
                null
            );
        return $query;
    }

    protected function _queryReportStatusAppraiser($query, $programaId,$filter)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->join( array('ApEC' => 'AppraiserEnterprise'),
                'ApEC.EnterpriseId = E.Id AND ApEC.ProgramaId = '.$programaId
                . (
                (isset($filter['appraiser_id']) and $filter['appraiser_id']) ?
                    " AND ApEC.UserId = {$filter['appraiser_id']}" : ''
                ),
                array('AppraiserIdC' => 'UserId','Status','AppraiserTypeId')
            )
            ->join( array('UsC' => 'User'), 'UsC.Id = ApEC.UserId',
                array('NameAvaliador'=>new Zend_Db_Expr('concat(UsC.FirstName," ",UsC.Surname)'))
            )
            ->columns(
                array(
                    'SocialName' => 'E.SocialName',
                    'PA' => 'EXA.Progress'
                )
            )
        ;

        if (isset($filter['status_avaliacao']) and $filter['status_avaliacao']) {
            $query->where('ApEC.Status = ?',$filter['status_avaliacao']);
        }

        return $query;
    }

    protected function _queryReportStatusChecker($query, $programaId)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->join( array('CE' => 'CheckerEnterprise'),
                'CE.EnterpriseId = E.Id AND CE.ProgramaId = '.$programaId,
                array('CheckerId' => 'UserId','Status','CheckerTypeId')
            )
            ->join( array('UsCh' => 'User'), 'UsCh.Id = CE.UserId',
                array('CheckerName'=>new Zend_Db_Expr('concat(UsCh.FirstName," ",UsCh.Surname)')))
            ->columns(array('SocialName' => 'E.SocialName'))

        ;
        return $query;
    }

    protected function _queryReportInscricoes($query, $competitionId)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('State' => 'S.Name', 'S.Uf', 'AE.StateId'))
            ->join(array('LCE1_RI' => 'LogCadastroEmpresa'),
                'LCE1_RI.EnterpriseId = E.Id AND LCE1_RI.ProgramaId = ' . $competitionId, null
            )
            ->join(array('UsLCE1_RIRole' => 'User_Role'),
                'UsLCE1_RIRole.UserId = LCE1_RI.UserIdLog', null
            )
            ->columns(
                array('AceiteDigitadoresQt' =>
                    new Zend_Db_Expr("sum(CASE WHEN (LCE1_RI.Acao = 'aceite' and UsLCE1_RIRole.RoleId in ('32', '34')) THEN 1 ELSE 0 END)"))
            )
            ->columns(
                array('AceiteEmpresaQt' =>
                    new Zend_Db_Expr("sum(CASE WHEN (LCE1_RI.Acao = 'aceite' and UsLCE1_RIRole.RoleId not in ('32', '34')) THEN 1 ELSE 0 END)"))
            )
            ->group('AE.StateId')
            ->order('1');
        return $query;
    }

    protected function _queryReportInscricoesCategoria($query, $competitionId)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('CategoriaId' => 'E.CategorySectorId'))
            ->join(array('LCE1_RIC' => 'LogCadastroEmpresa'),
                'LCE1_RIC.EnterpriseId = E.Id AND LCE1_RIC.ProgramaId = ' . $competitionId, null
            )
            ->join(array('UsLCE1_RICRole' => 'User_Role'),
                'UsLCE1_RICRole.UserId = LCE1_RIC.UserIdLog', null
            )
            ->columns(
                array('AceiteDigitadoresQt' =>
                    new Zend_Db_Expr("sum(CASE WHEN (LCE1_RIC.Acao = 'aceite' and UsLCE1_RICRole.RoleId in ('32', '34')) THEN 1 ELSE 0 END)"))
            )
            ->columns(
                array('AceiteEmpresaQt' =>
                    new Zend_Db_Expr("sum(CASE WHEN (LCE1_RIC.Acao = 'aceite' and UsLCE1_RICRole.RoleId not in ('32', '34')) THEN 1 ELSE 0 END)"))
            )
            ->group('E.CategorySectorId')
            ->order('1');
        return $query;
    }

    protected function _queryListaAvaliador($query, $questionnaireId, $competitionId)
    {

        switch ($competitionId) {
            case 2014:
                $programaIdAvaliador = 50;
                break;

            default:
                $programaIdAvaliador = Zend_Registry::get('configDb')->programaIdAvaliador;
                break;
        }

        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'IdKey','SocialName','FantasyName',
                'DiagnosticoEligibility','Cnpj','S.Uf',
                'CpfUser'=>'P.Cpf',
                'NameUser'=>'P.Name',
                'AppraiserId'=>'ApE.UserId',
                'AppraiserIdSec' => 'ApESec.UserId',
                'AppraiserIdTer' => 'ApETer.UserId',
                'AppraiserStatus'=>'ApE.Status',
                'AppraiserDevolutiva'=>'ApE.Devolutiva',
                'AppraiserStatusSec' => 'ApESec.Status',
                'AppraiserDevolutivaSec' => 'ApESec.Devolutiva',
                'AppraiserStatusTer' => 'ApETer.Status',
                'AppraiserDevolutivaTer' => 'ApETer.Devolutiva',
                'EXE.DevolutivePath','EXE.EvaluationPath',
                'EXE.FinalScore',

                'AE.StreetNameFull', 'AE.StreetNumber', 'AE.StreetCompletion','AE.Cep',
                'PhoneUser'=>'P.Phone',
                'EmailUser'=>'P.Email',
            ))
        ;
        return $query;
    }

    protected function _queryClassificadas($query, $queryBeg, $filter, $competitionId)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'PontosGeral' => $this->_sqlPontosGeral(),
                'MediaPontos' => $this->_sqlMediaGeral(),
				'TotalPrimeiraFase' => $this->_sqlTotalPrimeiraFase(),
                'IdKey','SocialName','FantasyName',
                'DiagnosticoEligibility','Cnpj','S.Uf',
                'CpfUser'=>'P.Cpf', 'E.EmailDefault', 'Phone',
                'PA' => 'EXA.Progress',
                'EPR.MotivoDesclassificadoVerificacao','EPR.Classificar','EPR.Desclassificar','EPR.Justificativa',
                'EPR.ClassificadoVerificacao',
                'EPR.DesclassificadoVerificacao',
                'EPR.MotivoDesclassificadoFase2Nacional',
                'EPR.ClassificarFase2Nacional',
                'EPR.DesclassificarFase2Nacional',
                'DescriptionEca' => 'ECA.Description',
                'AppraiserId'=>'ApE.UserId',
                'NegociosTotal' => 'EP.NegociosTotal',
                'AppraiserIdSec' => 'ApESec.UserId',
                'AppraiserIdTer' => 'ApETer.UserId',
                'AppraiserStatus'=>'ApE.Status',
                'AppraiserStatusSec' => 'ApESec.Status',
                'AppraiserStatusTer' => 'ApETer.Status',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'EXE.DevolutivePath','EXE.EvaluationPath', 'EXE.FinalScore',
                'FirstNameAvaliadorTer'=>'UTer.FirstName','LoginAvaliadorTer'=>'UTer.Login',
                'FirstNameAvaliadorSec'=>'USec.FirstName','LoginAvaliadorSec'=>'USec.Login',
                'FirstNameAvaliadorPri'=>'U.FirstName','LoginAvaliadorPri'=>'U.Login',
                'QtdePontosFortes'=>'CE.QtdePontosFortes'
                
            ));
        if ($queryBeg) {
            $query->columns(array('PontosEmpreendedorismo'=>new Zend_Db_Expr("($queryBeg)")));
        }
        $query->joinleft(array('CE' => 'CheckerEnterprise'),
            'CE.EnterpriseId = E.Id AND CE.CheckerTypeId = 1 AND CE.ProgramaId ='.$competitionId,
            array('CheckerId' => 'UserId')
        )
            ->where("(ApESec.Status is not null or ApETer.Status is not null or ApE.Status is not null)")
            ->where("EXE.ProgramaId = ?", $competitionId)
            ->order('TotalPrimeiraFase DESC')
            ->order('1 DESC')
        ;
        return $query;
    }

    protected function _queryClassificadasNacional($query, $queryBeg, $filter, $competitionId)
    {
    	//$themeScoreExpr = new Zend_Db_Expr("sum(MTQ.QuestionWeight * AL.ScoreLevel / 100) as ThemeScore");
    	//$questionnaireId = 52;
    	
    	$query
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
			    'PontosVerificador' =>  $this->_sqlPontosVerificadorNac(),
                'PontosGeral' => $this->_sqlPontosGeral($comBonus = true),
                'MediaPontos' => $this->_sqlMediaGeral(),
                'IdKey', 'SocialName', 'FantasyName',
                'DiagnosticoEligibility', 'Cnpj','S.Uf',
                'CpfUser'=>'P.Cpf', 'E.EmailDefault', 'Phone',
                'PA' => 'EXA.Progress',
                'EPR.MotivoDesclassificadoVerificacao','EPR.Classificar','EPR.Desclassificar','EPR.Justificativa',
                'EPR.ClassificadoVerificacao', 'EPR.DesclassificadoVerificacao',
                'EPR.DesclassificadoFinal','EPR.MotivoDesclassificadoFinal',
                'EPR.ClassificadoOuro','EPR.ClassificadoPrata','EPR.ClassificadoBronze',
                'DescriptionEca' => 'ECA.Description',
                'AppraiserId' => 'ApE.UserId',
                'AppraiserIdSec' => 'ApESec.UserId',
                'AppraiserIdTer' => 'ApETer.UserId',
                'AppraiserStatus'=>'ApE.Status',
                'AppraiserStatusSec' => 'ApESec.Status',
                'AppraiserStatusTer' => 'ApETer.Status',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'EXE.DevolutivePath','EXE.EvaluationPath', 'EXE.FinalScore',
                'FirstNameAvaliadorTer'=>'UTer.FirstName','LoginAvaliadorTer'=>'UTer.Login',
                'FirstNameAvaliadorSec'=>'USec.FirstName','LoginAvaliadorSec'=>'USec.Login',
                'FirstNameAvaliadorPri'=>'U.FirstName','LoginAvaliadorPri'=>'U.Login',
                'NegociosTotal' => 'EP.NegociosTotal',
                'EnterpriseId' => 'ApE.EnterpriseId',
            	//'TotalSSS' => new Zend_Db_Expr("E.PontosVerificador * 0.2"), 
            ));
        if($queryBeg) {
            $query->columns(array('PontosEmpreendedorismo'=> new Zend_Db_Expr("($queryBeg)")));
        }

        //$query->columns(array('TotalSSS'=> new Zend_Db_Expr('PontosVerificador * 0.2') ));

        //->columns(array('Regional' =>new Zend_Db_Expr($queryRegional)))
        $query->join(array('CE' => 'CheckerEnterprise'),
            'CE.EnterpriseId = E.Id AND CE.CheckerTypeId = 1 AND CE.ProgramaId ='.$competitionId,
            array('CheckerId' => 'UserId', 'CheckerStatus' => 'Status', 'QtdePontosFortes',
            		'PontosFortesCalc' => $this->_sqlPontosPortes('QtdePontosFortes')
            )
        )
            ->joinleft(
                array('CheckerUsr' => 'User'), 'CheckerUsr.Id = CE.UserId',
                array('FirstNameChecker' => 'FirstName', 'LoginChecker' => 'Login')
            )
            ->where("(ApESec.Status is not null or ApETer.Status is not null or ApE.Status is not null)")
            ->where("EXE.ProgramaId = ?", $competitionId)
            ->order('PontosVerificador DESC' )
           // ->order('2 DESC')
        ;
// echo $query;
    	return $query;
    }
    
    protected function _queryFinalistas($query, $queryBeg, $filter, $competitionId)
    {
        $query
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
			    'PontosVerificador' =>  $this->_sqlPontosVerificador(),
                'PontosGeral' => $this->_sqlPontosGeral($comBonus = true),
                'MediaPontos' => $this->_sqlMediaGeral(),
                'IdKey', 'SocialName', 'FantasyName',
                'DiagnosticoEligibility', 'Cnpj','S.Uf',
                'CpfUser'=>'P.Cpf', 'E.EmailDefault', 'Phone',
                'PA' => 'EXA.Progress',
                'EPR.MotivoDesclassificadoVerificacao','EPR.Classificar','EPR.Desclassificar','EPR.Justificativa',
                'EPR.ClassificadoVerificacao', 'EPR.DesclassificadoVerificacao',
                'EPR.DesclassificadoFinal','EPR.MotivoDesclassificadoFinal',
                'EPR.ClassificadoOuro','EPR.ClassificadoPrata','EPR.ClassificadoBronze',
                'DescriptionEca' => 'ECA.Description',
                'AppraiserId' => 'ApE.UserId',
                'AppraiserIdSec' => 'ApESec.UserId',
                'AppraiserIdTer' => 'ApETer.UserId',
                'AppraiserStatus'=>'ApE.Status',
                'AppraiserStatusSec' => 'ApESec.Status',
                'AppraiserStatusTer' => 'ApETer.Status',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'EXE.DevolutivePath','EXE.EvaluationPath', 'EXE.FinalScore',
                'FirstNameAvaliadorTer'=>'UTer.FirstName','LoginAvaliadorTer'=>'UTer.Login',
                'FirstNameAvaliadorSec'=>'USec.FirstName','LoginAvaliadorSec'=>'USec.Login',
                'FirstNameAvaliadorPri'=>'U.FirstName','LoginAvaliadorPri'=>'U.Login',
                'NegociosTotal' => 'EP.NegociosTotal',
                'EnterpriseId' => 'ApE.EnterpriseId',
                /*
                'RegionalCity'=>'Rcity.Description',
                'RegionalState'=>'Rstate.Description'
                */
            ));
        if($queryBeg) {
            $query->columns(array('PontosEmpreendedorismo'=>new Zend_Db_Expr("($queryBeg)")));
        }
        //->columns(array('Regional' =>new Zend_Db_Expr($queryRegional)))
        $query->join(array('CE' => 'CheckerEnterprise'),
            'CE.EnterpriseId = E.Id AND CE.CheckerTypeId = 1 AND CE.ProgramaId ='.$competitionId,
            array('CheckerId' => 'UserId', 'CheckerStatus' => 'Status', 'QtdePontosFortes')
        )
            ->joinleft(
                array('CheckerUsr' => 'User'), 'CheckerUsr.Id = CE.UserId',
                array('FirstNameChecker' => 'FirstName', 'LoginChecker' => 'Login')
            )
            ->where("(ApESec.Status is not null or ApETer.Status is not null or ApE.Status is not null)")
            ->where("EXE.ProgramaId = ?", $competitionId)
            ->order('1 DESC')
            ->order('2 DESC')
        ;
//echo "final. ".$query;
        return $query;
    }

    protected function _queryFinalistasNacional($query, $queryBeg, $filter, $competitionId)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'PontosGeral' => $this->_sqlPontosGeral($comBonus = true),
                'MediaPontos' => $this->_sqlMediaGeral(),
                'IdKey', 'SocialName', 'FantasyName',
                'DiagnosticoEligibility', 'Cnpj','S.Uf',
                'CpfUser'=>'P.Cpf', 'E.EmailDefault', 'Phone',
                'PA' => 'EXA.Progress',
                'EPR.MotivoDesclassificadoVerificacao','EPR.Classificar','EPR.Desclassificar','EPR.Justificativa',
                'EPR.ClassificadoVerificacao', 'EPR.DesclassificadoVerificacao',
                'EPR.DesclassificadoFinal','EPR.MotivoDesclassificadoFinal',
                'EPR.ClassificadoOuro','EPR.ClassificadoPrata','EPR.ClassificadoBronze',
                'DescriptionEca' => 'ECA.Description',
                'AppraiserId' => 'ApE.UserId',
                'AppraiserIdSec' => 'ApESec.UserId',
                'AppraiserIdTer' => 'ApETer.UserId',
                'AppraiserStatus'=>'ApE.Status',
                'AppraiserStatusSec' => 'ApESec.Status',
                'AppraiserStatusTer' => 'ApETer.Status',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'EXE.DevolutivePath','EXE.EvaluationPath', 'EXE.FinalScore',
                'FirstNameAvaliadorTer'=>'UTer.FirstName','LoginAvaliadorTer'=>'UTer.Login',
                'FirstNameAvaliadorSec'=>'USec.FirstName','LoginAvaliadorSec'=>'USec.Login',
                'FirstNameAvaliadorPri'=>'U.FirstName','LoginAvaliadorPri'=>'U.Login',
                'NegociosTotal' => 'EP.NegociosTotal'
            ));
        if ($queryBeg) {
            $query->columns(array('PontosEmpreendedorismo'=>new Zend_Db_Expr("($queryBeg)")));
        }

        $query->join(array('CE' => 'CheckerEnterprise'),
            'CE.EnterpriseId = E.Id AND CE.CheckerTypeId = 1 AND CE.ProgramaId ='.$competitionId,
            array('CheckerId' => 'UserId', 'CheckerStatus' => 'Status', 'QtdePontosFortes')
        )
            ->joinleft(
                array('CheckerUsr' => 'User'), 'CheckerUsr.Id = CE.UserId',
                array('FirstNameChecker' => 'FirstName', 'LoginChecker' => 'Login')
            )
            ->where("(ApESec.Status is not null or ApETer.Status is not null or ApE.Status is not null)")

            ->where("EXE.ProgramaId = ?", $competitionId)
            ->where('EPR.ClassificarFase2Nacional = 1')
            ->order('1 DESC')
            ->order('2 DESC')
        ;
//echo "final.nac. ".$query;
        return $query;
    }

    protected function _queryReportDigitador($query, $programaId)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->join(array('LCE_RD' => 'LogCadastroEmpresa'),
                'LCE_RD.EnterpriseId = E.Id AND LCE_RD.ProgramaId = ' . $programaId,
                array('UserIdLog')
            )
            ->joinLeft(array('UsLCE_RD' => 'User'),
                'UsLCE_RD.Id = LCE_RD.UserIdLog',
                array('FirstName', 'SurName')
            )
            ->join(array('UsLCE_RDRole' => 'User_Role'),
                'UsLCE_RDRole.UserId = UsLCE_RD.Id', null
            )
            ->columns(
                array('UfDigitador' => 'S.Uf')
            )
            ->columns(
                array('AceiteQt' =>
                    new Zend_Db_Expr('sum(CASE WHEN (LCE_RD.Acao = "aceite") THEN 1 ELSE 0 END)'))
            )
            ->columns(
                array('EdicaoCompletaQt' =>
                    new Zend_Db_Expr('sum(CASE WHEN (LCE_RD.Acao = "edicao_completa") THEN 1 ELSE 0 END)'))
            )
            ->columns(
                array('DevolutivaQt' =>
                    new Zend_Db_Expr('sum(CASE WHEN (LCE_RD.Acao = "devolutiva") THEN 1 ELSE 0 END)'))
            )
            ->columns(
                array('DevolutivaRegeradaQt' =>
                    new Zend_Db_Expr('sum(CASE WHEN (LCE_RD.Acao = "devolutiva-regerada") THEN 1 ELSE 0 END)'))
            )
            ->where('UsLCE_RDRole.RoleId in (?)',
                array(
                    Zend_Registry::get('config')->acl->roleDigitadorId,
                    Zend_Registry::get('config')->acl->roleGestorId,
                )
            )
            ->group('LCE_RD.UserIdLog')
            ->order('4')
            ->order('2');
        return $query;
    }

    protected function _queryReportCategoria($query)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('CategoriaId' => 'E.CategorySectorId'))
            ->columns(array('Inscritas' => new Zend_Db_Expr("count(E.Id)")))
            ->columns(
                array('Candidatas' =>
                    new Zend_Db_Expr("sum(CASE WHEN (EXE.DevolutivePath is not null) THEN 1 ELSE 0 END)"))
            )
            ->group('E.CategorySectorId');
        return $query;
    }

    protected function _queryReportCategoriaPremio($query)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('E.CategoryAwardId'))
            ->columns(array('Inscritas' => new Zend_Db_Expr("count(E.Id)")))
            ->columns(
                array('Candidatas' =>
                    new Zend_Db_Expr("sum(CASE WHEN (EXE.DevolutivePath is not null) THEN 1 ELSE 0 END)"))
            )
            ->group('E.CategoryAwardId');

        return $query;
    }

    protected function _queryReportRegional($query, $tipo)
    {
        switch ($tipo) {
            case 'bairros': $join = 'SA2.NeighborhoodId = AE.NeighborhoodId'; break;
            case 'cidades': $join = 'SA2.CityId = AE.CityId'; break;
            case 'estados': $join = 'SA2.StateId = AE.StateId and SA2.RegionalId <> 1'; break;
        }
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns('S.Uf')
            ->join(array('SA2' => 'ServiceArea'), $join, null)
            ->join(array('R2' => 'Regional'), 'R2.Id = SA2.RegionalId', null)
            ->joinLeft(array('Regiao2' => 'Regiao'), 'Regiao2.Id = R2.RegiaoId', array('Regiao' => 'Descricao'))
            ->columns(array('RegionalDescription' => 'R2.Description'))
            ->columns(array('Inscritas' => new Zend_Db_Expr("count(E.Id)")))
            ->columns(
                array('Candidatas' =>
                    new Zend_Db_Expr("sum(CASE WHEN (EXE.DevolutivePath is not null) THEN 1 ELSE 0 END)"))
            )
            ->group('R2.Id');
        return $query;
    }

    protected function _queryReportGlobalRespostas($query, $filter, $programaId)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->join(array('Ans' => 'Answer'), 'Ans.UserId = USL.UserId', null)
            ->columns('Ans.AlternativeId')
            ->columns(array('qtd' => new Zend_Db_Expr("count(Ans.Id)")))
            ->where('Ans.AlternativeId in (?)', $filter['alternativesId'])
            //->where('Ans.ProgramaId = ?', $programaId)
            ->group('Ans.AlternativeId');
        return $query;
    }

    protected function _queryReportGlobalCriterios($query)
    {
        $query->reset(Zend_Db_Select::COLUMNS)
            ->join(array('ExPo' => 'ExecutionPontuacao'), 'ExPo.ExecutionId = EXE.Id', null)
            ->columns(array('total' => new Zend_Db_Expr('"total"')))
            ->columns(array('Liderança' => new Zend_Db_Expr("avg(ExPo.GestaoLideranca)")))
            ->columns(array('Estratégias' => new Zend_Db_Expr("avg(ExPo.GestaoEstrategias)")))
            ->columns(array('Clientes' => new Zend_Db_Expr("avg(ExPo.GestaoClientes)")))
            ->columns(array('Sociedade' => new Zend_Db_Expr("avg(ExPo.GestaoSociedade)")))
            ->columns(array('Informações' => new Zend_Db_Expr("avg(ExPo.GestaoInformacoes)")))
            ->columns(array('Pessoas' => new Zend_Db_Expr("avg(ExPo.GestaoPessoas)")))
            ->columns(array('Processos' => new Zend_Db_Expr("avg(ExPo.GestaoProcessos)")))
            ->columns(array('Resultados' => new Zend_Db_Expr("avg(ExPo.GestaoResultados)")));
        return $query;
    }

    public function getEnterpriseByUserId($userId)
    {
        $this->camposEnterprise[] = 'Id';
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('E' => 'Enterprise'),
                $this->camposEnterprise
            )
            ->join(
                array('UL' => 'UserLocality'), 'UL.EnterpriseId = E.Id',
                null
            )
            ->where("UL.UserId = ?", $userId);

        $objResult = $this->fetchRow($query);
        return $objResult;
    }

    public function getEnterpriseByUserEmailDefault($login, $email)
    {
					

        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
			 array('U' => 'User')
              ///  array('E' => $this->_name),
               // array('EmailDefault')
            )
           /* ->join(
                array('UL' => 'UserLocality'), 'UL.EnterpriseId = E.Id', null
            )
            ->join(
                array('P' => 'President'), 'P.EnterpriseId = E.Id',
                array('Email')
            )*/
           /* ->join(
                array('U' => 'User'), 'U.Id = UL.UserId', array('Id', 'FirstName', 'Login')
            )*/
			
			->where("U.Email = ?", $email);
			//->where ("U.Cpf = ?",str_replace("-","",str_replace(".","",$login)));
			//->where ("U.Login = '" . $login ."' OR U.Cpf = '" . str_replace("-","",str_replace(".","",$login)) . "'");		
           // ->where("U.Login = ?", $login)
          
			
			
      //	var_dump($query);exit;
 
        $objResult = $this->fetchRow($query);
		
	
        return $objResult;
    }

    public function getEnterpriseByEmailDefault($email, $competitionId){
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('E' => $this->_name)
            )
            ->join(
                array('ECAC' => 'EnterpriseCategoryAwardCompetition'), 'ECAC.EnterpriseId = E.Id', null
            )
            ->where("E.EmailDefault = ?", $email)
            ->where("ECAC.CompetitionId = ?", $competitionId)
        ;
        $objResult = $this->fetchRow($query);

        return $objResult;
    }

    public function setDiagnosticoEligibility($enterpriseId, $eligibility)
    {
        $enterprise = $this->fetchRow(array('Id = ?' => $enterpriseId));

        if ($enterprise->getDiagnosticoEligibility() != $eligibility) {
            $enterprise->setDiagnosticoEligibility($eligibility);
            $enterprise->save();
        }
        return true;
    }

    public function setAutoavaliacaoEligibility($enterpriseId, $eligibility)
    {
        $enterprise = $this->fetchRow(array('Id = ?' => $enterpriseId));

        if ($enterprise->getAutoavaliacaoEligibility() != $eligibility) {
            $enterprise->setAutoavaliacaoEligibility($eligibility);
            $enterprise->save();
        }
        return true;
    }

    public function setPremioEligibility($enterpriseId, $eligibility)
    {
        $enterprise = $this->fetchRow(array('Id = ?' => $enterpriseId));

        if ($enterprise->getPremioEligibility() != $eligibility) {
            $enterprise->setPremioEligibility($eligibility);
            $enterprise->save();
        }
        return true;
    }

    protected function _queryCheckerList($query, $questionnaireId, $filter, $competitionId)
    {
        $programaIdAvaliador = Zend_Registry::get('configDb')->programaIdAvaliador;
        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'IdKey','SocialName','FantasyName',
                'DiagnosticoEligibility','Cnpj','S.Uf',
                'CpfUser'=>'P.Cpf',
                'NameUser'=>'P.Name',
                'EXE.DevolutivePath','EXE.EvaluationPath',
                'EXE.FinalScore'
            ))
            ->join(array('CE' => 'CheckerEnterprise'),
                'CE.EnterpriseId = E.Id AND CE.CheckerTypeId = 1 AND CE.ProgramaId ='.$competitionId
                . (
                (isset($filter['checker_id']) and $filter['checker_id']) ?
                    " AND CE.UserId = {$filter['checker_id']} and CE.Status != 'C'" : ''
                ),
                array('CheckerId' => 'UserId', 'CheckerStatus' => 'Status')
            )
            ->where("EXE.ProgramaId = ?", $competitionId)
        ;
        return $query;
    }   

    public function getEnterpriseScoreAppraisersData($enterpriseId, $competitionId) {
        $configDb = Zend_Registry::get('configDb');
        switch ($competitionId) {
            case 2013:
                $currentAutoavaliacaoId = 50;
                break;
            default:
                $currentAutoavaliacaoId = ($configDb->qstn->currentAutoavaliacaoId)? $configDb->qstn->currentAutoavaliacaoId:'"null"';
                break;
        }
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('E' => 'Enterprise'), null)
            ->where('E.Id = ?', $enterpriseId)
            ->join(
                array('USL' => 'UserLocality'), 'USL.EnterpriseId = E.Id',null
            )
            ->join(
                array('ECAC' => 'EnterpriseCategoryAwardCompetition'),
                'ECAC.EnterpriseId = E.Id AND ECAC.CompetitionId = '.$competitionId,
                null
            )
            ->joinleft(
                array('EPR' => 'EnterpriseProgramaRank'),
                "EPR.EnterpriseIdKey = E.IdKey AND EPR.ProgramaId = $competitionId",
                array('Classificar','Desclassificar','Justificativa')
            )
            ->joinleft(
                array('EXA' => 'Execution'),
                'EXA.UserId = USL.UserId AND EXA.QuestionnaireId = ' . $currentAutoavaliacaoId
                .' AND EXA.ProgramaId = '.$competitionId,
                array('PA' => 'Progress')
            )
        ;

        // Avaliador Primario
        $query
            ->joinleft(
                array('ApE' => 'AppraiserEnterprise'),
                'ApE.EnterpriseId = E.Id AND ApE.AppraiserTypeId = 1 AND ApE.ProgramaId ='.$competitionId,
                array('AppraiserId' => 'UserId')
            )
            ->joinleft(
                array('U' => 'User'), 'U.Id = ApE.UserId', null
            )
            // Avaliador Secundario
            ->joinleft(
                array('ApESec' => 'AppraiserEnterprise'),
                'ApESec.EnterpriseId = E.Id AND ApESec.AppraiserTypeId = 2 AND ApESec.ProgramaId ='.$competitionId,
                array('AppraiserIdSec' => 'UserId')
            )
            ->joinleft(
                array('USec' => 'User'), 'USec.Id = ApESec.UserId', null
            )
            // Avaliador Terciario
            ->joinleft(
                array('ApETer' => 'AppraiserEnterprise'),
                'ApETer.EnterpriseId = E.Id AND ApETer.AppraiserTypeId = 3 AND ApETer.ProgramaId ='.$competitionId,
                array('AppraiserIdTer' => 'UserId')
            )
            ->joinleft(
                array('UTer' => 'User'), 'UTer.Id = ApETer.UserId', null
            );

        $query
            ->joinleft(
                array('EP' => 'ExecutionPontuacao'),
                'EP.ExecutionId = EXA.Id',
                array('NegociosTotal')
            );

        $query
            ->joinleft(array('CE' => 'CheckerEnterprise'),
                'CE.EnterpriseId = E.Id AND CE.CheckerTypeId = 1 AND CE.ProgramaId ='.$competitionId,
                null
            )
            ->joinleft(
                array('CheckerUsr' => 'User'), 'CheckerUsr.Id = CE.UserId'
            );

        $query->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                //3831-7872
                //3831-4705
                'PontosGeral' => $this->_sqlPontosGeral(),
                'MediaPontos' => $this->_sqlMediaGeral(),
                'PA' => 'EXA.Progress',
                'EPR.MotivoDesclassificadoVerificacao','EPR.Classificar',
                'EPR.Desclassificar','EPR.Justificativa',
                'EPR.ClassificadoVerificacao','EPR.DesclassificadoVerificacao',
                'AppraiserId'=>'ApE.UserId',
                'NegociosTotal' => 'EP.NegociosTotal',
                'AppraiserIdSec' => 'ApESec.UserId',
                'AppraiserIdTer' => 'ApETer.UserId',
                'AppraiserStatus'=>'ApE.Status',
                'AppraiserStatusSec' => 'ApESec.Status',
                'AppraiserStatusTer' => 'ApETer.Status',
                'Pontos' => 'ApE.Pontos',
                'PontosSec' => 'ApESec.Pontos',
                'PontosTer' => 'ApETer.Pontos',
                'Conclusao' => 'ApE.Conclusao',
                'ConclusaoSec' => 'ApESec.Conclusao',
                'ConclusaoTer' => 'ApETer.Conclusao',
                'ConclusaoDate' => 'ApE.ConclusaoDate',
                'ConclusaoDateSec' => 'ApESec.ConclusaoDate',
                'ConclusaoDateTer' => 'ApETer.ConclusaoDate',
                'FirstNameAvaliadorTer' =>'UTer.FirstName','LoginAvaliadorTer'=>'UTer.Login',
                'FirstNameAvaliadorSec'=>'USec.FirstName','LoginAvaliadorSec'=>'USec.Login',
                'FirstNameAvaliadorPri'=>'U.FirstName','LoginAvaliadorPri'=>'U.Login',
                'CheckerId' => 'CE.UserId', 'CheckerStatus' => 'CE.Status',
                'CE.QtdePontosFortes',
                'CheckerConclusao' => 'CE.Conclusao',
                'CheckerConclusaoDate' => 'CE.ConclusaoDate',
                'FirstNameChecker' => 'CheckerUsr.FirstName',
                'LoginChecker' => 'CheckerUsr.Login',                                
            ))
            ->where("EXA.ProgramaId = ?", $competitionId);
        #echo '<!-- '.$query->__toString().' -->'; echo '<pre>'; echo $query; die;
        return $this->fetchRow($query);
    }

    protected function _sqlPontosGeral($comBonus = false)
    {
        $queryPontos = "(CASE
            WHEN
                (((CASE
                    WHEN (`ApE`.`Status` is not null) THEN 1
                    ELSE 0
                END) + (CASE
                    WHEN (`ApESec`.`Status` is not null) THEN 1
                    ELSE 0
                END) + (CASE
                    WHEN (`ApETer`.`Status` is not null) THEN 1
                    ELSE 0
                END)) = ((CASE
                    WHEN (`ApE`.`Status` = 'C') THEN 1
                    ELSE 0
                END) + (CASE
                    WHEN (`ApESec`.`Status` = 'C') THEN 1
                    ELSE 0
                END) + (CASE
                    WHEN (`ApETer`.`Status` = 'C') THEN 1
                    ELSE 0
                END)))
            THEN
                ((`EP`.`NegociosTotal` * 0.2) / 10) + ((((case
                    when `ApE`.`Pontos` is not null then `ApE`.`Pontos`
                    else 0
                end) + (case
                    when `ApESec`.`Pontos` is not null then `ApESec`.`Pontos`
                    else 0
                end) + (case
                    when `ApETer`.`Pontos` is not null then `ApETer`.`Pontos`
                    else 0
                end)) / ((CASE
                    WHEN (`ApE`.`Status` = 'C') THEN 1
                    ELSE 0
                END) + (CASE
                    WHEN (`ApESec`.`Status` = 'C') THEN 1
                    ELSE 0
                END) + (CASE
                    WHEN (`ApETer`.`Status` = 'C') THEN 1
                    ELSE 0
                END)) * 0.8) / 1000)
            ELSE '-'
        END)";

        if ($comBonus) {
            $queryPontos = $queryPontos . '*'
                . "(CASE "
                . "WHEN CE.QtdePontosFortes is null THEN 1 "
                . "WHEN CE.QtdePontosFortes >= 8 THEN 1.2 "
                . "WHEN CE.QtdePontosFortes >= 5 THEN 1.1 "
                ."ELSE 1 END)";
        }

        return new Zend_Db_Expr($queryPontos);
    }

    protected function _sqlMediaGeral()
    {
        return new Zend_Db_Expr(
            "((case
                when (`ApE`.`Status` = 'C' and `ApE`.`Pontos` is not null) then `ApE`.`Pontos`
                else 0
            end) + (case
                when (`ApESec`.`Status` = 'C' and `ApESec`.`Pontos` is not null) then `ApESec`.`Pontos`
                else 0
            end) + (case
                when (`ApETer`.`Status` = 'C' and `ApETer`.`Pontos` is not null) then `ApETer`.`Pontos`
                else 0
            end)) / ((CASE
                WHEN (`ApE`.`Status` = 'C') THEN 1
                ELSE 0
            END) + (CASE
                WHEN (`ApESec`.`Status` = 'C') THEN 1
                ELSE 0
            END) + (CASE
                WHEN (`ApETer`.`Status` = 'C') THEN 1
                ELSE 0
            END))"
        );
    }


	protected function _sqlPontosVerificador()
	{
		return new Zend_Db_Expr(
		"(SELECT  `APEV`.PontosFinal FROM `AppraiserEnterprise` AS `APEN` INNER JOIN `ApeEvaluationVerificador` AS `APEV` ON APEN.Id = APEV.AppraiserEnterpriseId WHERE (APEN.EnterpriseId = `ApE`.`EnterpriseId` and `APEN`.UserId = `CE`.`UserId`) order by `APEV`.PontosFinal desc limit 1) * 0.6  +

(SELECT sum(MTQ.QuestionWeight * AL.ScoreLevel / 100) / 8 as ThemeScore  FROM `ManagementTheme` AS `MT` INNER JOIN `ManagementThemeQuestion` AS `MTQ` ON MTQ.ManagementThemeId = MT.Id INNER JOIN `Question` AS `Q` ON Q.Id = MTQ.QuestionId INNER JOIN `Alternative` AS `AL` ON AL.QuestionId = Q.Id INNER JOIN `AnswerVerificador` AS `AN` ON AN.AlternativeId = AL.Id  INNER JOIN `Criterion` AS `C` ON C.Id = Q.CriterionId  INNER JOIN `Block` AS `B` ON B.Id = C.BlockId  INNER JOIN `Questionnaire` ON Questionnaire.id = B.QuestionnaireId WHERE (Questionnaire.Id = 52) AND (AN.EnterpriseId = USL.UserId)) * 0.2 +

(case when (CE.QtdePontosFortes > 0 and CE.QtdePontosFortes < 9) then CE.QtdePontosFortes * 2 when (CE.QtdePontosFortes = 9) then 17 when (CE.QtdePontosFortes = 10) then 18 when (CE.QtdePontosFortes = 11) then 19 when (CE.QtdePontosFortes = 12) then 20 else 0 end)
"
		);
	}
	
	protected function _sqlPontosVerificadorNac()
	{
		return new Zend_Db_Expr(
				"(SELECT sum(MTQ.QuestionWeight * AL.ScoreLevel / 100) / 8 as ThemeScore  FROM `ManagementTheme` AS `MT` 
				INNER JOIN `ManagementThemeQuestion` AS `MTQ` ON MTQ.ManagementThemeId = MT.Id 
				INNER JOIN `Question` AS `Q` ON Q.Id = MTQ.QuestionId 
				INNER JOIN `Alternative` AS `AL` ON AL.QuestionId = Q.Id 
				INNER JOIN `AnswerVerificador` AS `AN` ON AN.AlternativeId = AL.Id  
				INNER JOIN `Criterion` AS `C` ON C.Id = Q.CriterionId  
				INNER JOIN `Block` AS `B` ON B.Id = C.BlockId  
				INNER JOIN `Questionnaire` ON Questionnaire.id = B.QuestionnaireId 
				WHERE (Questionnaire.Id = 52) AND (AN.EnterpriseId = USL.UserId)) *0.2 + 
				(case when (CE.QtdePontosFortes > 0 and CE.QtdePontosFortes < 9) then CE.QtdePontosFortes * 2 
				when (CE.QtdePontosFortes = 9) then 17 when (CE.QtdePontosFortes = 10) then 18 
				when (CE.QtdePontosFortes = 11) then 19 when (CE.QtdePontosFortes = 12) then 20 else 0 end) + 
				((case
                when (`ApE`.`Status` = 'C' and `ApE`.`Pontos` is not null) then `ApE`.`Pontos`
                else 0
            end) + (case
                when (`ApESec`.`Status` = 'C' and `ApESec`.`Pontos` is not null) then `ApESec`.`Pontos`
                else 0
            end) + (case
                when (`ApETer`.`Status` = 'C' and `ApETer`.`Pontos` is not null) then `ApETer`.`Pontos`
                else 0
            end)) / ((CASE
                WHEN (`ApE`.`Status` = 'C') THEN 1
                ELSE 0
            END) + (CASE
                WHEN (`ApESec`.`Status` = 'C') THEN 1
                ELSE 0
            END) + (CASE
                WHEN (`ApETer`.`Status` = 'C') THEN 1
                ELSE 0
            END)) * 0.6"
		);
	}

	protected function _sqlPontosPortes($pontos)
	{
		return new Zend_Db_Expr($pontos * 2);
	}
	
	protected function _sqlTotalPrimeiraFase()
	{
		return new Zend_Db_Expr(
			"((((case
				when (`ApE`.`Status` = 'C' and `ApE`.`Pontos` is not null) then `ApE`.`Pontos`
				else 0
			end) + (case
				when (`ApESec`.`Status` = 'C' and `ApESec`.`Pontos` is not null) then `ApESec`.`Pontos`
				else 0
			end) + (case
				when (`ApETer`.`Status` = 'C' and `ApETer`.`Pontos` is not null) then `ApETer`.`Pontos`
				else 0
			end)) / ((CASE
				WHEN (`ApE`.`Status` = 'C') THEN 1
				ELSE 0
			END) + (CASE
				WHEN (`ApESec`.`Status` = 'C') THEN 1
				ELSE 0
			END) + (CASE
				WHEN (`ApETer`.`Status` = 'C') THEN 1
				ELSE 0
			END)) + `EP`.`NegociosTotal`) /2)"
		);
	}
    private function groupByStringFor($groupBy)
    {
        $groupByString = '';

        if ($groupBy) {
            if (!is_array($groupBy)) $groupBy = array($groupBy);

            foreach ($groupBy as $key) {
                if (strlen($groupByString) > 0) $groupByString .= ',';
                switch ($key) {
                    case 'enterprise_id':
                        $groupByString .= 'E.Id';
                        break;
                    default:
                        $groupByString = substr($groupByString, 0, -1);
                }
            }
        }

        return strlen($groupByString) > 0 ? $groupByString : null;
    }

    public function getEnterpriseScoreAppraiserAnwserVerificadorData($enterpriseId, $competitionId) {
        $configDb = Zend_Registry::get('configDb');
    
        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('APEN' => 'AppraiserEnterprise'), null)
        ->where('APEV.AppraiserEnterpriseId = ?', $enterpriseId)
        ->join(
            array('APEV' => 'ApeEvaluationVerificador'), 'APEN.Id = APEV.AppraiserEnterpriseId',null
        )
        ->join(
            array('AVPE' => 'AvaliacaoPerguntas'), 'APEV.AvaliacaoPerguntaId = AVPE.ID',null
        );
    
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns(array(
            'APEN.USERID',
            'APEN.AppraiserTypeId',
            'APEV.AppraiserEnterpriseId',
            'APEV.AvaliacaoPerguntaId',
            'APEV.Resposta',
            'APEV.PontosFinal',
            'AVPE.Criterio',
            'AVPE.BLOCO',
            'AVPE.QuestaoLetra'
        ))
        ;
        return $this->fetchRow($query);
    }
    
    public function getEnterpriseCheckerEnterprisePontosFortes($enterpriseId, $competitionId) {       
        
        $configDb = Zend_Registry::get('configDb');
    
        $query = $this->select()
        ->setIntegrityCheck(false)
        ->from(array('CHE' => 'CheckerEnterprise'), null)
        ->where('CHE.EnterpriseId = ?', $enterpriseId);
    
        $query->reset(Zend_Db_Select::COLUMNS)
        ->columns(array(
            'CHE.QtdePontosFortes'
        ));
        return $this->fetchRow($query);
    }
	
	public function setTempVal($id,$col,$val){
		$row = $this->fetchRow($this->select()->where('Id = ?', $id));
		// Change the value of one or more columns
		$row->$col = $val;
		 
		// UPDATE the row in the database with new values
		$row->save();
	}
}
