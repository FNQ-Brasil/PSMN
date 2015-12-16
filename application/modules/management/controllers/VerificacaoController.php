<?php

class Management_VerificacaoController extends Vtx_Action_Abstract
{
    protected $Block;
 
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
   
 public function init()
    {
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        
        $this->_helper->getHelper('contextSwitch')
             ->addActionContext('question', array('json'))
             ->addActionContext('answer', array('json'))
             ->setAutoJsonSerialization(true)
             ->initContext();

        $this->programId = Zend_Registry::get('configDb')->competitionId;
        $this->Enterprise = new Model_Enterprise;
        $this->EnterpriseReport = new Model_EnterpriseReport();
        $this->Appraiser = new Model_Appraiser; 
        
        $this->Block = new Model_Block();
        $this->Questionnaire = new Model_Questionnaire();
        $this->Question = new Model_Question();
        //$this->Answer = new Model_Answer();
        $this->AnswerVerificador = new Model_AnswerVerificador();
        $this->Alternative = new Model_Alternative();
	$this->modelUserLocality = new Model_UserLocality();
	$this->modelECAC = new Model_EnterpriseCategoryAwardCompetition();

        /* Verificação se o verificador tem permissao */
      
        $this->Acl = Zend_Registry::get('acl');
        $this->userLogged = Zend_Auth::getInstance()->getIdentity();
        $this->loggedUserId = $this->userLogged->getUserId();
        $this->enterpriseKey = $this->_getParam('enterprise-id-key');
        $this->enterpriseRow = $this->Enterprise->getEnterpriseByIdKey($this->enterpriseKey);
        $this->evaluationRow = $this->Appraiser->isCheckerPermit(
        $this->enterpriseRow->getId(), 
        $this->userAuth->getUserId(), 
        $this->programId
        );
        if (!$this->evaluationRow or $this->evaluationRow->getStatus() == 'C') {
            throw new Exception('Não autorizado');
        }                
      
        $this->enterpriseIdKey = $this->_getParam('enterprise-id-key',null);
        $this->enterpriseUserId = ($this->enterpriseIdKey)?
        $this->Enterprise->getUserIdByIdKey($this->enterpriseIdKey):null;
	$this->competitionId = Zend_Registry::get('configDb')->competitionId;
}

 public function indexAction()
    {
	$this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $commentQuestions = $this->Appraiser->getQuestions();
        $evaluationQuestions = DbTable_QuestionChecker::getInstance()->fetchAll('QuestionTypeId = 7', 'Designation');
        $questions = $this->Appraiser->getQuestions();
		
        $V = array(
            'enterprise' => $this->enterpriseRow,
            'president' => $this->enterpriseRow->getPresidentRow(),
            'questoes' => $commentQuestions,
            'questionsAvaliacao' => $evaluationQuestions,
            'respostas' => $this->evaluationRow->getAnswers(),
            'commentAnswers' => $this->evaluationRow->getCommentAnswers(),
            'conclusao' => $this->evaluationRow->getConclusao(),
            'scores' => $this->Appraiser->getEnterpriseScoreAppraisersData($this->enterpriseRow->getId()),
            'verificacaoAvaliador' => $this->Appraiser->getEnterpriseScoreAppraiserAnwserVerificadorData($this->enterpriseRow->getId(),$this->userAuth->getUserId()),
            'conclusao' => $this->evaluationRow->getConclusao(),
            'comentarioVerificador' => $this->Appraiser->getApeEvaluationVerificadorComment($this->enterpriseRow->getId(),$this->userAuth->getUserId())
        );

        $this->view->assign($V);
        $this->loggedUserId = $this->userLogged->getUserId();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $conclusao = $this->_getParam('conclusao', false);

		$data = array();
		$data['enterprise_id']= $this->enterpriseRow->getId();
        $data['appraiser_id'] = $this->userAuth->getUserId();
        $data['tipo']= '9';
        $data['programa_id'] = Zend_Registry::get('configDb')->competitionId;
        $data['etapa'] = $this->_getParam('etapa', 'estadual');

	
		$AppraiserEnterprise = new Model_Appraiser();
        $AppraiserEnterprise->setAppraiserToEnterprise($data);       
		$this->evaluationRow->setId($AppraiserEnterprise->getAppraiserId());
	

        $save = $this->Appraiser->saveApeEvaluationVerificador(
                $questions,
                $this->evaluationRow, 
                $this->_getParam('ans'), 
                $conclusao
        );      
		
		$appraiserEnterpriseId = $this->enterpriseRow->getId();
		$userId =  $this->loggedUserId;

	$save = $this->Appraiser->saveApeEvaluationVerificadorComment(
            $commentQuestions, 
            $evaluationQuestions, 
			$this->_getParam('comments'),
			$appraiserEnterpriseId,
			$userId 
        );
		
		
		$data['enterprise_id'] = $this->enterpriseRow->getId();
        $data['user_id'] = $this->loggedUserId;
        $data['programa_id'] = $this->competitionId;
        $data['tipo'] = 1;
		$data['status'] = "I";
 
		$AppraiserModel = new Model_Appraiser();
		$objAppraiser = $AppraiserModel->setCheckerToEnterpriseVerificador($data);
		
		if ($save['status']) {       
			$this->_redirect(
			'management/appraiser/checker/' . $this->enterpriseKey
			);
		} 

        //finalizacao da avaliação faltando conclusão final
        if (!$conclusao) {
            $V['commentAnswers'] = $this->evaluationRow->getCommentAnswers();
            $V['resposta'] = $this->evaluationRow->getAnswers();
            //$V['conclusaoErro'] = true;
           // $V['questionsError'] = isset($save['questionsError'])? $save['questionsError'] : array();

            $this->view->assign($V);
            return;
		}	
   }
    
