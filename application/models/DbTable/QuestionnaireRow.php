<?php

class DbTable_QuestionnaireRow extends Vtx_Db_Table_Row_Abstract
{    
    public function getAllBlocksCriterionsForView()
    {
       $questions = DbTable_Questionnaire::getInstance()->getAllBlocksCriterionsById($this->getId());
       $lastBlockId = $lastCriterionId = $lastQuestionId = null;
       $blocks = array();

        foreach ($questions as $question) {
            $blockId = $question->getBlockId();
            $criterionId = $question->getCriterionId();
            $questionId = $question->getQuestionId();

            if ($lastBlockId != $blockId) {
                $blocks[$blockId] = array(
                    'BlockValue' => $question->getBlockValue(),
                    'Criterions' => array()
                );
            }

            if ($lastCriterionId != $criterionId) {
                $blocks[$blockId]['Criterions'][$criterionId] = array(
                    'CriterionValue' => $question->getCriterionValue(),
                    'Questions' => array()
                );
            }

            if ($lastQuestionId != $questionId) {
                $blocks[$blockId]['Criterions'][$criterionId]['Questions'][$questionId] = array(
                    'QuestionId' => $questionId
                );
            }

            $lastCriterionId = $criterionId;
            $lastBlockId = $blockId;
            $lastQuestionId = $questionId;
        }
        return $blocks;
    }
    
    /**
     * 
     * Consulta os criterios e dados de um bloco do BD ou recupera/grava no sistema de cache
     */
    public function cacheBlockAndCriterion($blocoId)
    {        
        $mpeCache = new Vtx_Cache_MPE_QuestionarioCache();
        
        $blocoCacheModel = $mpeCache->BlocoECriterios($blocoId, $this);
        
        return $blocoCacheModel;
    }
    
    
    /**
     * lista questoes e criterios de um bloco de questionario
     * 
     * @param type $blocoId
     * @return type
     */
    public function getAllQuestionsByBlockIdAndCriterionsForView($blocoId)
    {
        $questionnaireId = $this->getId();
        $questions = DbTable_Questionnaire::getInstance()->getBlockAndCriterionsById($questionnaireId, $blocoId);
        
        //echo "<br><br>";
        //var_dump('questions',$questions);
        
        //$questions = DbTable_Questionnaire::getInstance()->getAllBlocksCriterionsById($this->getId());

        $lastBlockId = $lastCriterionId = $lastQuestionId = null;
        $blocks = array();

        foreach ($questions as $question) {
            $blockId = $question->getBlockId();
            $criterionId = $question->getCriterionId();
            $questionId = $question->getQuestionId();

            if ($lastBlockId != $blockId) {
                $blocks[$blockId] = array(
                    'BlockValue' => $question->getBlockValue(),
                    'Criterions' => array()
                );
            }

            if ($lastCriterionId != $criterionId) {
                $blocks[$blockId]['Criterions'][$criterionId] = array(
                    'CriterionValue' => $question->getCriterionValue(),
                    'Questions' => array()
                );
            }

            if ($lastQuestionId != $questionId) {
                $blocks[$blockId]['Criterions'][$criterionId]['Questions'][$questionId] = array(
                    'QuestionId' => $questionId
                );
            }

            $lastCriterionId = $criterionId;
            $lastBlockId = $blockId;
            $lastQuestionId = $questionId;
        }
        return $blocks;
    }    
}
