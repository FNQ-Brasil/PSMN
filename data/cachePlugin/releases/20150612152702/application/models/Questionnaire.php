<?php

class Model_Questionnaire
{
    protected $_messagesError = array(
        'questionnaireFormError' => 'Erro no preenchimento do campo: ',
        'questionnaireExistsReg' => 'Registro com título já existente na base.',
        'questionnaireExistsDep' => 'Existem registros relacionados ao questionário.'
    );
    
    public $tbQuestionnaire = "";
    
    function __construct() {
        $this->tbQuestionnaire = new DbTable_Questionnaire();
    }

    /**
     * 
     * Verifica se usuario respondeu completamente:
     * - preencheu Relato
     * - bloco Empreendedorismo
     * - bloco Negocios
     * - gerou Devolutiva
     * 
     * @param type $enterpriseRow
     * @return Array
     * 
       $arrTerminoEtapas = array(
            'relato' => $relato,
            'negocios' => $negocios,
            'empreemdedorismo' => $empreendedorismo,
            'devolutiva' => $gerouDevolutiva,
       );   
     *
     */
    public function terminoEtapas($enterpriseId, $userId)
    {
        //config
        $questionarioCurrentId = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;       
        $negociosBlockId = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
        $enterpreneurBlockId = Zend_Registry::get('configDb')->qstn->currentBlockIdEmpreendedorismo;        
        
        //models
        $model_enterpriseReport = new Model_EnterpriseReport();
        $model_execution = new Model_Execution();
        
        //variades de retorno
        $relato = false;
        $gerouDevolutiva = false;
        $negocios = true;
        $empreendedorismo = true;
        
        //$enterpriseId = $enterpriseRow->getId();
        
        $relatoRow = $model_enterpriseReport->getEnterpriseReport("EnterpriseId = ".$enterpriseId);
        $empreendedorismoRow = $this->isFullyAnsweredByBloco($questionarioCurrentId, $userId, $enterpreneurBlockId);
        $negociosRow = $this->isFullyAnsweredByBloco($questionarioCurrentId, $userId, $negociosBlockId);

        $reportId = $relatoRow? $relatoRow->getId() : null;
        
        //se respondeu todo bloco de Empreendedorismo
        if (!$empreendedorismoRow) {
            $empreendedorismo = false;
        }          

        //se respondeu todo bloco de Negocios
        if (!$negociosRow) {
            $negocios = false;
        }          
                
        //tem relato
        if ($reportId) {
            $relato = true;
        }
        //row execution
        $devolutive = $model_execution->getExecutionByUserAndQuestionnaire($questionarioCurrentId, $userId);
        
        //string - url da devolutiva gerada
        $devolutivePath = $devolutive? $devolutive->getDevolutivePath() : null;
        
        //gerou devvolutiva
        if ($devolutivePath) {
            $gerouDevolutiva = true;
        }
        
        //return
        $arrTerminoEtapas = array(
            'relato' => $relato,
            'negocios' => $negocios,
            'empreendedorismo' => $empreendedorismo,
            'devolutiva' => $gerouDevolutiva,
        );        
        
        return $arrTerminoEtapas;
            
    }
    
    public function getCurrentQstnRow()
    {
        $qstnId = Zend_Registry::get('configDb')->qstn->currentQuestionnaireId;
        $currentQstn = DbTable_Questionnaire::getInstance()->find(50)->current();
        return $currentQstn;
    }
    
