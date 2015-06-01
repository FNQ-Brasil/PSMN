<?php

class Management_AvaliacaoController extends Vtx_Action_Abstract
{
    public function init()
    {
        $this->userAuth = Zend_Auth::getInstance()->getIdentity();
        $this->programId = Zend_Registry::get('configDb')->competitionId;
        $this->Enterprise = new Model_Enterprise();
        $this->Appraiser = new Model_Appraiser(); 
        /* Verificação se o avaliador tem permissao */
        $this->enterpriseKey = $this->_getParam('enterprise-id-key');
        $this->enterpriseRow = $this->Enterprise->getEnterpriseByIdKey($this->enterpriseKey);
        $this->view->etapa = $etapa = $this->_getParam('etapa', 'estadual');
        
        $this->evaluationRow = $this->Appraiser->isPermit(
            $this->enterpriseRow->getId(), $this->userAuth->getUserId(),
            $this->programId, $etapa
        );
        if (!$this->evaluationRow or $this->evaluationRow->getStatus() == 'C') {
            throw new Exception('Não autorizado');
        }
    }

    public function indexAction()
    {
        $questions = $this->Appraiser->getQuestions();

        $View = array(
            'enterprise' => $this->enterpriseRow,
            'president' => $this->enterpriseRow->getPresidentRow(),
            'questoes' => $questions,
            'respostas' => $this->evaluationRow->getAnswers(),
            'conclusao' => $this->evaluationRow->getConclusao(),
        );
        $this->view->assign($View);

        if (!$this->getRequest()->isPost()) {
            return;
        }
        $conclusao = $this->_getParam('conclusao', false);
        $finalizar = $this->_getParam('finalizar', false);

        $save = $this->Appraiser->saveEvaluation(
            $questions, $this->evaluationRow, $this->_getParam('linha1'),
            $this->_getParam('linha2'), $this->_getParam('ans'), $conclusao, $finalizar
        );

        // na caso de finalizacao da avaliacao, porém com campos notas faltando
        if ($finalizar and !$save['finalizacaoSucesso'] and $save['status']) {
            $View['respostas'] = $this->evaluationRow->getAnswers();
            $View['finalizacaoErro'] = true;
            $View['questionsError'] = $save['questionsError'];
            $this->view->assign($View);
            return;
        }
        //finalizacao da avaliação faltando conclusão
        if ($finalizar and !$conclusao) {
            $View['respostas'] = $this->evaluationRow->getAnswers();
            $View['conclusaoErro'] = true;
            $View['questionsError'] = $save['questionsError'];
            $this->view->assign($View);
            return;
        }

        if ($save['status']) {
            $this->_redirect(
                'management/appraiser/index/enterprise-id-key-sucess/'
                . $this->enterpriseKey 
            );
        }
    }

    public function reportAction()
    {
        $modelReport = new Model_EnterpriseReport;
        $View = array(
            'report' => $modelReport->getEnterpriseReportByEnterpriseIdKey($this->enterpriseKey),
            'enterprise' => $this->enterpriseRow,
            'president' => $this->enterpriseRow->getPresidentRow()
        );
        $this->view->assign($View);
    }
}