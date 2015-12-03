<?php

class DbTable_Questionnaire extends Vtx_Db_Table_Abstract
{

    protected $_name = 'Questionnaire';
    protected $_id = 'Id';
    protected $_sequence = true;
	protected $_rowClass = 'DbTable_QuestionnaireRow';
    
    protected $_dependentTables = array(
        'DbTable_Block',
        'DbTable_Execution'
    );
    public $dateTime = "";
    
    public function getAllBlocksById($questionnaireId)
    {       
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QTR' => $this->_name), null
            )
            ->joinInner(
                array('BLK' => 'Block'), 'QTR.Id = BLK.QuestionnaireId',
                array('BlockId' => 'Id', 'BlockValue' => 'Value')
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'BLK.Id = CRT.BlockId', null
            )
            ->joinInner(
                array('QST' => 'Question'), 'CRT.Id = QST.CriterionId',
                array(
                    'QuestionOrder' => 'Designation', 'QuestionId' => 'Id',
                    'QuestionValue' => 'Value', 'SupportingText', 'QuestionTypeId'
                )
            )
            ->joinInner(
                array('ALT' => 'Alternative'), 'QST.Id = ALT.QuestionId',
                array(
                    'AlternativeOrder' => 'Designation', 'AlternativeId' => 'Id',
                    'AlternativeValue' => 'Value'
                )
            )
            ->where('QTR.Id = ?', $questionnaireId)
            ->where('ALT.Value <> ?', '.')
            ->order('BLK.Designation')
            ->order('CRT.Designation')
            ->order('QST.Designation')
            ->order('ALT.Designation');