    function createQuestionnaire($data)
    {
        DbTable_Questionnaire::getInstance()->getAdapter()->beginTransaction();
        
        try {
            $data = $this->_filterInputIdentify($data)->getUnescaped();
            
            $verifyQuestionnaire = DbTable_Questionnaire::getInstance()->fetchRow(array(
                'Title = ?' => $data['title']
            ));

            if ($verifyQuestionnaire) {
                throw new Vtx_UserException($this->_messagesError['questionnaireExistsReg']);
            }
            
            $row = DbTable_Questionnaire::getInstance()->createRow()
                ->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setLongDescription($data['long_description'])
                ->setOperationBeginning(Vtx_Util_Date::format_iso($data['operation_beginning']))
                ->setOperationEnding(Vtx_Util_Date::format_iso($data['operation_ending']))
                ->setPublicSubscriptionEndsAt(Vtx_Util_Date::format_iso($data['public_subscription_ends_at']))
                ->setInternalSubscriptionEndsAt(Vtx_Util_Date::format_iso($data['internal_subscription_ends_at']))
                ->setDevolutiveCalcId($data['devolutive_id']);
            $row->save();

            DbTable_Questionnaire::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    
    public function updateQuestionnaire($questionnaireRow, $data)
    {
        DbTable_Questionnaire::getInstance()->getAdapter()->beginTransaction();
        try {
            
            $data = $this->_filterInputIdentify($data)->getUnescaped();
            
            $verifyQuestionnaire = DbTable_Questionnaire::getInstance()->fetchRow(array(
                'Title = ?' => $data['title'],
                'Id <> ?' => $questionnaireRow->getId()
            ));
             
            if ($verifyQuestionnaire) {
                return array(
                    'status' => false, 'messageError' => $this->_messagesError['questionnaireExistsReg']
                );
            }
           
            $questionnaireRow->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setLongDescription($data['long_description'])
                ->setOperationBeginning(Vtx_Util_Date::format_iso($data['operation_beginning']))
                ->setOperationEnding(Vtx_Util_Date::format_iso($data['operation_ending']))
                ->setPublicSubscriptionEndsAt(Vtx_Util_Date::format_iso($data['public_subscription_ends_at']))
                ->setInternalSubscriptionEndsAt(Vtx_Util_Date::format_iso($data['internal_subscription_ends_at']))
                ->setDevolutiveCalcId($data['devolutive_id']);;
            $questionnaireRow->save();
            DbTable_Question::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function deleteQuestionnaire($Identify)
    {
        $verifyBlock = DbTable_Block::getInstance()->fetchRow(array(
            'QuestionnaireId = ?' => $Identify
        ));

        if ($verifyBlock) {
            return array(
                'status' => false, 'messageError' => $this->_messagesError['questionnaireExistsDep']
            );
        }

        $row = $this->tbQuestionnaire->find($Identify)
            ->current()
            ->delete();
        return array(
            'status' => true
        );

    }
    
    function getQuestionnaireById($Identify)
    {
        return DbTable_Questionnaire::getInstance()->getQuestionnaireById($Identify);
    }
    
    public function getAll($where = null, $order = 'OperationBeginning', $count = null, $offset = null)
    {
        $query = DbTable_Questionnaire::getInstance()->select();
        if ($where) {
            $query->where($where);
        }
        if ($order) {
            $query->order($order);
        }
        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
    }
    
    protected function _filterInputIdentify($parameters)
    {
		$filters = array(
                '*' => 'StripTags', 
                'operation_beginning' => 'StringTrim',
                'operation_ending' => 'StringTrim'
            );
        
        $validator = array(
                'title' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['questionnaireFormError'].'Título'
                ),
                'description' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['questionnaireFormError'].'Descrição'
                ),
                'long_description' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['questionnaireFormError'].'Texto Complementar'
                ),
                'operation_beginning' => array(
                    'NotEmpty', 
                     new Zend_Validate_Date('dd/MM/yyyy')
                ),  
                'operation_ending' => array(
                    'NotEmpty', 
                     new Zend_Validate_Date('dd/MM/yyyy')
                ),
                'public_subscription_ends_at' => array(
                    'allowEmpty' => true
                ),
                'internal_subscription_ends_at' => array(
                    'allowEmpty' => true
                ),
                'devolutive_id' => array(
                    'NotEmpty',
                    'messages' => $this->_messagesError['questionnaireFormError'].'Tipo de Devolutiva'
                ),
            );
        
        $options = array(
                'presence' => 'required'
            );
        
        $input = new Zend_Filter_Input($filters,$validator,$parameters,$options);
        
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        
        return $input;
        
    }

    public function getCurrentExecution()
    {
        // questionario corrente
        $currentAutoavaliacaoId = Zend_Registry::get('configDb')->qstn->currentAutoavaliacaoId;
        return DbTable_Questionnaire::getInstance()
            ->find($currentAutoavaliacaoId)->current();
    }

    public function isQuestionnaireExecution($QstnId) 
    {
        $date = new Zend_Date();
        $now = $date->getIso();
        $where = array(
            'Id = ?' => $QstnId,'OperationBeginning <= ?' => $now, 'OperationEnding >= ?' => $now
        );
        return DbTable_Questionnaire::getInstance()->fetchRow($where, 'OperationBeginning');
    }

    public function getQuestionnairesByDevolutiveCalcId($devolutiveCalcId)
    {
        $where = array(
            'DevolutiveCalcId = ?' => $devolutiveCalcId
        );
        return DbTable_Questionnaire::getInstance()->fetchAll($where);
    }
    
    public function getQuestionsAnsweredByUserId($QstnId, $UserId, $blockId = false)
    {
        return DbTable_Questionnaire::getInstance()
            ->getQuestionsAnsweredByUserId($QstnId, $UserId, 'assoc', $blockId);
    }   
    
    public function getBlocksAutoavaliacao($QstnId)
    {
        return DbTable_Questionnaire::getInstance()
            ->getBlocksAutoavaliacao($QstnId);
    }
    
    public function getBlocks($QstnId)
    {
        return DbTable_Questionnaire::getInstance()
            ->getBlocks($QstnId);
    }
    
    /**
     * 
     * @param type $QstnId
     * @param type $BlkId
     * @param type $UserId
     * @return type
     */
    public function getRadarData($QstnId, $BlkId, $UserId)
    {
        //query grande
        $arrTabulationDef = DbTable_Questionnaire::getInstance()
            ->getCriterionTabulation($QstnId, $BlkId);
        
        //query grande
        $arrPunctuationDef = DbTable_Questionnaire::getInstance()
            ->getPunctuationByCriterion($QstnId, $BlkId, $UserId);
        
        $arrTabulation = array();
        $arrPunctuation = array();
        $arrRadarData = array();
        
        foreach($arrTabulationDef AS $tabulation) {
            $arrTabulation[$tabulation->getDesignation()] = $tabulation->getSumPontuacao();
        }
        
        foreach($arrPunctuationDef AS $punctuation) {
            $arrPunctuation[$punctuation->getDesignation()] = $punctuation->getNota();
        }
        
        /**
         * faz o calculo
         */
        
        foreach($arrTabulation AS $chave => $valor) {
            if(($valor != "0") && isset($arrPunctuation[$chave])) {
                $arrRadarData[$chave] = round(((int)$arrPunctuation[$chave]/(int)$valor)*100,2);
            } else {
                $arrRadarData[$chave] = 0;
            }
        }
        
        return array($arrRadarData, $arrTabulation, $arrPunctuation);
    }
    
    public function getQuestionsPunctuationByBlock($QstnId, $UserId, $blkId = null)
    {
        if($blkId == null) {
            return DbTable_Questionnaire::getInstance()
            ->getQuestionsPunctuationByBlock($QstnId, $UserId);
        } else {
            return DbTable_Questionnaire::getInstance()
            ->getQuestionsPunctuationByBlock($QstnId, $UserId, $blkId);
        }
    }
    /**
     * 
     * Verifica se todas as questoes foram respondidas
     * Se sim, retorna false, caso contrario true
     * 
     * @param type $QstnId
     * @param type $UserId
     * @return boolean
     */
    public function isFullyAnswered($QstnId,$UserId,$blockId = null)
    {
        if (DbTable_Questionnaire::getInstance()
            ->isFullyAnswered($QstnId, $UserId, $blockId)
        ) {
            return false;
        } 
        return true;
    }

    
    
    /**
     * 
     * Verifica se todas as questoes foram respondidas
     * Se sim, retorna false, caso contrario true
     * 
     * @param int $QstnId
     * @param int $UserId
     * @param int $BlockId
     * @return boolean
     */
    public function isFullyAnsweredByBloco($QstnId,$UserId, $blockId)
    {
        
        if (DbTable_Questionnaire::getInstance()
            ->isFullyAnswered($QstnId, $UserId, $blockId)
        ) {
            return false;
        } 
        return true;
    }    
    
    
    public function verifyQuestionnaireEligibility($questionnaireId,$enterpriseId)
    {
        $this->Eligibility = new Model_Eligibility();
        
        $enterpriseRow = DbTable_Enterprise::getInstance()->fetchRow(array('Id = ?' => $enterpriseId));
        
        switch ($questionnaireId) {
            case $this->Eligibility->diagnosticoId : 
                if (!$enterpriseRow->getDiagnosticoEligibility()) {
                    return false;
                }
                break;
            
            case $this->Eligibility->autoavaliacaoId : 
                if (!$enterpriseRow->getAutoavaliacaoEligibility()) {
                    return false;
                }
                break;
        }
        return true;
    }
    
    public function verifyQuestionnaireRolePeriod($questionnaireId,$roleId)
    {
        $roleQuestionnaireRow = DbTable_RoleQuestionnaire::getInstance()
            ->fetchRow(array('RoleId = ?' => $roleId,'QuestionnaireId = ?' => $questionnaireId));
        if(!$roleQuestionnaireRow) {
            return true;
        } else {
            $dateNow = date('Y-m-d');
            $startDate = $roleQuestionnaireRow->getStartDate();
            $endDate = $roleQuestionnaireRow->getEndDate();
            if ($dateNow >= $startDate && $dateNow <= $endDate) {
                return true;
            } 
            return false;
        }
        return false;
    }
    
    public function verifyQuestionnaireOperation($QstnId,$beginningDate,$endingDate)
    {
        $questionnaireRow = $this->getQuestionnaireById($QstnId);
        $startDate = new DateTime($questionnaireRow->getOperationBeginning());
        $endDate = new DateTime($questionnaireRow->getOperationEnding());
        $beginningDate = new DateTime($beginningDate);
        $endingDate = new DateTime($endingDate);
        
        if (($beginningDate < $startDate) || ($endingDate > $endDate)) {
            return false;
        }
        return true;
    }
    public function setExecutionProgress($QstnId,$UserId) 
    {
        $qtdTotal = DbTable_Questionnaire::getInstance()->getQuestionnaireTotalQuestions($QstnId)->getQtdTotal();
        $qtdFaltante = count(DbTable_Questionnaire::getInstance()->needToFinish($QstnId,$UserId)->toArray());
        $qtdRealizado = ($qtdTotal-$qtdFaltante);
        $pctRealizado = round(($qtdRealizado/$qtdTotal)*100,0);
        
        $executionRow = DbTable_Execution::getInstance()
            ->fetchRow(array('UserId = ?' => $UserId, 'QuestionnaireId = ?' => $QstnId));
        
        if (!$executionRow) {
            $Execution = new Model_Execution();
            $executionRow = $Execution->initExecution($QstnId, $UserId);
        }
        
        
        $executionRow->setProgress($pctRealizado);
        $executionRow->save();
        
        return true;
    }

    public function subscriptionPeriodIsOpenFor($questionnaire,$user){
        if(!$questionnaire) $questionnaire = $this->getCurrentExecution();

        $validRoles = array('digit-dor','gestor');

        if($user and in_array($user->getRole(),$validRoles)){
            $subscriptionEndsAt = $questionnaire->getInternalSubscriptionEndsAt();
        } else {
            $subscriptionEndsAt = $questionnaire->getPublicSubscriptionEndsAt();
        }
        $subscriptionEndsAt = new Zend_Date($subscriptionEndsAt, 'yyyy-MM-dd');

        $now = new Zend_Date();
        return ($now->isEarlier($subscriptionEndsAt));
    }

    public function validateAndRecoverQuestionnaire($questionnaire_id)
    {
        $return = true;

        $objQuestionnaire = $this->getQuestionnaireById($questionnaire_id);

        $this->setQuestionnaireType($objQuestionnaire);

        if (!$questionnaire_id || !$objQuestionnaire) {
            $return = false;
        }

        return $return;
    }

    private function setQuestionnaireType($questionnaire){
        $this->devolutive_id = $questionnaire->getDevolutiveCalcId();
    }


    public function verifyIfAllQuestionsWereAnswered($questionnaire_id, $user_id)
    {
        $return = true;
        $currentBlockIdNegocios = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;

        $answeredQuestions = $this->isFullyAnswered($questionnaire_id, $user_id, $currentBlockIdNegocios);

        if (!$answeredQuestions) {
            $return = false;
        }

        return $return;
    }


    public function validateQuestionnaireBlocks($questionnaire_id, $devolutive)
    {
        $blocks = $this->getBlocksAutoavaliacao($questionnaire_id);

        if (!$blocks) {
            throw new Exception($this->_messagesError['blocksNotExists']);
        }

        $negociosBlockId = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
        $enterpreneurBlockId = Zend_Registry::get('configDb')->qstn->currentBlockIdEmpreendedorismo;

        //seta blocos para uso na classe que processara a devolutiva
        $devolutive->setBlockIdNegocios($negociosBlockId);
        $devolutive->setBlockIdEmpreendedorismo($enterpreneurBlockId);
    }

    public function calculateScoreFromQuestionnaireAnswers($QuestionnaireId, $UserId, $BlockId, $CompetitionId)
    {
        //forma correta
        $db = Zend_Registry::get('db');
        $sql = "CALL p_pontuacao_grade (?, ?, ?, ?)";
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        $params = array($QuestionnaireId, $BlockId, $UserId, $CompetitionId);
        $stmt->execute($params);
        //$stmt->fetch();
        $db->closeConnection();

        return true;
    }
}
