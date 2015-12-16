<?php

class DbTable_Criterion extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Criterion';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Block' => array(
            'columns' => 'BlockId',
            'refTableClass' => 'Block',
            'refColumns' => 'Id'
        ),
        'Question' => array(
            'columns' => 'QuestionId',
            'refTableClass' => 'Question',
            'refColumns' => 'Id'
        )
    );
    
    protected $_dependentTables = array(
        'DbTable_Question'
    );
    
    public function getHigherOrder($blockId){
        $query = $this->select()
            ->from(
                array('c' => 'Criterion'),
                array('HigherOrder' => 'MAX(c.Designation)')
            )
            ->where('c.BlockId = ?', $blockId);
        $objResultCriterion = $this->fetchRow($query);
		return $objResultCriterion->getHigherOrder();
	}

	public function getSmallerOrder($blockId){
        $query = $this->select()
            ->from(
                array('c' => 'Criterion'),
                array('SmallerOrder' => 'MIN(c.Designation)')
            )
            ->where('c.BlockId = ?', $blockId);
        $objResultCriterion = $this->fetchRow($query);
		return $objResultCriterion->getSmallerOrder();
	}

	static public function reorder($block_id, $fromId = null, $fromOldPosition = null, $old_position_id = null)
	{
        if (!is_numeric($block_id)) {
            return false;
        }
        $modelCriterion = new Model_Criterion();
        $orderBy = 0;
        $where = array('BlockId = ?' => $block_id);
        $order = array('Designation ASC');
        $criterions = $modelCriterion->getAll($where, $order); //$newPositionRow = $modelCriterion->getCriterionByPosition($new_position, $block_id); //$newPositionRowId = $newPositionRow->getId()?$newPositionRow->getId():null; //$newPositionRowId = $new_position_id;
        $newPositionRowId = null;
        if ( $fromId ) {
            $newPositionRow = $modelCriterion->getCriterionById($fromId);
            $newPositionRowId = $newPositionRow->getId()?$newPositionRow->getId():null;            
        }
        if ( $fromOldPosition ) {
            $fromOldPosition = $fromOldPosition?$fromOldPosition:null;
        }
        $toPositionRowId = null;
        if ( $old_position_id ) {
            $toPositionRow = $modelCriterion->getCriterionById($old_position_id);
            $toPositionRowId = $toPositionRow->getId()?$toPositionRow->getId():null;
            $toNewPosition = $toPositionRow->getDesignation()?$toPositionRow->getDesignation():null;     
        }
        if ( !is_object($criterions) || !(count($criterions) > 0)) {
            return false;
        }
        foreach ( $criterions as $criterionRow )
        {
            $orderBy++;
            if ( $criterionRow->getId() == $newPositionRowId ) { // não editar $newPositionRowId - RESOLVIDO //var_dump('_________________newPositionRowId___'); var_dump($newPositionRowId); var_dump('____________________');
                continue;
            }
            $data['block_id']          = $block_id;
            $data['value']             = $criterionRow->getValue();
            $data['long_description']  = $criterionRow->getLongDescription();
            $data['conclusion_text']   = $criterionRow->getConclusionText();
            if ( $criterionRow->getId() == $toPositionRowId ) {
                $criterionRow->setId($toPositionRowId);
                if ( $fromOldPosition <= $toNewPosition ) { 
                    $data['designation'] = $toNewPosition - 1;
                } else {
                    $data['designation'] = $toNewPosition + 1;
                } #var_dump('---------------'); var_dump($data['designation'].' - '.$toNewPosition); var_dump('---------------');
            } else {
                $data['designation'] = $orderBy;
            }
            $modelCriterion->updateCriterion($criterionRow,$data); #var_dump('*********'); var_dump($orderBy);
        }
        return true;
	}

	public function getIdDesignation($blockId){
        $query = $this->select()
            ->from(
                array('c' => 'Criterion'),
                array('Id', 'Designation')
            )
            ->where('c.BlockId = ?', $blockId);
        $objResultCriterion = $this->fetchAll($query);
		return $objResultCriterion;
	}

}
