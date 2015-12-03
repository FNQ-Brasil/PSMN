<?php

class Manager_ExecutionPontuacao {
    protected $ManagementTheme;
    protected $Execution;
    protected $ExecutionPontuacao;

    public function __construct(){
        $this->ManagementTheme = new Model_ManagementTheme();
        $this->Execution = new Model_Execution();
        $this->ExecutionPontuacao = new Model_ExecutionPontuacao();
    }

    public function updateExecutionScore($questionnaireId, $blockId, $userId){
        $execution = $this->getExecutionFor($questionnaireId, $userId);
        $executionPontuacao = $this->ExecutionPontuacao->getRowByExecutionId($execution->getId());

        $attributes = array();
        $attributes['negociosTotal'] = $this->calculateExecutionScore($questionnaireId, $userId);

        if(is_null($executionPontuacao)){
            $attributes['executionId'] = $execution->getId();
            $this->ExecutionPontuacao->createExecutionPontuacao($attributes, $blockId);
        }else{
            $this->ExecutionPontuacao->updateExecutionPontuacao($execution->getId(), $attributes, $blockId);
        }
    }

    private function getExecutionFor($questionnaireId, $userId){
        return $this->Execution->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);
    }

    public function calculateExecutionScore($questionnaireId, $userId,$verificador = false){
        $executionScore = 0;
        $themesScore = $this->ManagementTheme->getScoreByTheme($questionnaireId, $userId,$verificador);
        foreach($themesScore as $themeScore) $executionScore += $themeScore->getThemeScore();
		if($executionScore > 0) return ($executionScore = $executionScore / count($themesScore));
		else  return 0;
    }

}