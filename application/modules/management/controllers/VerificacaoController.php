<?php

class Management_VerificacaoController extends Vtx_Action_Abstract
{
    protected $Block;
    
    public function init()
    {
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->programId = Zend_Registry::get('configDb')->competitionId;
        $this->Enterprise = new Model_Enterprise;
        $this->Appraiser = new Model_Appraiser; 
        
        $this->Block = new Model_Block();
        $this->Questionnaire = new Model_Questionnaire();
        $this->Question = new Model_Question();
        

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
        
      }

    public function indexAction()
    {
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
            'verificacaoAvaliador' => $this->Appraiser->getEnterpriseScoreAppraiserAnwserAvaliatorData($this->enterpriseRow->getId()),
            'verificacaoVerificadorRelato' => $this->Appraiser->getEnterpriseScoreAppraiserAnwserVerificadorData($this->enterpriseRow->getId()),
            'conclusao' => $this->evaluationRow->getConclusao(),
        );

        $this->view->assign($V);

        if (!$this->getRequest()->isPost()) {
            return;
        }
        $conclusao = $this->_getParam('conclusao', false);
        $finalizar = $this->_getParam('finalizar', false);
        
        $save = $this->Appraiser->saveCheckerEvaluation(
            $commentQuestions, 
            $evaluationQuestions, 
            $this->evaluationRow,
            $this->_getParam('comments'),
            $this->_getParam('respostas'),
            $this->_getParam('ansAvaliacao')//, 
            //$conclusao, 
            //$finalizar            
        );        
        
        $save = $this->Appraiser->saveApeEvaluationVerificador(
                $questions,
                //$this->_getParam('answers'),
                $this->evaluationRow, 
                $this->_getParam('ans'), 
                $conclusao, 
                $finalizar
                );
        
        // no caso de finalizacao da avaliacao, porém com campos notas faltando
        if ($finalizar and !$save['finalizacaoSucesso'] and $save['status']) {
            $V['commentAnswers'] = $this->evaluationRow->getCommentAnswers();
            $V['respostas'] = $this->evaluationRow->getAnswers();
            $V['finalizacaoErro'] = true;
            $V['questionsError'] = $save['questionsError'];
//            $V['criteriosError'] = $save['criteriosError'];
  //          $V['evaluationQuestionsError'] = $save['evaluationQuestionsError'];
            $this->view->assign($V);
            return;
        }
        //finalizacao da avaliação faltando conclusão final
        if ($finalizar and !$conclusao) {
            $V['commentAnswers'] = $this->evaluationRow->getCommentAnswers();
            $V['respostas'] = $this->evaluationRow->getAnswers();
            $V['conclusaoErro'] = true;
            $V['questionsError'] = isset($save['questionsError'])? $save['questionsError'] : array();
            $this->view->assign($V);
            return;
        }

       //if ($save['status']) {       
        //$this->_redirect(
          //  'management/appraiser/checker/' . $this->enterpriseKey
        //);
       //}
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
            //$this->_getParam('respostas'),
            $this->_getParam('ansAvaliacao'), 
            $conclusao, 
            $finalizar
        );         
      }
      
    public function subscriptionPeriodIsOpen(){
          $isOpen = true;
      
          if(!$this->Questionnaire->subscriptionPeriodIsOpenFor(null, $this->userLogged)){
              $this->view->itemSuccess = false;
              $this->view->messageError = 'Não é possível responder ao questionário: as inscrições foram encerradas.';
              $isOpen = false;
          }
      
          return $isOpen;
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
      
              if ($this->Answer->hasChange($answerId, $respondRowData/ $alternativeRow)) {
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
      
      public function questionarionegocioAction()
      {
          $commentQuestions = $this->Appraiser->getQuestions();          
          $evaluationQuestions = DbTable_QuestionChecker::getInstance()->fetchAll('QuestionTypeId = 5', 'Designation');
      
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
      
       $this->view->currentBlockIdNegocios = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
       $blockId = $this->_getParam('block', $this->view->currentBlockIdNegocios);
       
       if ($blockId != $this->view->currentBlockIdNegocios) {
           throw new Exception('access denied');
           return;
       }
       
       $this->view->qstnCurrent = $this->Questionnaire->getCurrentExecution();
       
       if (!$this->view->qstnCurrent) {
           throw new Exception('Nenhum questionário ativo.');
       }
              
       $this->enterpriseIdKey = $this->_getParam('enterprise-id-key',null);
       $this->view->qstnRespondId = $this->view->qstnCurrent->getId();       
       $this->enterpriseUserId = ($this->enterpriseIdKey)?
       $this->Enterprise->getUserIdByIdKey($this->enterpriseIdKey):null;
       
       if ($this->enterpriseUserId) {
           $ns->enterpriseUserId = $this->enterpriseUserId;
       } else {
           $this->enterpriseUserId = $ns->enterpriseUserId;
       }
       
       //print_r($this->view->answeredByUserId);
       
       //print_r($this->view->qstnRespondId);
       //print_r($this->enterpriseUserId );
       //print_r($blockId);
        
       
       $this->view->answeredByUserId = $this->Questionnaire->getQuestionsAnsweredByUserId(
       $this->view->qstnRespondId, 
       $this->enterpriseUserId, 
       $blockId
      );
       
     // print_r($this->Questionnaire);
       
       $this->view->user_id = $this->enterpriseUserId;
       $this->userLogged = Zend_Auth::getInstance()->getIdentity();
       $this->loggedUserId = $this->userLogged->getUserId();
       
      $this->view->papelEmpresa = ($this->userLogged->getRoleId() == Zend_Registry::get('config')->acl->roleEnterpriseId)?'true':'false';       
      
        //recupera do CACHE ou MODEL
        $this->view->blockQuestions = $this->Block->cacheOrModelBlockById($blockId);
        $this->view->blockCurrent = $this->Block->getDbTable()->find($blockId)->current();
        $this->view->assign($V);
      
          if (!$this->getRequest()->isPost()) {
              return;
          }
          $conclusao = $this->_getParam('conclusao', false);
          $finalizar = $this->_getParam('finalizar', false);
      
         // $save = $this->Appraiser->saveCheckerEvaluation(
           //   $commentQuestions,
            //  $evaluationQuestions, 
             // $this->evaluationRow,
              //$this->_getParam('comments'),
              //$this->_getParam('respostas'),
              //$this->_getParam('ansAvaliacao'), 
              //$conclusao, 
              //$finalizar             
          //);
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
                  $execution = $modelExec->getExecutionByUserAndPrograma($this->enterpriseUserId, '2015');
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
    }
      