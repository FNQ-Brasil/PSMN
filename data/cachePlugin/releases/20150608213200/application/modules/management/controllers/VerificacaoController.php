<?php

class Management_VerificacaoController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->programId = Zend_Registry::get('configDb')->competitionId;
        $this->Enterprise = new Model_Enterprise;
        $this->Appraiser = new Model_Appraiser; 
        /* Verificação se o verificador tem permissao */
        $this->enterpriseKey = $this->_getParam('enterprise-id-key');
        $this->enterpriseRow = $this->Enterprise->getEnterpriseByIdKey($this->enterpriseKey);
        $this->evaluationRow = $this->Appraiser->isCheckerPermit(
            $this->enterpriseRow->getId(), $this->userAuth->getUserId(), $this->programId
        );
        if (!$this->evaluationRow or $this->evaluationRow->getStatus() == 'C') {
            throw new Exception('Não autorizado');
        }
    }

    public function indexAction()
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
        );

        $this->view->assign($V);

        if (!$this->getRequest()->isPost()) {
            return;
        }
        $conclusao = $this->_getParam('conclusao', false);
        $finalizar = $this->_getParam('finalizar', false);

        $save = $this->Appraiser->saveCheckerEvaluation(
            $commentQuestions, $evaluationQuestions, $this->evaluationRow,
            $this->_getParam('comments'), $this->_getParam('ansAvaliacao'), $conclusao, $finalizar
        );

        // no caso de finalizacao da avaliacao, porém com campos notas faltando
        if ($finalizar and !$save['finalizacaoSucesso']) {
            $V['commentAnswers'] = $this->evaluationRow->getCommentAnswers();
            $V['respostas'] = $this->evaluationRow->getAnswers();
            $V['finalizacaoErro'] = true;
            $V['criteriosError'] = $save['criteriosError'];
            $V['evaluationQuestionsError'] = $save['evaluationQuestionsError'];
            $this->view->assign($V);
            return;
        }
        //finalizacao da avaliação faltando conclusão final
        if ($finalizar and !$conclusao) {
            $V['respostas'] = $this->evaluationRow->getAnswers();
            $V['conclusaoErro'] = true;
            $V['questionsError'] = isset($save['questionsError'])? $save['questionsError'] : array();
            $this->view->assign($V);
            return;
        }

        $this->_redirect(
            'management/appraiser/checker/' . $this->enterpriseKey
        );

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