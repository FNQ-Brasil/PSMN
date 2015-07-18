<?php

class DbTable_Block extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Block';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Questionnaire' => array(
          	'columns' => 'QuestionnaireId',
            'refTableClass' => 'Questionnaire',
            'refColumns' => 'Id'
        ),
        'Question' => array(
          	'columns' => 'QuestionId',
            'refTableClass' => 'Question',
            'refColumns' => 'Id'
        )
    );

    protected $_dependentTables = array(
        'DbTable_Criterion'
    );

    public function getHigherOrder($questionnaireId){
        $select = $this->select()
            ->from(
                array('b' => 'Block'),
                array('HigherOrder' => 'MAX(b.Designation)')
            )
            ->where('b.QuestionnaireId = ?', $questionnaireId);
        $objResultBlock = $this->fetchRow($select);
		return $objResultBlock->getHigherOrder();
	}
    
	public function getSmallerOrder($questionnaireId){
        $query = $this->select()
            ->from(
                array('b' => 'Block'),
                array('SmallerOrder' => 'MIN(b.Designation)')
            )
            ->where('b.QuestionnaireId = ?', $questionnaireId);
        $objResultBlock = $this->fetchRow($query);
		return $objResultBlock->getSmallerOrder();
	}

	static public function reorder($questionnaire_id, $fromId = null, $fromOldPosition = null, $oldPositionId = null)
	{
        if( !is_numeric($questionnaire_id) ){
            return false;
        }
        $modelBlock = new Model_Block();
        $orderBy = 0;
        $where = array('QuestionnaireId = ?' => $questionnaire_id);
        $order = array('Designation ASC');
        $blocks = $modelBlock->getAll($where, $order);
        $newPositionRowId       = null;
        if ($fromId) {
            $newPositionRow     = $modelBlock->getBlockById($fromId);
            $newPositionRowId   = $newPositionRow->getId()?$newPositionRow->getId():null;            
        }
        if ($fromOldPosition) {
            $fromOldPosition    = $fromOldPosition?$fromOldPosition:null;
        }
        $toPositionRowId = null;
        if ($oldPositionId) {
            $toPositionRow      = $modelBlock->getBlockById($oldPositionId);
            $toPositionRowId    = $toPositionRow->getId()?$toPositionRow->getId():null;
            $toNewPosition      = $toPositionRow->getDesignation()?$toPositionRow->getDesignation():null;     
        }
        if (!is_object($blocks) || !(count($blocks) > 0)) {
            return false;
        }
        foreach ($blocks as $blockRow)
        {
            $orderBy++;
            if ( $blockRow->getId() == $newPositionRowId ) { // não editar $newPositionRowId
                continue;
            }
            $data['questionnaire_id']  = $questionnaire_id;
            $data['value']             = $blockRow->getValue();
            $data['long_description']  = $blockRow->getLongDescription();
            $data['conclusion_text']   = $blockRow->getConclusionText();
            if ( $blockRow->getId() == $toPositionRowId ) {
                $blockRow->setId($toPositionRowId);
                if ( $fromOldPosition <= $toNewPosition ) { 
                    $data['designation'] = $toNewPosition - 1;
                } else {
                    $data['designation'] = $toNewPosition + 1;
                }
            } else {
                $data['designation'] = $orderBy;
            }
            $modelBlock->updateBlock($blockRow,$data);
        }
        return true;
    }
    
	public function getIdDesignation($questionnaireId){
        $query = $this->select()
            ->from(
                array('b' => 'Block'),
                array('Id', 'Designation')
            )
            ->where('b.QuestionnaireId = ?', $questionnaireId);
        $objResultBlock = $this->fetchAll($query);
		return $objResultBlock;
	}
    
    public function getQuestionsByBlockId($blockId)
    {       
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('BLK' => $this->_name),
                array('BlockId' => 'Id')
            )
            ->joinInner(
                array('CRT' => 'Criterion'), 'BLK.Id = CRT.BlockId', null
            )
            ->joinInner(
                array('QST' => 'Question'), 'CRT.Id = QST.CriterionId',
                array(
                    'QuestionOrder' => 'Designation', 'QuestionId' => 'Id',
                    'QuestionValue' => 'Value', 'SupportingText', 'QuestionTypeId',
                    'Summary'
                )
            )
            ->joinInner(
                array('ALT' => 'Alternative'), 'QST.Id = ALT.QuestionId',
                array(
                    'AlternativeOrder' => 'Designation', 'AlternativeId' => 'Id',
                    'AlternativeValue' => 'Value'
                )
            )
            ->where('BLK.Id = ?', $blockId)
            ->where('ALT.Value <> ?', '.')
            ->order('BLK.Designation')
            ->order('CRT.Designation')
            ->order('QST.Designation')
            ->order('ALT.Designation');

        return $this->fetchAll($query);
    }
}