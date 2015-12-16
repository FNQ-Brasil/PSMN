<?php

class DbTable_ManagementTheme extends Vtx_Db_Table_Abstract {
    protected $_name = 'ManagementTheme';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function getAll(){
        return $this->fetchAll();
    }

    public function getScoreByTheme($questionnaireId, $userId,$verificador = false){
        $themeScoreExpr = new Zend_Db_Expr("sum(MTQ.QuestionWeight * AL.ScoreLevel / 100) as ThemeScore");
		if($verificador) {
			$answerTb = 'AnswerVerificador';
			$answerUserId = "AN.EnterpriseId = ?";
		}
		else{
			$answerTb = 'Answer';
			$answerUserId = "AN.UserId = ?";
		}
			
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('MT' => 'ManagementTheme'), array('ThemeName' => 'Name', $themeScoreExpr, 'ThemeId' => 'Id'))
            ->joinInner(array('MTQ' => 'ManagementThemeQuestion'), 'MTQ.ManagementThemeId = MT.Id', array())
            ->joinInner(array('Q' => 'Question'), 'Q.Id = MTQ.QuestionId', array())
            ->joinInner(array('AL' => 'Alternative'), 'AL.QuestionId = Q.Id', array())
            ->joinInner(array('AN' => $answerTb), 'AN.AlternativeId = AL.Id', array())
            ->joinInner(array('C' => 'Criterion'), 'C.Id = Q.CriterionId', array())
            ->joinInner(array('B' => 'Block'), 'B.Id = C.BlockId', array())
            ->joinInner('Questionnaire', 'Questionnaire.id = B.QuestionnaireId', array())
            ->where("Questionnaire.Id = ?", $questionnaireId)
            ->where($answerUserId, $userId)
            ->group('MT.ID')
            ->order('MT.ID')
        ;
		
	

        return $this->fetchAll($query);
    }
}