    public function criterioavaliacaoAction()
    {
        $commentQuestions = $this->Appraiser->getQuestions();
        $evaluationQuestions = DbTable_QuestionChecker::getInstance()->fetchAll('QuestionTypeId = 7', 'Designation');
        
        $V = array(
            'enterprise' => $this->enterpriseRow,
            'president' => $this->enterpriseRow->getPresidentRow(),
            'questoes' => $commentQuestions,
            'questionsAvaliacao' => $evaluationQuestions,
            'respostas' => $this->evaluationRow->getAnswers(),
            'commentAnswers' => $this->evaluationRow->getCommentAnswers(),
            'conclusao' => $this->evaluationRow->getConclusao(),
            'scores' => $this->Appraiser->getEnterpriseScoreAppraisersData($this->enterpriseRow->getId()),
            'verificacaoAvaliador' => $this->Appraiser->getEnterpriseScoreAppraiserAnwserAvaliatorData($this->enterpriseRow->getId()),
            'checkerEvaluation' => $this->Appraiser->getCheckerEvaluations($this->enterpriseRow->getId())
        );

        $this->view->assign($V);

        if (!$this->getRequest()->isPost()) {
            return;
        }
        $conclusao = $this->_getParam('conclusao', false);
        $finalizar = $this->_getParam('finalizar', false);

        $save = $this->Appraiser->saveCheckerEvaluation(
            $commentQuestions, $evaluationQuestions, $this->evaluationRow,
            $this->_getParam('comments'),
            $this->_getParam('ansAvaliacao'), 
            $conclusao, 
            $finalizar
        );     
		
		//print_r($this->_getParam('ansAvaliacao'));exit;
		
		$data['enterprise_id'] = $this->enterpriseRow->getId();
        $data['user_id'] = $this->loggedUserId;
        $data['programa_id'] = $this->competitionId;
        $data['tipo'] = 1;
		$data['status'] = "III";
 
		$AppraiserModel = new Model_Appraiser();
		$objAppraiser = $AppraiserModel->setCheckerToEnterpriseVerificador($data);
		
if ($save['status']) {       
			$this->_redirect(
			'management/appraiser/checker/' . $this->enterpriseKey
			);
		}  
    }
      
/* public function subscriptionPeriodIsOpen(){
          $isOpen = true;
      
          if(!$this->Questionnaire->subscriptionPeriodIsOpenFor(null, $this->userLogged)){
              $this->view->itemSuccess = false;
              $this->view->messageError = 'Não é possível responder ao questionário: as inscrições foram encerradas.';
              $isOpen = false;
          }
      
          return $isOpen;
      }      
  */    

