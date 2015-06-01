<?php

class DbTable_Question extends Vtx_Db_Table_Abstract
{

    protected $_name = 'Question';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'QuestionType' => array(
          	'columns' => 'QuestionTypeId',
            'refTableClass' => 'QuestionType',
            'refColumns' => 'Id'
        ),
        'Criterion' => array(
          	'columns' => 'CriterionId',
            'refTableClass' => 'Criterion',
            'refColumns' => 'Id'
        ),
        'ParentQuestion' => array(
          	'columns' => 'ParentQuestionId',
            'refTableClass' => 'ParentQuestion',
            'refColumns' => 'Id'
        )
    );
    
    protected $_dependentTables = array(
        'DbTable_Alternative',
        'DbTable_Answer',
        'DbTable_AnnualResult',
        'DbTable_Question',
        'DbTable_QuestionHistory',
        'DbTable_QuestionTip'
    );
    
    public function getHigherOrder($criterionId){
        $query = $this->select()
            ->from(
                array('q' => 'Question'),
                array('HigherOrder' => 'MAX(q.Designation)')
            )
            ->where('q.CriterionId = ?', $criterionId);
        $objResultQuestion = $this->fetchRow($query);
		return $objResultQuestion->getHigherOrder();
	}
    
    public function getSmallerOrder($criterionId){
        $query = $this->select()
            ->from(
                array('q' => 'Question'),
                array('SmallerOrder' => 'MIN(q.Designation)')
            )
            ->where('q.CriterionId = ?', $criterionId);
        $objResultQuestion = $this->fetchRow($query);
		return $objResultQuestion->getSmallerOrder();
	}

	static public function reorder($criterion_id, $fromId = null, $fromOldPosition = null, $oldPositionId = null)
	{
        if (!is_numeric($criterion_id)){
            return false;
        }
        $modelQuestion = new Model_Question();
        $orderBy = 0;
        $where = array('CriterionId = ?' => $criterion_id);
        $order = array('Designation ASC');
        $questions = $modelQuestion->getAll($where, $order);
        $newPositionRowId = null;
        if ($fromId) {
            $newPositionRow     = $modelQuestion->getQuestionById($fromId);
            $newPositionRowId   = $newPositionRow->getId()?$newPositionRow->getId():null;            
        }
        if ($fromOldPosition) {
            $fromOldPosition    = $fromOldPosition?$fromOldPosition:null;
        }
        $toPositionRowId = null;
        if ($oldPositionId) {
            $toPositionRow      = $modelQuestion->getQuestionById($oldPositionId);
            $toPositionRowId    = $toPositionRow->getId()?$toPositionRow->getId():null;
            $toNewPosition      = $toPositionRow->getDesignation()?$toPositionRow->getDesignation():null;     
        }
        if (!is_object($questions) && !(count($questions) > 0)) {
            return false;    
        }
        foreach ($questions as $questionRow) {
            $orderBy++;
            if ( $questionRow->getId() == $newPositionRowId ) { // não editar $newPositionRowId
                continue;
            }
            $data['parent_id']            = $criterion_id;
            $data['question_type_id']     = $questionRow->getQuestionTypeId();
            $data['parent_question_id']   = $questionRow->getParentQuestionId();
            $data['value']                = $questionRow->getValue();
            $data['supporting_text']      = $questionRow->getSupportingText();
            $data['version']              = $questionRow->getVersion();
            $data['status']               = $questionRow->getStatus();
            $data['designation'] = $orderBy;
            if ($questionRow->getId() == $toPositionRowId) {
                $questionRow->setId($toPositionRowId);
                $data['designation'] = $toNewPosition +
                    (( $fromOldPosition <= $toNewPosition ) ? - 1 : + 1);
            }
            $modelQuestion->updateQuestion($questionRow,$data);
        }
        return true;
    }

    public function getIdDesignation($criterionId){
    $query = $this->select()
        ->from(
            array('q' => 'Question'),
            array('Id', 'Designation')
        )
        ->where('q.CriterionId = ?', $criterionId);
    $objResultQuestion = $this->fetchAll($query);
            return $objResultQuestion;
    }

    public function isAnsweredByUserId($questionId,$userId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ANS' => 'Answer'),
                array('AnswerId' => 'Id')
            )
            ->join(
                array('ALT' => 'Alternative'), 'ALT.Id = ANS.AlternativeId',
                array('AlternativeId' => 'Id')
            )
            ->join(
                array('QST' => 'Question'), 'QST.Id = ALT.QuestionId')
            ->where('ANS.UserId = ?', $userId)
            ->where('QST.Id = ?', $questionId);
        
        return $this->fetchAll($query)->count();
    }

    public function isAnswered($questionId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ANS' => 'Answer'),
                array('AnswerId' => 'Id')
            )
            ->join(
                array('ALT' => 'Alternative'), 'ALT.Id = ANS.AlternativeId',
                array('AlternativeId' => 'Id')
            )
            ->join(
                array('QST' => 'Question'), 'QST.Id = ALT.QuestionId')
            ->where('QST.Id = ?', $questionId);
        
        return $this->fetchAll($query)->count();
    }

    public function getAnswer($questionId,$userId) 
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ANS' => 'Answer'),
                array('AnswerId' => 'Id', 'AnswerValue')
            )
            ->joinLeft(
                array('ALT' => 'Alternative'), 'ALT.Id = ANS.AlternativeId',
                array('AlternativeId' => 'Id',
                      'AlternativeTypeId'
                )
            )
            ->join(
                array('QST' => 'Question'), 'QST.Id = ALT.QuestionId')
            ->where('ANS.UserId = ?', $userId)
            ->where('QST.Id = ?', $questionId);
        
        return $this->fetchRow($query);
    }
    
    /**
     * 
     * recupera respostas do tipo anual
     * 
     * @param type $answerId
     * @param type $alternativeId
     * @return type
     */
    public function getAnswerAnnualResult($answerId, $alternativeId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ARD' => 'AnnualResultData'),
                array('Year')
            )
            ->join(
                array('ANR' => 'AnnualResult'), 'ARD.AnnualResultId = ANR.Id',
                array('Mask' => new Zend_Db_Expr('CASE Mask WHEN "percentual" THEN "%" WHEN "moeda" THEN "R$" ELSE "" END'))
            )
            ->joinLeft(
                array('AAR' => 'AnswerAnnualResult'), 'ARD.Id = AAR.AnnualResultDataId AND AAR.AnswerId = '.$answerId,
                array('AnswerAnnualResultId' => 'Id', 'Value')
            )
            ->where('ARD.AlternativeId = ?', $alternativeId)
            ->order('ARD.Year ASC');
        
        return $this->fetchAll($query);
    }
    
    public function getAllByQuestionnaireId($questionnaireId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QNR' => 'Questionnaire'),
                array('Questionario' => 'QNR.Title')
            )
            ->join(
                array('BLK' => 'Block'), 'QNR.Id = BLK.QuestionnaireId',
                array('Bloco' => 'BLK.designation',
                      'BlocoTitulo' => 'BLK.Value' 
                )
            )
            ->join(
                array('CRT' => 'Criterion'), 'BLK.Id = CRT.BlockId',
                array('Criterio' => 'CRT.designation',
                      'CriterioTitulo' => 'CRT.Value' 
                )
            )    
            ->join(
                array('QST' => 'Question'), 'CRT.Id = QST.CriterionId',
                array('Id' => 'QST.Id',
                      'Questao' => 'QST.Value',
                      'Designacao' => 'QST.Designation',
                      'Texto' => 'QST.SupportingText'
                )
            )
            ->where('QNR.Id = ?', $questionnaireId)
            ->order('BLK.Designation')
            ->order('CRT.Designation')
            ->order('QST.Designation');
        
        return $this->fetchAll($query);
    }
    
    
    /**
     * recupera dados do bloco do Questionario e informacoes das questoes relacionadas ao bloco do questionario.
     * 
     * @param type $questionnaireId
     * @param type $blockId
     * @return type
     */
    public function getAllByQuestionnaireIdBlockId($questionnaireId, $blockId = null)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('QNR' => 'Questionnaire'),
                array('Questionario' => 'QNR.Title')
            )
            ->join(
                array('BLK' => 'Block'), 'QNR.Id = BLK.QuestionnaireId',
                array('Bloco' => 'BLK.designation',
                      'BlocoTitulo' => 'BLK.Value' 
                )
            )
            ->join(
                array('CRT' => 'Criterion'), 'BLK.Id = CRT.BlockId',
                array('Criterio' => 'CRT.designation',
                      'CriterioTitulo' => 'CRT.Value' 
                )
            )    
            ->join(
                array('QST' => 'Question'), 'CRT.Id = QST.CriterionId',
                array('Id' => 'QST.Id',
                      'Questao' => 'QST.Value',
                      'Designacao' => 'QST.Designation',
                      'Texto' => 'QST.SupportingText'
                )
            )
            ->where('QNR.Id = ?', $questionnaireId);
            
            if ($blockId != null) {
                $query->where('BLK.Id = ?', $blockId);
            }
            
            $query->order('BLK.Designation')
            ->order('CRT.Designation')
            ->order('QST.Designation');
        
         return $this->fetchAll($query);
    }
}
