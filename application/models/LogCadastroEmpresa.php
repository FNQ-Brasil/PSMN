<?php
/**
 * 
 * Model_LogCadastroEmpresa
 * @uses  
 * @author mcianci
 *
 */
class Model_LogCadastroEmpresa
{

    public $dbTable_LogCadastroEmpresa = "";
    
    function __construct() {
        $this->dbTable_LogCadastroEmpresa = new DbTable_LogCadastroEmpresa();
    }

    public function getLogCadastroEmpresaByEnterpriseId($enterpriseId)
    {
        return $this->dbTable_LogCadastroEmpresa->fetchRow(array('EnterpriseId = ?' => $enterpriseId));
    }

    public function createLogCadastroEmpresa($data)
    {
        $data = $this->_filterInputLogCadastroEmpresa($data)->getUnescaped();
        $rowData = DbTable_LogCadastroEmpresa::getInstance()->createRow()
            ->setUserIdLog($data['user_id_log'])
            ->setEnterpriseId($data['enterprise_id'])
            ->setProgramaId($data['programa_id'])
            ->setAcao($data['acao'])
            ;
        $rowData->save();
        return array(
            'status' => true,
            'lastInsertId' => $rowData->getId()
        );
    }
    
    public function createLogDevolutiva($userIdLogged, $enterpriseId)
    {
        $programaId = Zend_Registry::get('configDb')->competitionId;
        $logDevolutivaExistente = DbTable_LogCadastroEmpresa::getInstance()->fetchRow(
            array(
                'EnterpriseId = ?' => $enterpriseId,
                'ProgramaId = ?' => $programaId,
                'Acao = ?' => 'devolutiva'
            )
        );

        $log = array(
            'user_id_log' => $userIdLogged,
            'enterprise_id' => $enterpriseId,
            'programa_id' => $programaId,
            'acao' => $logDevolutivaExistente? 'devolutiva-regerada' : 'devolutiva'
        );
        return $this->createLogCadastroEmpresa($log);
    }

    protected function _filterInputLogCadastroEmpresa($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
            ),
            array( //validates
                'user_id_log' => array(
                    'NotEmpty',
                    'presence' => 'required'
                ),
                'enterprise_id' => array(
                    'NotEmpty',
                    'messages' => array('Erro ao cadastrar empresa.'),
                    'presence' => 'required'
                ),
                'programa_id' => array(
                    'NotEmpty',
                    'messages' => array('Erro ao cadastrar empresa.'),
                    'presence' => 'required'
                ),
                'acao' => array(
                    'NotEmpty',
                    'messages' => array('Erro ao cadastrar empresa.'),
                    'presence' => 'required'
                )
            ),
            $params,
            array()
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }

    
}   