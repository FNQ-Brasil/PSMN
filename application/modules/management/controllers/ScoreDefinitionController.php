<?php

class Management_ScoreDefinitionController extends Vtx_Action_Abstract
{

    public function init()
    {
        $this->questionModel = new Model_Question();
        $this->alternativeModel = new Model_Alternative();
        $this->view->questions = $this->questionModel->getAll();
    }
    
    public function indexAction() 
    {
        //$this->init();
        
        $QuestionIdentify = $this->getRequest()->getParam('question_id');
        
        if(!$QuestionIdentify) {
            return;
        }
        
        $this->view->dataFormAlt = $this->alternativeModel->getAllByQuestionId($QuestionIdentify);
        $this->view->dataForm = array('question_id' => $QuestionIdentify);
        
    }
    
    public function editAction()
    {
        $this->_helper->viewRenderer->setRender('index');
        
        $QuestionIdentify = $this->getRequest()->getParam('question_id');
        
        if(!$QuestionIdentify) {
            return;
        }
        
        $this->view->dataFormAlt = $this->alternativeModel->getAllByQuestionId($QuestionIdentify);
        
        $this->view->dataForm = $this->getRequest();
        $this->view->dataForm = array('question_id' => $QuestionIdentify);
        
        $status = true;
        $message = "";
        
        foreach ($this->view->dataFormAlt as $alternative) {
            $Key = 'score_alt_'.$alternative->getId();
            
            $update = $this->alternativeModel->updateScoreLevel($alternative, $this->_getParam($Key));
            
            if ($update['status'] == false) {
                $status = false;
                $message = $update['messageError'];
            }
        }
       
        if ($status) {
            $this->view->crudMessageSucess = 'Pontuações alteradas com sucesso.';
            unset($this->view->dataForm);
            unset($this->view->dataFormAlt);
        }
        else {
            $this->view->messageError = $message;
        }

        $this->_forward('index');
    }
    
}