        return $this->fetchAll($query);
    }
    
    function getQuestionnaireById($Identify)
    {
        return $this->fetchRow(array('Id = ?' => $Identify));
    }

    /**
     * A variavel $currentAutoavaliacaoId, que esta na tabela Configuration é o ID do questionario corrente.
     * 
     */
    public function getQuestionnaireIdByCompetitionId($competitionId)
    {
        $row = $this->fetchRow(array('CompetitionId = ?' => $competitionId));
        $currentAutoavaliacaoId = (!is_null($row) and $row) ? $row->getId() : null; 
        return $currentAutoavaliacaoId;
    }
    

    /**
     * 
     * 
     * @param int $questionnaireId
     * @return mixed
     */
    public function getBlockAndCriterionsById($questionnaireId, $blockId)
    {   
        
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QTR' => $this->_name), null
            )
            ->joinInner(
                array('BLK' => 'Block'), 'QTR.Id = BLK.QuestionnaireId',
                array('BlockId' => 'Id', 'BlockValue' => 'Value')
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'BLK.Id = CRT.BlockId',
                array('CriterionId' => 'Id', 'CriterionValue' => 'Value')
            )
            ->joinInner(
                array('QST' => 'Question'), 'CRT.Id = QST.CriterionId',
                array('QuestionOrder' => 'Designation', 'QuestionId' => 'Id')
            )
            ->where('QTR.Id = ?', $questionnaireId)
            ->where('BLK.Id = ?', $blockId)
            ->order('BLK.Designation')
            ->order('CRT.Designation')
            ->order('QST.Designation');

        $result = $this->fetchAll($query);
        //var_dump($result);
        
        return $result;
        
    }
    
    public function getBlocks($questionnaireId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('BLK' => 'Block'), 
                array('Id')
            )
            ->where('BLK.QuestionnaireId = ?', $questionnaireId)
            ->order('BLK.Designation');
        
        $objResult = $this->fetchAll($query);
        
        return $objResult;
        
        
    }
    
    public function getBlocksAutoavaliacao($questionnaireId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('BLK' => 'Block'), 
                array('Id')
            )
            ->where('BLK.QuestionnaireId = ?', $questionnaireId)
            ->order('BLK.Designation')
            ->limit(2);
        
        $objResult = $this->fetchAll($query);
        
        if ($objResult) {
        
            $arrResult = array();
            foreach ($objResult AS $objReg) {
                $arrResult[] = $objReg->getId();
            }
            // verifica qts blocos tem o questionario.
            if (count($arrResult) == 1) {
                return $arrResult;
            }
            return false;
            
        }
        return false;
        
    }
    
    public function getCriterionTabulation($questionnaireId, $blockId) 
    {
        $queryMaxScoreLevel = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST' => 'Question'), null
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'QST.CriterionId = CRT.Id',
                array('CriterionId' => 'Id','Designation')
            )
            ->joinInner(
                array('BLK' => 'Block'), 'CRT.BlockId = BLK.Id', null
            )
            ->joinInner(
                array('QTR' => 'Questionnaire'), 'BLK.QuestionnaireId = QTR.Id', null
            )
            ->joinInner(
                array('ALT' => 'Alternative'), 'QST.Id = ALT.QuestionId',
                array('Pontuacao' => 'MAX(ScoreLevel)')
            )
            ->where('QTR.Id = ?', $questionnaireId)
            ->where('BLK.Id = ?', $blockId)    
            ->order('CRT.Designation')
            ->group('QST.Id');
           
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('PTS' => $queryMaxScoreLevel), 
                array(
                    'Designation',
                    'CriterionId', 
                    'SumPontuacao' => 'SUM(Pontuacao)'
                )
            )
            ->group('PTS.CriterionId');
            
        return $this->fetchAll($query);
    }
    
    public function getPunctuationByCriterion($questionnaireId, $blockId, $userId)
    {
        $queryPontos = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST' => 'Question'), null
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'QST.CriterionId = CRT.Id',
                array('CriterionId' => 'Id','Designation')
            )
            ->joinInner(
                array('BLK' => 'Block'), 'CRT.BlockId = BLK.Id', null
            )
            ->joinInner(
                array('QTR' => 'Questionnaire'), 'BLK.QuestionnaireId = QTR.Id', null
            )
            ->joinInner(
                array('ALT' => 'Alternative'), 'QST.Id = ALT.QuestionId',
                array('Pontos' => 'ScoreLevel')
            )
            ->joinInner(
                array('ANS' => 'Answer'), 'ALT.Id = ANS.AlternativeId', null
            )
            ->where('QTR.Id = ?', $questionnaireId)
            ->where('BLK.Id = ?', $blockId)
            ->where('ANS.UserId = ?', $userId)
            ->order('CRT.Designation')
            ->group('QST.Id')
            ->having('count(QST.Id) = 1');
           
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('PTS' => $queryPontos), 
                array(
                    'Designation',
                    'CriterionId', 
                    'Nota' => 'SUM(Pontos)'
                )
            )
            ->group('PTS.CriterionId');
           
        return $this->fetchAll($query);
    }
    
    public function makeScore($questionnaireId, $userId, $blockId) 
    {
        $arrDataTab = $this->getQuestionsPunctuationByBlock($questionnaireId, $userId, $blockId);

        $scorePart = 0;
        foreach($arrDataTab AS $dataTab) {
            $scorePart = $scorePart + $dataTab->getPontos();
        } 

        return $scorePart;
    }
    
    public function getQuestionsPunctuationByBlock($questionnaireId, $userId, $blockId = null)
    {
/*
        SELECT 
                `Questoes`.`Bloco`,
                `Questoes`.`BD`,
                `Questoes`.`Criterio`,
                `Questoes`.`CD`,
                `Questoes`.`Questao`,
                `Questoes`.`QD`,
                `Respostas`.`Alternativa`,
                `Respostas`.`Pontos`
        FROM 
                (SELECT 
                        `BLK`.`Id` AS `Bloco`, 
                        `BLK`.`Designation` AS `BD`, 
                        `CRT`.`Id` AS `Criterio`,
                        `CRT`.`Designation` AS `CD`, 
                        `QST`.`Id` AS `Questao`,
                        `QST`.`Designation` AS `QD` 
                FROM `Question` AS `QST` 
                INNER JOIN `Criterion` AS `CRT` ON QST.CriterionId = CRT.Id 
                INNER JOIN `Block` AS `BLK` ON CRT.BlockId = BLK.Id 
                INNER JOIN `Questionnaire` AS `QTR` ON BLK.QuestionnaireId = QTR.Id
                WHERE 
                        (QTR.Id = '2') AND 
                        (BLK.Id in ('3','4')))  AS Questoes
        LEFT JOIN 	
                (SELECT 
                        `QST`.`Id` AS `Questao`, 
                        `ALT`.`Designation` AS `Alternativa`,
                        `ALT`.`ScoreLevel` AS `Pontos` 
                FROM `Question` AS `QST` 
                INNER JOIN `Criterion` AS `CRT` ON QST.CriterionId = CRT.Id 
                INNER JOIN `Block` AS `BLK` ON CRT.BlockId = BLK.Id 
                INNER JOIN `Questionnaire` AS `QTR` ON BLK.QuestionnaireId = QTR.Id 
                INNER JOIN `Alternative` AS `ALT` ON QST.Id = ALT.QuestionId 
                INNER JOIN `Answer` AS `ANS` ON ALT.Id = ANS.AlternativeId 
                WHERE 
                        (QTR.Id = '2') AND 
                        (BLK.Id in ('3')) AND 
                        (ANS.UserId = '1') 
                GROUP BY 
                        `QST`.`Id` 
                HAVING 
                        (count(QST.Id) = 1)) AS Respostas
                ON 
                        `Questoes`.`Questao` = `Respostas`.`Questao` 
        ORDER BY `Questoes`.`BD` ASC,
                         `Questoes`.`CD` ASC, 
                         `Questoes`.`QD` ASC
*/
        $queryQuestoes = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST' => 'Question'), 
                array('Questao'=>'Id',
                      'QD'=>'Designation'
                )
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'QST.CriterionId = CRT.Id', 
                array('Criterio'=>'Id',
                      'CD'=>'Designation'
                )
            )
            ->joinInner(
                array('BLK' => 'Block'), 'CRT.BlockId = BLK.Id', 
                array('Bloco'=>'Id',
                      'BD'=>'Designation'
                )
            )
            ->joinInner(
                array('QTR' => 'Questionnaire'), 'BLK.QuestionnaireId = QTR.Id', null
            )
            ->where('QTR.Id = ?', $questionnaireId);
            
            if($blockId != null) {
                $queryQuestoes->where('BLK.Id IN (?)', $blockId);
            }
            
        $queryRespostas = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST' => 'Question'),
                array('Questao'=>'Id')
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'QST.CriterionId = CRT.Id', null
            )
            ->joinInner(
                array('BLK' => 'Block'), 'CRT.BlockId = BLK.Id', null
            )
            ->joinInner(
                array('QTR' => 'Questionnaire'), 'BLK.QuestionnaireId = QTR.Id', null
            )
            ->joinInner(
                array('ALT' => 'Alternative'), 'QST.Id = ALT.QuestionId',
                array('Alternativa'=>'Designation',
                      'Pontos'=>'ScoreLevel'
                )    
            )
            ->joinInner(
                array('ANS' => 'Answer'), 'ALT.Id = ANS.AlternativeId', null
            )
            ->where('QTR.Id = ?', $questionnaireId);
            
            if($blockId != null) {
                $queryRespostas->where('BLK.Id IN (?)', $blockId);   
            }
            
            $queryRespostas->where('ANS.UserId = ?', $userId)
            ->group('QST.Id')
            ->having('count(QST.Id) = 1');
            
        
        $queryPontuacao =  $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('Questoes' => $queryQuestoes),
                array('Bloco',
                      'BD',
                      'Criterio',
                      'CD',
                      'Questao',
                      'QD')
            )
            ->joinLeft(
                array('Respostas' => $queryRespostas), 'Questoes.Questao = Respostas.Questao',
                array('Alternativa' => new Zend_Db_Expr('CASE WHEN Respostas.Alternativa IS NULL THEN 0 ELSE Respostas.Alternativa END'),
                      'Pontos' => new Zend_Db_Expr('CASE WHEN Respostas.Pontos IS NULL THEN 0 ELSE Respostas.Pontos END')
                )
            )
            ->order('Questoes.BD')
            ->order('Questoes.CD')
            ->order('Questoes.QD');
        
        return $this->fetchAll($queryPontuacao);
    }
    
    public function getQuestionsAnsweredByUserId($QstnId, $UserId, $fetch = 'all', $blockId = false)
    {       
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST' => 'Question'), array('Id')
            )
            ->joinLeft(
                array('CRT' => 'Criterion'), 'QST.CriterionId = CRT.Id', null
            )
            ->joinInner(
                array('BLK' => 'Block'), 'CRT.BlockId = BLK.Id', null
            )
            ->joinInner(
                array('QTR' => 'Questionnaire'), 'BLK.QuestionnaireId = QTR.Id', null
            )
            ->joinInner(
                array('ALT' => 'Alternative'), 'QST.Id = ALT.QuestionId', null
            )
            ->joinInner(
                array('ANS' => 'Answer'), 'ALT.Id = ANS.AlternativeId', array('AlternativeId', 'AnswerValue')
            )
            ->joinLeft(
                array('ANF' => 'AnswerFeedback'), 'ANS.Id = ANF.AnswerId', 
                array('Feedbacks' => 'count(ANF.Id)')
            )
            ->where('QTR.Id = ?', $QstnId)
            ->where('ANS.UserId = ?', $UserId);

        //if ($blockId) {
          //  $query->where('BLK.Id = ?', $blockId);
        //}
            $query->group('QST.Id')
            ->order('QST.Designation');

        return $this->fetch($query, $fetch);
    }


    public function getQuestionsAnsweredByUserIdVerificador($QstnId, $UserId,$EnterpriseId, $fetch = 'all', $blockId = false)
    {       
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST' => 'Question'), array('Id')
            )
            ->joinLeft(
                array('CRT' => 'Criterion'), 'QST.CriterionId = CRT.Id', null
            )
            ->joinInner(
                array('BLK' => 'Block'), 'CRT.BlockId = BLK.Id', null
            )
            ->joinInner(
                array('QTR' => 'Questionnaire'), 'BLK.QuestionnaireId = QTR.Id', null
            )
            ->joinInner(
                array('ALT' => 'Alternative'), 'QST.Id = ALT.QuestionId', null
            )
            ->joinInner(
                array('ANS' => 'AnswerVerificador'), 'ALT.Id = ANS.AlternativeId', array('AlternativeId', 'AnswerValue')
            )
            ->joinLeft(
                array('ANF' => 'AnswerFeedback'), 'ANS.Id = ANF.AnswerId', 
                array('Feedbacks' => 'count(ANF.Id)')
            )
            ->where('QTR.Id = ?', $QstnId)
            ->where('ANS.UserId = ?', $UserId)
			->where('ANS.EnterpriseId = ?', $EnterpriseId);
			

            $query->group('QST.Id')
            ->order('QST.Designation');

        return $this->fetch($query, $fetch);
    }
    
    /**
     * Faz verificacao se um usuario respondeu todas as questoes 
     * de um questionario (@param $questionnaireId) ou bloco (@param $blockId).
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @param type $blockId
     * @return type
     */
    public function isFullyAnswered($questionnaireId,$userId, $blockId = null)
    {
        $queryQuestoesRespondidas = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST2' => 'Question'), 
                array('Question' => 'Id')
            )
            ->joinInner(
                array('CRT2' => 'Criterion'), 'QST2.CriterionId = CRT2.Id', null
            )
            ->joinInner(
                array('BLK2' => 'Block'), 'CRT2.BlockId = BLK2.Id', null
            )
            ->joinInner(
                array('QTR2' => 'Questionnaire'), 'BLK2.QuestionnaireId = QTR2.Id', null
            )
            ->joinInner(
                array('ALT2' => 'Alternative'), 'QST2.Id = ALT2.QuestionId', null
            )
            ->joinInner(
                array('ANS2' => 'Answer'), 'ALT2.Id = ANS2.AlternativeId', null
            )
            ->where('QTR2.Id = ?', $questionnaireId)
            ->where('ANS2.UserId = ?', $userId);
                
             if ($blockId) {                   
                $queryQuestoesRespondidas->where('BLK2.Id = ?', $blockId)   ;
             }
             
            
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST1' => 'Question'), 
                array('Question' => 'Id')
            )
            ->joinInner(
                array('CRT1' => 'Criterion'), 'QST1.CriterionId = CRT1.Id', null
            )
            ->joinInner(
                array('BLK1' => 'Block'), 'CRT1.BlockId = BLK1.Id', null
            )
            ->joinInner(
                array('QTR1' => 'Questionnaire'), 'BLK1.QuestionnaireId = QTR1.Id', null
            )
            ->where('QTR1.Id = ?', $questionnaireId)
            ->where('QST1.Id NOT IN (?)', $queryQuestoesRespondidas);

             if ($blockId) {                   
                $query->where('BLK1.Id = ?', $blockId)   ;
             }

        $fetchRow = $this->fetchRow($query);
        //$fetchRow = $this->fetchAll($query)->current();
        
        
        //return $this->fetchAll($query)->current();
        return $fetchRow;
    }

    public function needToFinish($questionnaireId,$userId)
    {
        $queryQuestoesRespondidas = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST2' => 'Question'), 
                array('Question' => 'Id', )
            )
            ->joinInner(
                array('CRT2' => 'Criterion'), 'QST2.CriterionId = CRT2.Id', null
            )
            ->joinInner(
                array('BLK2' => 'Block'), 'CRT2.BlockId = BLK2.Id', null
            )
            ->joinInner(
                array('QTR2' => 'Questionnaire'), 'BLK2.QuestionnaireId = QTR2.Id', null
            )
            ->joinInner(
                array('ALT2' => 'Alternative'), 'QST2.Id = ALT2.QuestionId', null
            )
            ->joinInner(array('ANS2' => 'Answer'), 'ALT2.Id = ANS2.AlternativeId', null)
            ->where('QTR2.Id = ?', $questionnaireId)
            ->where('ANS2.UserId = ?', $userId);
            
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST1' => 'Question'), 
                array('Question' => 'Id','Value' )
            )
            ->joinInner(
                array('CRT1' => 'Criterion'), 'QST1.CriterionId = CRT1.Id', array('Criterion' => 'Id','Value')
            )
            ->joinInner(
                array('BLK1' => 'Block'), 'CRT1.BlockId = BLK1.Id', array('Block' => 'Id','Value')
            )
            ->joinInner(
                array('QTR1' => 'Questionnaire'), 'BLK1.QuestionnaireId = QTR1.Id', array('Questionnaire' => 'Id','Title')
            )

            ->where('QTR1.Id = ?', $questionnaireId)
            ->where('QST1.Id NOT IN (?)', $queryQuestoesRespondidas);
        //echo $query;
        
        if ($this->fetchAll($query)) {
            return $this->fetchAll($query);
        } 
        return true;
    }
    
    public function getQuestionnaireTotalQuestions($questionnaireId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QST' => 'Question'), 
                array('QtdTotal' => 'count(QST.Id)')
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'QST.CriterionId = CRT.Id', null
            )
            ->joinInner(
                array('BLK' => 'Block'), 'CRT.BlockId = BLK.Id', null
            )
            ->joinInner(
                array('QTR' => 'Questionnaire'), 'BLK.QuestionnaireId = QTR.Id', null
            )
            ->where('QTR.Id = ?', $questionnaireId);
        
        return $this->fetchAll($query)->current();
    }

}
