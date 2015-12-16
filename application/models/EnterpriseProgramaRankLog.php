<?php
/**
 * 
 * Model_EnterpriseProgramaRankLog
 * @uses  
 * @author mcianci
 *
 */
class Model_EnterpriseProgramaRankLog
{

    public $dbTable_EnterpriseProgramaRankLog = "";
    
    function __construct() 
    {
        $this->dbTable_EnterpriseProgramaRankLog = new DbTable_EnterpriseProgramaRankLog();
    }

    public function createEnterpriseProgramaRankLog($data) 
    {
        $logRow = DbTable_EnterpriseProgramaRankLog::getInstance()->createRow()
            ->setEnterpriseProgramaRankId($data['enterprise_programa_rank_id'])
            ->setEnterpriseIdKey($data['enterprise_id_key'])
            ->setUserId($data['user_id'])
            ->setProgramaId($data['programa_id'])
            ->setClassificar($data['classificar'])
            ->setDesclassificar($data['desclassificar'])
            ->setClassificadoVerificacao($data['classificado_verificacao'])
            ->setDesclassificadoVerificacao($data['desclassificado_verificacao'])
            ->setMotivoDesclassificadoVerificacao($data['motivo_desclassificado_verificacao'])
        ;
        $logRow->save();
        return array(
            'status' => true
        );
    }

}