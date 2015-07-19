<?php

class Questionnaire_RespondController extends Vtx_Action_Abstract
{
    
    protected $_messagesError = array(
        'answerValue' => 'Campo resposta escrita deve ser preenchido. ',
        'alternativeError' => 'Alternativa escolhida não pertence a questão corrente.',
        'questionNotExists' => 'A questão informada não existe.'
    );
    
    protected $_messagesSuccess = array(
        'answerOk' => 'Questão respondida com sucesso. ',
        'answerExists' => 'Questão respondida anteriormente; nenhuma alteração detectada. ',
        'answerValue' => 'Resposta escrita excluída devida alternativa escolhida. ',
    );
    /**
     *
     * @var Model_Block
     */
    protected $Block;
    protected $ExecutionPontuacaoManager;
    protected $Enterprise;
    protected $EnterpriseReport;

    public function init()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }        
        
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('question', array('json'))
             ->addActionContext('answer', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();

        $this->Block = new Model_Block();
        $this->Question = new Model_Question();
        $this->Questionnaire = new Model_Questionnaire();
        $this->Answer = new Model_Answer();
        $this->Alternative = new Model_Alternative();
        $this->AnswerHistory = new Model_AnswerHistory(); // grava respostas do aval, gestor, admin
        $this->QuestionTip = new Model_QuestionTip();
        $this->Enterprise = new Model_Enterprise();
        $this->EnterpriseReport = new Model_EnterpriseReport();
        
        $this->modelUserLocality = new Model_UserLocality();
        $this->modelECAC = new Model_EnterpriseCategoryAwardCompetition();

        $this->ExecutionPontuacaoManager = new Manager_ExecutionPontuacao();

        $this->Acl = Zend_Registry::get('acl');
        $this->userLogged = Zend_Auth::getInstance()->getIdentity();
        $this->loggedUserId = $this->userLogged->getUserId();

        //Privilégio 'respond-not-entreprise' tem permissão para alterar/responder para todas coops.
        //Privilégio 'respond-is-entreprise' é a propria coop. logada empresa
        //Privilégio 'evaluation-of-response' tem permissão para inserir AnswerFeedback (avaliação)
        $this->permissionNotEnterprise = ($this->Acl->isAllowed(
            $this->userLogged->getRole(), 'management:questionnaire', 'not-coop-responding'
            ) or $this->Acl->isAllowed($this->userLogged->getRole(), 'management:questionnaire', 'acompanhqstn')
        ); 
        
        $this->permissionIsEnterprise = $this->Acl->isAllowed(
            $this->userLogged->getRole(), 'questionnaire:respond', 'coop-responding'
        );
        $this->permissionEvaluationOfResponse = $this->Acl->isAllowed(
            $this->userLogged->getRole(), 'management:questionnaire', 'evaluation-of-response'
        );

        if ($this->permissionNotEnterprise) {
            $ns = new Zend_Session_Namespace('respond');
            
            $this->enterpriseIdKey = $this->_getParam('enterprise-id-key',null);

            $this->enterpriseUserId = ($this->enterpriseIdKey)?
                    $this->Enterprise->getUserIdByIdKey($this->enterpriseIdKey):null;
            
            if ($this->enterpriseUserId) {
                $ns->enterpriseUserId = $this->enterpriseUserId;
            } else {
                $this->enterpriseUserId = $ns->enterpriseUserId;
            }

            /* @TODO Mudar para post/ elegante */
            /* @TODO testar se coop existe */
        } elseif ($this->permissionIsEnterprise) {
            $this->enterpriseUserId = $this->loggedUserId;
            $this->enterpriseIdKey = $this->Enterprise->getIdKeyByUserId($this->loggedUserId);
        } else {
            throw new Exception('Privilégio inválido');
        }

        if (!$this->enterpriseUserId) {
            $this->_redirect('/management/enterprise/?coop');
        }
        $this->competitionId = Zend_Registry::get('configDb')->competitionId;
    }
    
    public function acompanhqstnAction()
    {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $this->indexAction();
        $this->_helper->viewRenderer->setRender('index');
    }

    public function indexAction()
    {
        
        //Definicao de que CompetitionId é o mesmo que programaId para PSMN
        //19/06/2013: Everton, thiago, Marco
        
        $programaId = $this->competitionId;
        
        $enterpriseId = $this->modelUserLocality->getUserLocalityByUserId($this->enterpriseUserId)->getEnterpriseId();
        $hasECAC = $this->modelECAC->hasECAC($enterpriseId,$this->competitionId);
        
        if (!$hasECAC) { 
            throw new Exception('access denied');
            return;
        }

        $this->view->subscriptionPeriodIsClosed = !$this->subscriptionPeriodIsOpen();
        $this->view->currentBlockIdNegocios = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
        $this->view->currentBlockIdEmpreendedorismo = Zend_Registry::get('configDb')->qstn->currentBlockIdEmpreendedorismo;
        $blockId = $this->_getParam('block', $this->view->currentBlockIdNegocios);

        
        /*
            Caso tente respnder um questionário, que não seja o atual.
         */
        if ($blockId != $this->view->currentBlockIdNegocios) { 
            throw new Exception('access denied');
            return;
        }
        
        $this->view->qstnCurrent = $this->Questionnaire->getCurrentExecution();
        if (!$this->view->qstnCurrent) {
            throw new Exception('Nenhum questionário ativo.');
        }
        
        /* @TODO verificar se o bloco passado pertence ao questionário corrente que tem q ser pego por config */
        
        //$this->view->blockQuestions = $this->Block->getQuestionsByBlockIdForView($blockId);
        
        //recupera do CACHE ou MODEL
        $this->view->blockQuestions = $this->Block->cacheOrModelBlockById($blockId);
        

        //var_dump('blockQuestions: ',$this->view->blockQuestions);
        //echo "<br><br>";
        
        
        
        $this->view->blockCurrent = $this->Block->getDbTable()->find($blockId)->current();
        
        $this->view->qstnRespondId = $this->view->qstnCurrent->getId();
        $this->view->papelEmpresa = ($this->userLogged->getRoleId() == Zend_Registry::get('config')->acl->roleEnterpriseId)?'true':'false';
        $this->view->user_id = $this->enterpriseUserId;
        $this->view->enterpriseIdKey = $this->enterpriseIdKey;
        
        //$enterpriseId = $this->Enterprise->getEnterpriseByUserId($this->enterpriseUserId)->getId();
        
        
        if (!$this->Questionnaire->verifyQuestionnaireEligibility($this->view->qstnRespondId, $enterpriseId)) {
            
            $this->view->messageError = "Você não possui elegibilidade para o questionário escolhido.";
            return;
        }
        
        /* Caso geração de devolitiva, redireciona */
        if ($this->_getParam('geraDevolutiva')) {
            
            if ($this->_getParam('menu-admin')) {
                $this->view->isViewAdmin = true;
                $this->_helper->_layout->setLayout('new-qstn');
            }
            //regerar devolutiva
            $regerar = $this->_getParam('regerar');
            if ($regerar) {
                //exclui o link da ultima devolutiva gerada
                $modelExec = new Model_Execution();
                $execution = $modelExec->getExecutionByUserAndPrograma($this->enterpriseUserId, $programaId);
                $execution->setDevolutivePath(null);
                $execution->save();
            }            
            
            
            $this->view->questionnaireId = $this->view->qstnRespondId;
            $this->view->enterpriseUserId = $this->enterpriseUserId;
            $this->_forward('index', 'devolutive', 'questionnaire');
            return;
        }
        
        
        if (!$this->Questionnaire->verifyQuestionnaireRolePeriod($this->view->qstnRespondId,$this->userLogged->getRoleId())) {
            $this->view->messageError = "Você não possui permissão de acesso para o questionário escolhido.";
            return;
        }
        
        $this->view->answeredByUserId = $this->Questionnaire->getQuestionsAnsweredByUserId(
            $this->view->qstnRespondId, $this->enterpriseUserId, $blockId
        );
        
        
        $this->view->periodoRespostas = true;
        if (!$this->Questionnaire->isQuestionnaireExecution($this->view->qstnRespondId)) {
            $this->view->periodoRespostas = false;
            $this->view->messageError = "Período de resposta do questionário inválido.";
            return;
        }
 
        $UserLocality = new Model_UserLocality();
        $this->view->enterpriseRow = $UserLocality->getUserLocalityByUserId($this->enterpriseUserId)
            ->findParentEnterprise();
        $this->view->enterpriseIdGetParam = ($this->permissionNotEnterprise)?
            $this->enterpriseUserId : null;
        $this->view->permissionEvaluationOfResponse = $this->permissionEvaluationOfResponse;
    }

    public function answerAction()
    {
        if(!$this->subscriptionPeriodIsOpen()) return;

        $this->view->papelEmpresa = ($this->userLogged->getRoleId() == Zend_Registry::get('config')->acl->roleEnterpriseId)?'true':'false';
        $this->view->user_id = $this->enterpriseUserId;
        $this->view->respondQuestionOk = false;
        $this->view->itemSuccess = false;
        $this->view->respondRowData = $dataPosted = $this->_getAllParams();

        //Não respondeu nada.
        if (!isset($this->view->respondRowData['alternative_id']) 
            or $this->view->respondRowData['alternative_id'] == ''
        ) {
            $this->view->itemSuccess = true;
            return;
        }
        
        $respondQuestionId = $this->_getParam('question_id', '');
        $respondQuestionRow = $this->Question->getQuestionById($respondQuestionId);
        if (!$respondQuestionId or !$respondQuestionRow) {
            throw new Exception('Questão inválida, não encontrada.');
        }
        
        $block = $respondQuestionRow->findParentCriterion()->findParentBlock();
        $questionnaire = $block->findParentQuestionnaire();
        $qstnId = $questionnaire->getId();
        $competitionId = $questionnaire->getCompetitionId();

        if (!$this->Questionnaire->isQuestionnaireExecution($qstnId)) {
            throw new Exception('Período de resposta do questionário inválido.');
        }
        
        $isAnswered = $this->Question->isAnsweredByEnterprise($respondQuestionId,  $this->enterpriseUserId);

        $respondRowData = $this->view->respondRowData;

        // resposta escrita
        $respondRowData['answer_value'] = isset($respondRowData['answer_value'])?
            trim($respondRowData['answer_value']) : '';
        
        $respondRowData = $this->Answer->filterAnswerForm($respondRowData)->getUnescaped();
        $respondRowData['aaresult_value'] = ''; // resposta com resultado anual

        //Verificação de segurança se é uma alternativa válida da questão
        $alternativeRow = $this->Alternative->isQuestionAlternative(
            $respondRowData['alternative_id'], $respondQuestionId
        );
        if (!$alternativeRow) {
            throw new Exception($this->_messagesError['alternativeError']);
        }

        /*
        if ($respondRowData['answer_value'] == '') {
            $this->view->itemSuccess = false;
            $this->view->messageError = $this->_messagesError['answerValue'];
            return;
        }
         */
        $this->view->respondRowData['answer_value'] = "";
        
        $setExecutionProgress = false;

        $respondRowData['answer_date'] = date('Y-m-d');
        $respondRowData['end_time'] = date('H:i:s');
        $respondRowData['user_id'] =  $this->enterpriseUserId;
        $respondRowData['logged_user_id'] = $this->loggedUserId;
        $respondRowData['qstn_id'] = $qstnId;

        if ($isAnswered['status']) {
            $answerId = $isAnswered['objAnswered']->getAnswerId();
            
            if ($this->Answer->hasChange($answerId, $respondRowData, $alternativeRow)) {
                $answer = $this->Answer->updateAnswer($answerId, $respondRowData, $alternativeRow);
                $setExecutionProgress = true;
            } else {
                $answer['status'] = true;
                $answer['row'] = $isAnswered['objAnswered'];
            }
        } else {
            $answer = $this->Answer->createAnswer($respondRowData, $alternativeRow);
            $answerId = $answer['row']->getId();
            $setExecutionProgress = true;
        }

        if (!$answer['status']) {
            $this->view->itemSuccess = false;
            $this->view->messageError = $answer['messageError'];
            return;
        }
        
        if ($setExecutionProgress) {
            $this->Questionnaire->setExecutionProgress($qstnId, $this->enterpriseUserId);
        }

        //Privilégio avaliação de resposta: Pontos Fortes e Pontos a melhorar
        $this->verificaRotinasFeedback($answerId, $dataPosted);

        $this->checkForDevolutiveUpdate($competitionId, $qstnId, $block->getId());

        $this->view->respondQuestionOk = true;
        $this->view->respondRowData = array();
        $this->view->itemSuccess = true;
        
    }

    /**
     * funcao que faz verificacao de dados do form Questionario, para os campos:
     * - Pontos Fortes
     * - Pontos a melhorar
     * 
     */
    public function verificaRotinasFeedback($answerId, $dataPosted)
    {
        $dataPosted['evaluation'] = 'não tem este campo na view do admin';
        $dataPosted['evaluationImprove'] = 'não tem este campo na view do admin';
        //Privilégio avaliação de resposta
        if ($this->permissionEvaluationOfResponse) {
            //tabela AnswerFeedback
            $AnswerFeedback = new Model_AnswerFeedback();
            $AnswerFeedback->createAnswerFeedback(array(
               'user_id' => $this->loggedUserId,
               'answer_id' => $answerId,
               'feedback' => $dataPosted['evaluation']
            ));
            //tabela AnswerFeedbackImprove
            $AnswerFeedbackImprove = new Model_AnswerFeedbackImprove();
            $AnswerFeedbackImprove->createAnswerFeedbackImprove(array(
               'user_id' => $this->loggedUserId,
               'answer_id' => $answerId,
               'feedback_improve' => $dataPosted['evaluationImprove']
            ));            
        }        
    } //end function

    public function subscriptionPeriodIsOpen(){
        $isOpen = true;

        if(!$this->Questionnaire->subscriptionPeriodIsOpenFor(null, $this->userLogged)){
            $this->view->itemSuccess = false;
            $this->view->messageError = 'Não é possível responder ao questionário: as inscrições foram encerradas.';
            $isOpen = false;
        }

        return $isOpen;
    }

    private function checkForDevolutiveUpdate($competitionId, $questionnaireId, $blockId){
        $QuestionnaireTable = $this->Questionnaire->tbQuestionnaire;
        $questionsCount = $QuestionnaireTable->getQuestionnaireTotalQuestions($questionnaireId)->getQtdTotal();

        $answeredQuestionsCount = count(
            $this->Questionnaire->getQuestionsAnsweredByUserId($questionnaireId, $this->enterpriseUserId, $blockId)
        );

        $enterpriseReport = $this->EnterpriseReport->getCurrentEnterpriseReportByEnterpriseIdKey(
            $this->enterpriseIdKey, $competitionId
        );

        $this->view->updateDevolutive = ($questionsCount == $answeredQuestionsCount && $enterpriseReport);
    }
    
} //end class controller