     public function answerAction()
     {
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
        
		
	    $data['enterprise_id'] = $this->enterpriseRow->getId();
        $data['user_id'] = $this->loggedUserId;
        $data['programa_id'] = $this->competitionId;
        $data['tipo'] = 1;
		$data['status'] = "I";
 
		if($this->view->respondRowData["question_id"] == 530){
			$AppraiserModel = new Model_Appraiser();
			$objAppraiser = $AppraiserModel->setCheckerToEnterpriseVerificador($data);
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
        
        $isAnswered = $this->Question->isAnsweredByVerificador($respondQuestionId,  $this->loggedUserId,$this->enterpriseUserId);
		
		//print_r($isAnswered );exit;

        $respondRowData = $this->view->respondRowData;

        // resposta escrita
        $respondRowData['answer_value'] = isset($respondRowData['answer_value'])?
            trim($respondRowData['answer_value']) : '';
        
        $respondRowData = $this->AnswerVerificador->filterAnswerForm($respondRowData)->getUnescaped();
        $respondRowData['aaresult_value'] = ''; // resposta com resultado anual

        //Verificação de segurança se é uma alternativa válida da questão
        $alternativeRow = $this->Alternative->isQuestionAlternative(
            $respondRowData['alternative_id'], $respondQuestionId
        );
        if (!$alternativeRow) {
            throw new Exception($this->_messagesError['alternativeError']);
        }

        $this->view->respondRowData['answer_value'] = "";
        
        $setExecutionProgress = false;

        $respondRowData['answer_date'] = date('Y-m-d');
        $respondRowData['end_time'] = date('H:i:s');
        $respondRowData['user_id'] =  $this->userLogged->getUserId();
        $respondRowData['logged_user_id'] = $this->loggedUserId;
        $respondRowData['qstn_id'] = $qstnId;
		$respondRowData['enterprise_id'] = $this->enterpriseUserId;
		
		//print_r($respondRowData);exit;

        if ($isAnswered['status']) {
            $answerId = $isAnswered['objAnswered']->getAnswerId();
            
 
            if ($this->AnswerVerificador->hasChange($answerId, $respondRowData, $alternativeRow)) {
 
                $answer = $this->AnswerVerificador->updateAnswer($answerId, $respondRowData, $alternativeRow);
                $setExecutionProgress = true;
            } else {
                $answer['status'] = true;
                $answer['row'] = $isAnswered['objAnswered'];
            }
        } else {
 
            $answer = $this->AnswerVerificador->createAnswer($respondRowData, $alternativeRow);
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
      
     public function questionarionegocioAction()
     {

        $programaId = $this->competitionId;
	    $enterpriseId = $this->modelUserLocality->getUserLocalityByUserId($this->enterpriseUserId)->getEnterpriseId();
		
		
		
        $hasECAC = $this->modelECAC->hasECAC($enterpriseId,$this->competitionId);

        if (!$hasECAC) { 
            throw new Exception('access denied');
            return;
        }

        //$this->view->subscriptionPeriodIsClosed = !$this->subscriptionPeriodIsOpen();
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
        
       
        //recupera do CACHE ou MODEL
        $this->view->blockQuestions = $this->Block->cacheOrModelBlockById($blockId);
        $this->view->blockCurrent = $this->Block->getDbTable()->find($blockId)->current();
        $this->view->qstnRespondId = $this->view->qstnCurrent->getId();
        $this->view->papelEmpresa = ($this->userLogged->getRoleId() == Zend_Registry::get('config')->acl->roleEnterpriseId)?'true':'false';
        $this->view->user_id = $this->enterpriseUserId;
        $this->view->user_verificador_id = $this->userLogged->getUserId();
        $this->view->enterpriseIdKey = $this->enterpriseIdKey;
        
 	
        $this->view->answeredByUserId = $this->Questionnaire->getQuestionsAnsweredByUserId(
            $this->view->qstnRespondId, $this->enterpriseUserId, $blockId);

        $this->view->answeredByUserIdVerificador = $this->Questionnaire->getQuestionsAnsweredByUserIdVerificador(
            $this->view->qstnRespondId, $this->userLogged->getUserId(),$this->enterpriseUserId, $blockId);
        
        $this->view->periodoRespostas = true;
        if (!$this->Questionnaire->isQuestionnaireExecution($this->view->qstnRespondId)) {
            $this->view->periodoRespostas = false;
            $this->view->messageError = "Período de resposta do questionário inválido.";
            return;
        }

        $UserLocality = new Model_UserLocality();
        $this->view->enterpriseRow = $UserLocality->getUserLocalityByUserId($this->enterpriseUserId)
            ->findParentEnterprise();
     }
     private function checkForDevolutiveUpdate($competitionId, $questionnaireId, $blockId)
     {
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
      
     public function reportAction()
    {
        $modelReport = new Model_EnterpriseReport;
        $V = array(
            'report' => $modelReport->getEnterpriseReportByEnterpriseIdKey($this->enterpriseKey),
            'enterprise' => $this->enterpriseRow,
            'president' => $this->enterpriseRow->getPresidentRow(),
            'scores' => $this->Appraiser->getEnterpriseScoreAppraisersData($this->enterpriseRow->getId())
        );
	
        $this->view->assign($V);
		
    }
}
