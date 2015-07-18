<?php

class Management_QuestionnaireController extends Vtx_Action_Abstract
{

    public function init()
    {
        $this->Block         = new Model_Block();
        $this->Devolutive    = new Model_Devolutive();
        $this->Questionnaire = new Model_Questionnaire();
    }

    public function indexAction()
    {
        $page = $this->_getParam('page');
        $count = $this->_getParam('count', 10);
        $this->view->getAllQuestionnaire = $this->Questionnaire->getAll(null, null, $count, $page);
    }

    public function insertAction()
    {
        $this->_helper->viewRenderer->setRender('edit');
        $this->view->getAllDevolutives = $this->Devolutive->getAllDevolutiveTypes();

        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->questionnaireRowData = $questionnaireRowData = $this->_getAllParams();
        $insert = $this->Questionnaire->createQuestionnaire($questionnaireRowData);
        if (!$insert['status']) {
            $this->view->messageError = $insert['messageError'];
            return;
        }
        $this->view->questionnaireInsertSucess = true;
        $this->_forward('index');
    }

    public function editAction()
    {
        $questionnaireId = $this->view->questionnaireId = $this->_getParam('id');
        $questionnaireRow = $this->Questionnaire->getQuestionnaireById($questionnaireId);
        if (!$questionnaireRow) {
            throw new Exception('Questionário inválido, não encontrado.');
        }
        $this->view->getAllDevolutives = $this->Devolutive->getAllDevolutiveTypes();
        $this->view->questionnaireRow = $questionnaireRow;
        $this->view->questionnaireRowData = array(
            'title'                 => $questionnaireRow->getTitle(),
            'description'           => $questionnaireRow->getDescription(),
            'long_description'      => $questionnaireRow->getLongDescription(),
            'version'               => $questionnaireRow->getVersion(),
            'operation_beginning'   => $questionnaireRow->getOperationBeginning(),
            'operation_ending'      => $questionnaireRow->getOperationEnding(),
            'devolutive_id'         => $questionnaireRow->getDevolutiveCalcId(),
        );
        if (!$this->getRequest()->isPost()) {
            return;
        }
        $this->view->questionnaireRowData = $questionnaireRowData = $this->_getAllParams();
        $update = $this->Questionnaire->updateQuestionnaire($questionnaireRow, $questionnaireRowData);
        if (!$update['status']) {
            $this->view->messageError = $update['messageError'];
            return;
        }
        $this->view->questionnaireUpdateSucess = true;
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $questionnaireId = $this->_getParam('id');
        $questionnaireRow = $this->Questionnaire->getQuestionnaireById($questionnaireId);
        if (!$questionnaireRow) {
            throw new Exception('Questionário inválido.');
        }
        $delete = $this->Questionnaire->deleteQuestionnaire($questionnaireId);
        if (!$delete['status']) {
            $this->view->messageError = $delete['messageError'];
        }
        else {
            $this->view->questionnaireDeleteSucess = true;
        }
        $this->_forward('index');
    }
    
    public function manageAction()
    {
        $questionnaireId = $this->view->questionnaireId = $this->_getParam('questionnaire_id');
        if (!$questionnaireId) {
            throw new Exception('Dados inválidos.');
        }

        $questionnaireRow = $this->Questionnaire->getQuestionnaireById($questionnaireId);
        if (!$questionnaireRow) {
            throw new Exception('Questionário inválido, não encontrado.');
        }
        $this->view->questionnaireRowData = array(
            'title'                 => $questionnaireRow->getTitle(),
            'description'           => $questionnaireRow->getDescription(),
            'long_description'      => $questionnaireRow->getLongDescription(),
            'version'               => $questionnaireRow->getVersion(),
            'operation_beginning'   => $questionnaireRow->getOperationBeginning(),
            'operation_ending'      => $questionnaireRow->getOperationEnding(),
        );
        
        $this->view->alternatives = array('A', 'B', 'C', 'D', 'E');
        $this->view->defaultBlocks = array(
            /* first: system block */
            array(
                'Value' => '',
                'LongDescription' => '',
                'criterions' => array(
                    array(
                    'name' => '',
                    'apoio' => '',
                        'questions' => array(
                            array(
                                'name' => '',
                                'apoio' => '',
                                'answers' => ''
                            )
                        )
                    )
                )
            )
        );
        
        $this->view->blocks = $this->Block->getAllByQuestionnaireId($questionnaireId);
    }
    
    public function notCoopRespondingAction()
    {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $this->_forward('index', 'respond', 'questionnaire');
    }
    
    public function acompanhqstnAction()
    {
        $this->view->isViewAdmin = true;
        $this->_helper->_layout->setLayout('new-qstn');
        $this->_forward('acompanhqstn', 'respond', 'questionnaire');
    }
}


