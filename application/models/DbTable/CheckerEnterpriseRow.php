<?php

class DbTable_CheckerEnterpriseRow extends Vtx_Db_Table_Row_Abstract
{   
    public function getCommentAnswers()
    {
        $tbCheckerEvaluation = DbTable_CheckerEvaluation::getInstance();
        $select = $tbCheckerEvaluation->select()
            ->from(
                $tbCheckerEvaluation, array('CriterionNumber', 'Comment')
            )
            ->where(
                'CheckerEnterpriseId = ? and CheckerEvaluationTypeId = 1', $this->getId()
            );

        return $tbCheckerEvaluation->fetch($select, 'assoc');
    }
    
    public function getAnswers()
    {
        $tbCheckerEvaluation = DbTable_CheckerEvaluation::getInstance();
        $select = $tbCheckerEvaluation->select()
            ->from(
                $tbCheckerEvaluation, array('QuestionCheckerId', 'Resposta')
            )
            ->where(
                'CheckerEnterpriseId = ? and CheckerEvaluationTypeId = 2', $this->getId()
            );

        return $tbCheckerEvaluation->fetch($select, 'assoc');
    }
}
