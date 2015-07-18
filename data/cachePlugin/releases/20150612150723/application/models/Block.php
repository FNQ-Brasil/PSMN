<?php
/**
 * 
 * Model_Block
 * @uses  
 *
 */
class Model_Block
{

    public $tbBlock = "";
    
    function __construct() {
        $this->tbBlock = new DbTable_Block();
    }

    public function createBlock($data)
    {
        DbTable_Block::getInstance()->getAdapter()->beginTransaction();
        try {
            $data = $this->_filterInputBlock($data)->getUnescaped();

            $verifyBlock = DbTable_Block::getInstance()->fetchRow(array(
                'QuestionnaireId = ?' => $data['questionnaire_id'],
                'Value = ?' => $data['value']
            ));

            if ($verifyBlock) {
                return array(
                    'status' => false, 
                    'messageError' => 'Nome ('.$data['value'].') já em uso neste questionário.'
                        );
            }
            $blockRowData = DbTable_Block::getInstance()->createRow()
                ->setQuestionnaireId($data['questionnaire_id'])
                ->setDesignation((DbTable_Block::getInstance()
                ->getHigherOrder($data['questionnaire_id']) + 1))
                ->setValue($data['value'])
                ->setLongDescription($data['long_description'])
                ->setConclusionText($data['conclusion_text']);
            $blockRowData->save();
            DbTable_Block::getInstance()->getAdapter()->commit();
            return array(
                'status' => true,
                'lastInsertId' => $blockRowData->getId(),
                'lastDesignation' => $blockRowData->getDesignation()
            );
        } catch (Vtx_UserException $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function updateBlock($blockRowData, $data)
    {
        DbTable_Block::getInstance()->getAdapter()->beginTransaction();
        try {
            $data = $this->_filterInputBlock($data)->getUnescaped();
            $blockRowData
                ->setQuestionnaireId($data['questionnaire_id'])
                ->setDesignation($data['designation'])
                ->setValue($data['value'])
                ->setLongDescription($data['long_description'])
                ->setConclusionText($data['conclusion_text']);
            $blockRowData->save();
            DbTable_Block::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    protected function _filterInputBlock($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'value' => array(
                    array('Alnum', 
                        array('allowwhitespace' => true)
                        )
                ),
                'long_description' => array(),
                'conclusion_text' => array(
                    array('Alnum',
                        array('allowwhitespace' => true)
                        )
                )
            ),
            array( //validates
                'questionnaire_id' => array('NotEmpty'),
                'designation' => array('allowEmpty' => true),
                'value' => array('NotEmpty', 'messages' => array('O nome do bloco não pode ser vazio.')),
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
    
    public function getDbTable()
    {
        return DbTable_Block::getInstance();
    }

    public function deleteBlock($blockRow)
    {   
        DbTable_Block::getInstance()->getAdapter()->beginTransaction();
        try {
            $questionnaireId = $blockRow->getQuestionnaireId();
            $query = DbTable_Criterion::getInstance()->select()
                ->from(
                    array('c' => 'Criterion'),
                    array('Block' => 'c.BlockId')
                )
                ->where('c.BlockId = ?', $blockRow->getId());
            $objResultCriterion = DbTable_Criterion::getInstance()->fetchRow($query);
            if (!$objResultCriterion) {
                $blockRow->delete();
                DbTable_Block::getInstance()->getAdapter()->commit();
                DbTable_Block::getInstance()->reorder($questionnaireId);
            } else {
                return array(
                    'status' => false,
                    'messageError' => 'Há critérios para este bloco.'
                );
            }
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 
                'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getBlockById($Id)
    {
        return $this->tbBlock->fetchRow(array('Id = ?' => $Id));
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->tbBlock->fetchAll($where, $order, $count, $offset);
    }
    
    public function getAllByQuestionnaireId($questionnaireId)
    {
        $where = null;
        $order = array('Designation ASC');
        if ($questionnaireId) {
            $where = array('QuestionnaireId = ?' => $questionnaireId);
        }
        return $this->getAll($where,$order);
    }
    public function moveBlock($fromId, $toId)
    {
        DbTable_Block::getInstance()->getAdapter()->beginTransaction();
        try {
            $blockNewPositionRowData = $this->getBlockById($toId);
            
            $fromOldPosition    = $this->getPositionByBlockId($fromId);
            $newDesignation     = $blockNewPositionRowData->getDesignation();
            $questionnaireId    = $blockNewPositionRowData->getQuestionnaireId();
            
            $blockOldPositionRowData = $this->getBlockById($fromId);
            $blockOldPositionRowData->setDesignation($newDesignation);
            $blockOldPositionRowData->save();

            DbTable_Block::getInstance()->getAdapter()->commit();
            
            DbTable_Block::getInstance()->reorder($questionnaireId, $fromId, $fromOldPosition, $toId);
            
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Block::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

	static public function getPositionByBlockId($blockId){
        if ( !is_numeric($blockId) ) {
            return false;
        }
        $query = DbTable_Block::getInstance()->select()
            ->from(
                array('b' => 'Block'),
                array('Position' => 'b.Designation')
            )
            ->where('b.Id = ?', $blockId);
        $objResultBlock = DbTable_Block::getInstance()->fetchRow($query);
        if ($objResultBlock) {
            return $objResultBlock->getPosition();
        }
	}
    
	public function getBlockByPosition($designation, $questionnaireId){
        if ( !is_numeric($designation) || !is_numeric($questionnaireId) ){
            return false;
        }
        $select = DbTable_Block::getInstance()->select()
            ->from(
                array('b' => 'Block'),
                array('*')
            )
            ->where('b.Designation = ?', $designation)
            ->where('b.QuestionnaireId = ?', $questionnaireId);
        $objResultBlock = DbTable_Block::getInstance()->fetchRow($select);
        if ($objResultBlock) {
            return $objResultBlock;            
        }
    }
    
    
    
    public function getQuestionsByBlockIdForView($blockId)
    {
        $questions = DbTable_Block::getInstance()->getQuestionsByBlockId($blockId);
        $lastBlockId = $lastQuestionId = $lastAlternativeId = null;
        $questionsArray = array();

        foreach ($questions as $k => $question) {
            $questionId = $question->getQuestionId();
            $blockId = $question->getBlockId();
            $alternativeId = $question->getAlternativeId();

            if ($lastQuestionId != $questionId) {
                $questionSummary = $question->getSummary();
                $questionType = $question->getQuestionTypeId();
                $supportingText = $question->getSupportingText();
                $showEnterpriseFeedback = ($supportingText
                    and $questionType == Model_QuestionType::AGREEDISAGREE_ID)? true : false;
               $questionsArray[$questionId] = array(
                    'QuestionId' => $questionId,
                    'QuestionSummary' => $questionSummary,
                    'QuestionValue' => $question->getQuestionValue(),
                    'SupportingText' => $supportingText,
                    'QuestionTypeId' => $questionType,
                    'ShowEnterpriseFeedback' => $showEnterpriseFeedback,
                    'Alternatives' => array()
                );
            }
            
            if ($lastAlternativeId != $alternativeId) {
                $questionsArray[$questionId]['Alternatives'][$alternativeId] = array(
                    'AlternativeId' => $alternativeId,
                    'AlternativeValue' => $question->getAlternativeValue()
                );
            }

            $lastBlockId = $blockId;
            $lastQuestionId = $questionId;
            $lastAlternativeId = $alternativeId;
        }

        return $questionsArray;
    }
    
    
     /**
     * recupera ou grava Alternativas de um questao no Cache
     * 
     * @param type $questionId
     * @return fetchall
     */
    public function cacheOrModelBlockById($blockId)
    {
        $this->cache = Zend_Registry::get('cache_FS');
  
        $nameCache = 'block_'.$blockId;
        
        $cache = $this->cache->load($nameCache);
        
        $origem = "--->vem do cache---";
        
        //recupera do cache
        if ($cache == false) 
        {               
            $cache = $this->getQuestionsByBlockIdForView($blockId);
            $this->cache->save($cache, $nameCache);
            
            $origem = "--->NAO vem do cache---";
        }
        
        //echo $origem;
        
        return $cache;
        
    } //end function     
    
    
}