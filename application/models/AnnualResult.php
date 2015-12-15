<?php
/**
 * 
 * Model_AnnualResult
 * @uses  
 * @author mcianci
 *
 */
class Model_AnnualResult
{
    public static $MASK = array(
        'moeda' => 'R$ 9.999,99', 'numero' => '9.999.99',
        'percentual' => '9.999.99%', 'texto' => 'Texto'
    );
    
    public $dbTable_AnnualResult = "";
    
    public function __construct() {
        $this->dbTable_AnnualResult = new DbTable_AnnualResult();
    }
    
    public function createAnnualResultsByQuestion($data, $alternativeId)
    {
        // utilizar transaction externa.
        $data = $this->_filterInputAnnualResult($data)->getUnescaped();
        $annualResultRow = DbTable_AnnualResult::getInstance()->createRow()
            ->setQuestionId($data['question_id'])
            ->setAlternativeId($alternativeId)
            ->setMask($data['mask'])
            ->setValue($data['value']);
        $annualResultRow->save();

        // Insere Annual Result Data para esta alternativa;
        $modelAnnualResultData = new Model_AnnualResultData();
        $createAnnualResultData = $modelAnnualResultData
            ->createAnnualResultDataByAlternative($annualResultRow->getId(), $alternativeId);
        if (!$createAnnualResultData['status']) {
            throw new Vtx_UserException($createAnnualResultData['messageError']);
        }

        return array(
            'status' => true,
            'lastInsertId' => $annualResultRow->getId()
        );
    }

    public function updateAnnualResult($annualResultRow, $data)
    {
        // utilizar transaction externa.
        $data = $this->_filterInputAnnualResult($data)->getUnescaped();
        $annualResultRow
            ->setQuestionId(isset($data['question_id'])?
                    $data['question_id'] : $annualResultRow->getQuestionId()
                )
            ->setMask($data['mask'])
            ->setValue($data['value']);
        $annualResultRow->save();
        return array(
            'status' => true,
            'lastInsertId' => $annualResultRow->getId()
        );
    }
    
    public function updateAnnualResultsByQuestion($annualResultsData, $questionId) 
    {
        // utilizar transaction externa.
        $oldAnnualResults = $this->getByQuestionId($questionId);
        foreach ($oldAnnualResults as $key => $annualResult) {
            $annualResultArray['annualResultId'][$annualResult->getAlternativeId()] = $annualResult->getId();
            $annualResultArray['alternativeId'][$annualResult->getAlternativeId()] = $annualResult->getAlternativeId();
            $annualResult->setMask($annualResultsData['mask'][$key]);
            $annualResult->setValue($annualResultsData['value'][$key]);
            $annualResult->save();            
        }
        return array(
            'status' => true,
            'annualResult' => $annualResultArray
        );
    }
    
    protected function _filterInputAnnualResult($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StringTrim'), // 'StripTags', 
                'value' => array()
            ),
            array( //validates
                'question_id' => array(),
                'mask' => array('NotEmpty',
                    'messages' => array('Escolha a máscara do Resultado Anual.')
                    ),
                'value' => array(
                    'NotEmpty',
                    'messages' => array('Escolha o nome do Resultado Anual.')
                        ),
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

    public function deleteAnnualResult($annualResultRow)
    {   
        // utilizar transaction externa. (OFF)
        DbTable_AnnualResult::getInstance()->getAdapter()->beginTransaction();
        try {
            /* Deletar : 
             * 'AnnualResultData',
             */
            $whereDeleteAnnualResultData = array('AnnualResultId = ?' => $annualResultRow->getId());
            DbTable_AnnualResultData::getInstance()->delete($whereDeleteAnnualResultData);
            $annualResultRow->delete();
            DbTable_AnnualResult::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnnualResult::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnnualResult::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function deleteByAlternativeId($alternativeId)
    {   
        $annualResultRow = $this->getByAlternativeId($alternativeId);
        /* Deletar : 
         * 'AnnualResultData',
         */
        if (!$annualResultRow) {
            return;
        }
        $whereDeleteAnnualResultData = array('AnnualResultId = ?' => $annualResultRow->getId());
        DbTable_AnnualResultData::getInstance()->delete($whereDeleteAnnualResultData);

        $annualResultRow->delete();
        return array(
            'status' => true
        );
    }

    function getAll()
    {
        $tbAnnualResult = new DbTable_AnnualResult();
        return $tbAnnualResult->fetchAll();
    }

    function getAnnualResultById($Id)
    {
        $tbAnnualResult = new DbTable_AnnualResult();
        $objResultAnnualResult = $tbAnnualResult->fetchRow(array('Id = ?' => $Id));
        return $objResultAnnualResult;
    }
    
    function getAllByQuestionId($QuestionId) {
        return $this->dbTable_AnnualResult->fetchAll(array('QuestionId = ?' => $QuestionId));
    }
    
    function getByQuestionId($QuestionId) {
        return $this->dbTable_AnnualResult->fetchRow(array('QuestionId = ?' => $QuestionId));
    }
    
    function getByAlternativeId($alternativeId) {
        return $this->dbTable_AnnualResult->fetchRow(array('AlternativeId = ?' => $alternativeId));
    }
}