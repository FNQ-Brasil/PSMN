<?php
/**
 * 
 * Model_Criterion
 * @uses  
 * @author mcianci
 *
 */
class Model_Criterion
{
    public $tbCriterion = "";

    public function __construct() {
        $this->tbCriterion = new DbTable_Criterion();
    }

    public function createCriterion($data)
    {
        DbTable_Criterion::getInstance()->getAdapter()->beginTransaction();
        try {
            $data = $this->_filterInputCriterion($data)->getUnescaped();

            $verifyCriterion = DbTable_Criterion::getInstance()->fetchRow(array(
                'BlockId = ?' => $data['block_id'],
                'Value = ?' => $data['value']
            ));

            if ($verifyCriterion) {
                return array(
                    'status' => false, 
                    'messageError' => 'Nome ('.$data['value'].') já em uso neste Bloco.'
                        );
            }

            $criterionRowData = DbTable_Criterion::getInstance()->createRow()
                ->setBlockId($data['block_id'])
                ->setDesignation((DbTable_Criterion::getInstance()
                    ->getHigherOrder($data['block_id']) + 1))
                ->setValue($data['value'])
                ->setLongDescription($data['long_description'])
                ->setConclusionText($data['conclusion_text']);
            $criterionRowData->save();
            DbTable_Criterion::getInstance()->getAdapter()->commit();
            return array(
                'status' => true,
                'lastDesignation' => $criterionRowData->getDesignation(),
                'lastInsertId' => $criterionRowData->getId()
            );
        } catch (Vtx_UserException $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function updateCriterion($criterionRowData, $data)
    {
        DbTable_Criterion::getInstance()->getAdapter()->beginTransaction();
        try {
            $data = $this->_filterInputCriterion($data)->getUnescaped();
            $criterionRowData->setBlockId($data['block_id'])
                ->setDesignation($data['designation'])
                ->setValue($data['value'])
                ->setLongDescription($data['long_description'])
                ->setConclusionText($data['conclusion_text']);
            $criterionRowData->save();
            DbTable_Criterion::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    protected function _filterInputCriterion($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'value' => array(
                    array('Alnum', array('allowwhitespace' => true))
                ),
                'long_description' => array(),
                'conclusion_text' => array(
                    array('Alnum', array('allowwhitespace' => true))
                )
            ),
            array( //validates
                'block_id' => array('NotEmpty'),
                'designation' => array('allowEmpty' => true),
                'value' => array('allowEmpty' => true),
                'long_description' => array('allowEmpty' => true),
                'conclusion_text' => array('allowEmpty' => true),
            ),
            $params,
            array('presence' => 'required')
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }

    public function deleteCriterion($criterionRow)
    {   
        DbTable_Criterion::getInstance()->getAdapter()->beginTransaction();
        try {
            $blockId = $criterionRow->getBlockId();
            
            
            $query = DbTable_Question::getInstance()->select()
                ->from(
                    array('q' => 'Question'),
                    array('Criterion' => 'q.CriterionId')
                )
                ->where('q.CriterionId = ?', $criterionRow->getId());
            $objResultQuestion = DbTable_Question::getInstance()->fetchRow($query);
            

            if (!$objResultQuestion) {
                
                $criterionRow->delete();
                #var_dump($objResultQuestion); die($blockId);
                
                
                DbTable_Criterion::getInstance()->getAdapter()->commit();
                DbTable_Criterion::getInstance()->reorder($blockId);
            } else {
                return array(
                    'status' => false,
                    'messageError' => 'Há questões para este Critério.'
                );
            }

            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getCriterionById($Id)
    {
        return $this->tbCriterion->fetchRow(array('Id = ?' => $Id));
    }

    function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->tbCriterion->fetchAll($where, $order, $count, $offset);
    }

    public function getAllByBlockId($blockId)
    {
        $where = null;
        $order = array('Designation ASC');
        if ($blockId) {
            $where = array('BlockId = ?' => $blockId);
        }
        return $this->getAll($where, $order);
    }

    public function moveCriterion($from, $to)
    {
        DbTable_Criterion::getInstance()->getAdapter()->beginTransaction();
        try {
            
            $criterionNewPositionRowData = $this->getCriterionById($to);
            
            $fromOldPosition = $this->getPositionByCriterionId($from);
            
            $newDesignation = $this->getPositionByCriterionId($criterionNewPositionRowData->getId());
            $blockId = $criterionNewPositionRowData->getBlockId();
            
            $criterionOldPositionRowData = $this->getCriterionById($from);
            $criterionOldPositionRowData->setDesignation($newDesignation);
            $criterionOldPositionRowData->save();

            DbTable_Criterion::getInstance()->getAdapter()->commit();
            
            DbTable_Criterion::getInstance()->reorder($blockId, $from, $fromOldPosition, $to);
            
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Criterion::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

	static public function getPositionByCriterionId($criterionId){
        if(is_numeric($criterionId)){
            $query = DbTable_Criterion::getInstance()->select()
                ->from(
                    array('c' => 'Criterion'),
                    array('Position' => 'c.Designation')
                )
                ->where('c.Id = ?', $criterionId);
            $objResultCriterion = DbTable_Criterion::getInstance()->fetchRow($query);
            if ($objResultCriterion) {
                return $objResultCriterion->getPosition();
            }
        }
        return false;
	}

	static public function getCriterionByPosition($designation, $blockId){
        if ( !is_numeric($designation) && !is_numeric($blockId) ){
            return false;
        }
        $select = DbTable_Criterion::getInstance()->select()
            ->from(
                array('c' => 'Criterion')
                ,array('*')
            )
            ->where('c.Designation = ?', $designation)
            ->where('c.BlockId = ?', $blockId);
        $objResultCriterion = DbTable_Criterion::getInstance()->fetchRow($select);
        if ($objResultCriterion) {
            return $objResultCriterion;            
        }
        
        return false;
	}

}