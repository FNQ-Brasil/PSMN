<?php
/**
 * 
 * Model_EnterpriseReport
 * @uses  
 * @author mcianci
 *
 */
class Model_EnterpriseReport
{

    public $dbTable_EnterpriseReport = "";
    
    function __construct() {
        $this->dbTable_EnterpriseReport = new DbTable_EnterpriseReport();
    }

    public function getAllEnterpriseReport($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_EnterpriseReport->fetchAll($where, $order, $count, $offset);
    }
 
    public function getEnterpriseReport($where = null)
    {
        return $this->dbTable_EnterpriseReport->fetchRow($where);
    }

    function getEnterpriseReportById($Id)
    {
        return $this->dbTable_EnterpriseReport->fetchRow(array('Id = ?' => $Id));
    }
    
    function getEnterpriseReportByEnterpriseId($enterpriseId)
    {
        return $this->dbTable_EnterpriseReport->fetchRow(array('EnterpriseId = ?' => $enterpriseId));
    }
    
    function getEnterpriseReportByEnterpriseIdKey($enterpriseIdKey, $competitionId = null)
    {
        if (!$competitionId) {
            $competitionId = Zend_Registry::get('configDb')->competitionId;
        }
        $modelEnterprise = new Model_Enterprise();
        $enterpriseId = $modelEnterprise->getEnterpriseByIdKey($enterpriseIdKey)->getId();
        return $this->dbTable_EnterpriseReport->fetchRow(array(
            'EnterpriseId = ?' => $enterpriseId, 'CompetitionId = ?' =>  $competitionId
        ));
    }
    /**
     * informa se a empresa preencheu o relato
     * 
     * @param type $enterpriseId
     * @return boolean
     */
    function getCurrentEnterpriseReportByEnterpriseId($enterpriseId)
    {
        $return = true;
        $report = $this->dbTable_EnterpriseReport->fetchRow(
            array(
                'EnterpriseId = ?' => $enterpriseId,
                'CompetitionId = ?' => Zend_Registry::get('configDb')->competitionId
            )
        );
        
        
        if (!$report) {
            $return = false;
        }    
        
        return $return;
    }
    
    /**
     * informa se a empresa preencheu o relato
     * 
     * @param type $enterpriseId
     * @return boolean
     */
    function getCurrentEnterpriseReportByEnterpriseIdKey($enterpriseIdKey,$programaId)
    {
        $modelEnterprise = new Model_Enterprise();
        $enterpriseId = $modelEnterprise->getEnterpriseByIdKey($enterpriseIdKey)->getId();
        $return = true;
        $report = $this->dbTable_EnterpriseReport->fetchRow(
            array(
                'EnterpriseId = ?' => $enterpriseId,
                'CompetitionId = ?' => $programaId // Zend_Registry::get('configDb')->competitionId
            )
        );
        if (!$report) {
            $return = false;
        }    
        return $return;
    }
    
    function getAllEnterpriseReportByEnterpriseId($enterpriseId)
    {
        return $this->dbTable_EnterpriseReport->fetchAll(array('EnterpriseId = ?' => $enterpriseId));
    }
    
    public function createReport($enterpriseReportData,$enterpriseId) 
    {
        $isValidData = $this->validData($enterpriseReportData);
        if (!$isValidData['status']) {
            return $isValidData;
        }
        
        $enterpriseReportData['enterprise_id'] = $enterpriseId;
        $enterpriseReportData = $this->_filterInputReport($enterpriseReportData)->getUnescaped();
        
        $enterpriseReportRow = DbTable_EnterpriseReport::getInstance()->createRow()
            ->setEnterpriseId($enterpriseId)
            ->setCompetitionId($enterpriseReportData['competition_id'])
            ->setReport($enterpriseReportData['report'])
            ->setTitle($enterpriseReportData['title'])
            ;
        $enterpriseReportRow->save();
        return array(
            'status' => true
        );
    }
    
    public function updateReport($reportRow,$reportRowData,$enterpriseId) 
    {
        
        $where = array(
            'EnterpriseId' => $enterpriseId,
            'CompetitionId' => $reportRowData['competition_id']
            );
        $isOwnerReport = $this->getEnterpriseReport($where);
        
        if (!$isOwnerReport) {
            Exception('invalid report.');
        }
        
        $isValidData = $this->validData($reportRowData);
        if (!$isValidData['status']) {
            return $isValidData;
        }
        
        $reportRowData = $this->_filterInputReport($reportRowData)->getUnescaped();
        
        $reportRow
            ->setCompetitionId(isset($reportRowData['competition_id'])? 
                $reportRowData['competition_id'] : $reportRow->getCompetitionId())
            ->setReport(isset($reportRowData['report'])? 
                $reportRowData['report'] : $reportRow->getReport())
            ->setTitle(isset($reportRowData['title'])? 
                $reportRowData['title'] : $reportRow->getTitle())
            ;
        $reportRow->save();
        
        return array(
            'status' => true
        );
    }
    
    protected function validData($reportData) 
    {
        $userRoleDescription = strtolower(Zend_Auth::getInstance()->getIdentity()->getRoleLongDescription());
        $validationRequired = !in_array($userRoleDescription, array('gestor','digitador'), true);

        if($validationRequired){
            $titulo = $reportData['title']; // str_replace(, "\n", '');
            $texto = str_replace("\n", ' ',$reportData['report']); // preg_replace(array('~[:;!?]|[.,](?![0-9])|\'s~', '~\s+~'), array('', ' '), $reportData['report']); //
            $palavras = trim($titulo).' '.trim($texto);
            $characters = (mb_strlen($titulo, "UTF-8") + mb_strlen($texto, "UTF-8"));
            $words  = Vtx_Util_Formatting::contadorPalavras($titulo) + Vtx_Util_Formatting::contadorPalavras($texto);
            //$characters = $words;
            //$words = count(explode(' ', trim($palavras)));
            //var_dump($palavras,$words,$characters);
            /*
            var_dump($words);
            die;
            um dois; três qu4tró% (çinço) çeis sete
            39
            / *
             * O Relato deverá ter de 60 linhas a 120 linhas,
                        de 500 a 1200 palavras e de 3.000 a 7.140 caracteres,
                        contando o Título.
            */
            if ( $characters < 2900 or $words < 480 or $words > 1300 ) {
                return array(
                    'status' => false,
                    'messageError' => 'O Relato deverá ter mais de 3.000 caracteres e ter de 500 a 1200 palavras,
                        contando o Título.'
                );
            }
            if ( $characters > 7240 or $words < 480 or $words > 1300 ) {
                return array(
                    'status' => false,
                    'messageError' => 'O Relato deverá ter menos de  7.140 caracteres e ter de 500 a 1200 palavras,
                        contando o Título.'
                );
            }
        }

        return array(
            'status' => true
        );
    }


    protected function _filterInputReport($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'report' => array(), 'title' => array()
            ),
            array( //validates
                'enterprise_id' => array('allowEmpty' => true),
                'competition_id' => array('allowEmpty' => true),
                'report' => array('allowEmpty' => true),
                'title' => array('allowEmpty' => true)
            ),
            $params
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }
}