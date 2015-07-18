<?php
/**
 * 
 * Model_AnnualResultData
 * @uses  
 * @author mcianci
 *
 */
class Model_AnnualResultData
{

    public $dbTable_AnnualResultData = "";
    
    public function __construct() {
        $this->dbTable_AnnualResultData = new DbTable_AnnualResultData();
    }

    public function createAnnualResultData($data)
    {
        DbTable_AnnualResultData::getInstance()->getAdapter()->beginTransaction();
        try {
            $data = $this->_filterInputAnnualResultData($data)->getUnescaped();
            $annualResultRowData = DbTable_AnnualResultData::getInstance()->createRow()
                ->setAnnualResultId($data['annual_result_id'])
                ->setYear($data['year'])
                ->setValue($data['value']);
            $annualResultRowData->save();
            DbTable_AnnualResultData::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnnualResultData::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnnualResultData::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    public function createAnnualResultDataByAlternative($annualResultId, $alternativeId)
    {
        // utilizar transaction externa.
        $currentYear = Zend_Registry::get('config')->util->currentYear;
        for ($i = 1; $i <= 3; $i++ ){
            $annualResultRowData = DbTable_AnnualResultData::getInstance()->createRow()
                ->setAnnualResultId($annualResultId)
                ->setAlternativeId($alternativeId)
                ->setYear($currentYear);
            $annualResultRowData->save();
            $currentYear -= 1;
        }
        return array(
            'status' => true
        );
    }

    protected function _filterInputAnnualResultData($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'value' => array(
                    array('Alnum', 
                        array('allowwhitespace' => true)
                        )
                ),
                'Year' => array(
                    array('Digits',
                        array('allowwhitespace' => true)
                        )
                )
            ),
            array( //validates
                'annual_result_id' => array('NotEmpty'),
                'year' => array('allowEmpty' => true),
                'value' => array('allowEmpty' => true),
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

    public function deleteAnnualResultData($annualResultRow)
    {   
        DbTable_AnnualResultData::getInstance()->getAdapter()->beginTransaction();
        try {
            $annualResultRow->delete();
            DbTable_AnnualResultData::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_AnnualResultData::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_AnnualResultData::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }

    function getAnnualResultDataById($Id)
    {
        return $this->dbTable_AnnualResultData->fetchRow(array('Id = ?' => $Id));
    }

    function getAll()
    {
        return $this->dbTable_AnnualResultData->fetchAll();
    }
    
    

    function getAllAnnualResultDataByAlternativeId($alternativeId)
    {
        return $this->dbTable_AnnualResultData->fetchAll(
            array('AlternativeId = ?' => $alternativeId),
            'Year ASC
'        );
    }

}