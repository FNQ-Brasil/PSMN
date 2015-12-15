<?php
/**
 * 
 * Model_AnswerHistory
 *
 */
class Model_Configuration
{
    function getAll()
    {
        return DbTable_Configuration::getInstance()->fetchAll();
    }

    function getConfigurationById($Id)
    {
        return DbTable_Configuration::getInstance()->fetchRow(array('Id = ?' => $Id));
    }

    function getConfigurationByConfKey($confKey)
    {
        return DbTable_Configuration::getInstance()->fetchRow(array('ConfKey = ?' => $confKey));
    }
    
    public function createConfig($data)
    {
        $data = $this->_filterInputConfiguration($data)->getUnescaped();
        $configurationRow = DbTable_Configuration::getInstance()->createRow()
            ->setConfKey($data['conf_key'])
            ->setConfValue($data['conf_value']);
        $configurationRow->save();
        return array(
            'status' => true
        );
    }
    
    public function updateConfig($configurationRow, $value)
    {
        //$data = $this->_filterInputConfiguration($data)->getUnescaped();
        $configurationRow
            //->setConfValue(isset($data['conf_value'])? $data['conf_value'] : $configurationRow->getConfValue());
            ->setConfValue(isset($value)? $value : $configurationRow->getConfValue());
        $configurationRow->save();
        return array(
            'status' => true
        );
    }

    protected function _filterInputConfiguration($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
            ),
            array( //validates
                //'conf_value' => array('NotEmpty')
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

    public function deleteConfiguration($configurationRow)
    {   
        DbTable_Configuration::getInstance()->getAdapter()->beginTransaction();
        try {
            $configurationRow->delete();
            DbTable_Configuration::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Configuration::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Configuration::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }

    }
    /**
     * Entenda CompetitionId como ProgramaId (19/06/2013).
     * 
     * 
     * 
     * @return \Zend_Config
     * 
     * 
     */
    public function getSescoopConfiguration()
    {
        $cache = Zend_Registry::get('cache_FS');
        $nameCache = 'configuration';
        $dataCached = $cache->load($nameCache);

        if (!$dataCached) {               
            $dataCached = $this->getAll();
            $cache->save($dataCached, $nameCache);
        }

        $objConfiguration = $dataCached;
        $conf = array();

        foreach ($objConfiguration AS $configuration) {
            switch ($configuration['ConfKey']) {
                case 'competitionIdKey':
                                       
                case 'qstn.currentAutoavaliacaoId':
                case 'qstn.currentBlockIdEmpreendedorismo':
                case 'qstn.currentBlockIdNegocios':
                    
                case 'qstn.currentPremioId':
                    
                case 'addr.sescoopContactEmail':
                case 'addr.eligibilityGestorEmail':
                    $conf[$configuration['ConfKey']] = $configuration['ConfValue'];
                    break;
            }
        }

        $configDb = new Zend_Config(array(), true);
        $configDb->competitionId = $conf['competitionIdKey'];
        
        $configDb->qstn = array();
        
        $configDb->qstn->currentAutoavaliacaoId = $conf['qstn.currentAutoavaliacaoId'];
        $configDb->qstn->currentBlockIdEmpreendedorismo = $conf['qstn.currentBlockIdEmpreendedorismo'];
        $configDb->qstn->currentBlockIdNegocios = $conf['qstn.currentBlockIdNegocios'];
        $configDb->qstn->currentPremioId = $conf['qstn.currentPremioId'];
        
        $configDb->addr = array();
        $configDb->addr->sescoopContactEmail = $conf['addr.sescoopContactEmail'];
        $configDb->addr->eligibilityGestorEmail = $conf['addr.eligibilityGestorEmail'];
        
        return $configDb;
    }
}