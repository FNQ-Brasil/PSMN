<?php
/**
 * 
 * Model_EnterpriseProgramaRank
 * @uses  
 * @author mcianci
 *
 */
class Model_EnterpriseProgramaRank
{

    public $dbTable_EnterpriseProgramaRank = "";
    
    function __construct() 
    {
        $this->dbTable_EnterpriseProgramaRank = new DbTable_EnterpriseProgramaRank();
    }

    public function getByIdKey($idKey,$programaId)
    {
        return $this->dbTable_EnterpriseProgramaRank->fetchRow(array('EnterpriseIdKey = ?' => $idKey,'ProgramaId = ?'=>$programaId));
    }
    
    public function doRank($data) 
    {
        
        if ($data["programa_id"] != Zend_Registry::get('configDb')->competitionId) {
            die;
        }
        
        $row = $this->getByIdKey($data['enterprise_id_key'],$data['programa_id']);
        if (!$row) {
            $obj = $this->createEnterpriseProgramaRank($data);
        } else {
            $obj = $this->updateEnterpriseProgramaRank($row,$data);
        }
        
        if (!$obj['status']) {
            return array(
                'status' => false,
                'messageError' => $obj['messageError']
            );            
        }
        
        return array(
            'status' => true
        );
    }
    
    public function createEnterpriseProgramaRank($data) 
    {
        $data = $this->_filterInputEnterpriseProgramaRank($data)->getUnescaped();
        $enterpriseProgramaRankRow = DbTable_EnterpriseProgramaRank::getInstance()->createRow()
            ->setEnterpriseIdKey($data['enterprise_id_key'])
            ->setUserId($data['user_id'])
            ->setProgramaId($data['programa_id'])
            ->setClassificar(isset($data['classificar'])? $data['classificar'] : '0')
            ->setDesclassificar(isset($data['desclassificar'])? $data['desclassificar'] : '0')
            ->setJustificativa(isset($data['justificativa'])? $data['justificativa'] : '')
            ;
        $enterpriseProgramaRankRow->save();
        $this->createEnterpriseProgramaRankLog($enterpriseProgramaRankRow);
        return array(
            'status' => true
        );
    }
    
    public function updateEnterpriseProgramaRank($row, $data) 
    {
        $data = $this->_filterInputEnterpriseProgramaRank($data)->getUnescaped();
        DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->beginTransaction();
        try {
            $row
                ->setClassificar(isset($data['classificar'])? 
                    $data['classificar'] : $row->getClassificar())
                
                ->setDesclassificar(isset($data['desclassificar'])? 
                    $data['desclassificar'] : $row->getDesclassificar())
                ->setJustificativa(isset($data['justificativa'])? 
                    $data['justificativa'] : $row->getJustificativa())
                
                ->setClassificadoVerificacao(isset($data['classificado_verificacao'])? 
                    $data['classificado_verificacao'] : $row->getClassificadoVerificacao())
                
                ->setDesclassificadoVerificacao(isset($data['desclassificado_verificacao'])? 
                    $data['desclassificado_verificacao'] : $row->getDesclassificadoVerificacao())
                ->setMotivoDesclassificadoVerificacao(isset($data['motivo_desclassificado_verificacao'])? 
                    $data['motivo_desclassificado_verificacao'] : $row->getMotivoDesclassificadoVerificacao()) 
                
                ->setClassificadoOuro(isset($data['classificado_ouro'])? 
                    $data['classificado_ouro'] : $row->getClassificadoOuro())
                ->setClassificadoPrata(isset($data['classificado_prata'])? 
                    $data['classificado_prata'] : $row->getClassificadoPrata())
                ->setClassificadoBronze(isset($data['classificado_bronze'])? 
                    $data['classificado_bronze'] : $row->getClassificadoBronze())
                
                ->setDesclassificadoFinal(isset($data['desclassificado_final'])? 
                    $data['desclassificado_final'] : $row->getDesclassificadoFinal())
                ->setMotivoDesclassificadoFinal(isset($data['motivo_desclassificado_final'])? 
                    $data['motivo_desclassificado_final'] : $row->getMotivoDesclassificadoFinal()) 
                
                ->setClassificarNacional(isset($data['classificar_nacional'])? 
                    $data['classificar_nacional'] : $row->getClassificarNacional())

                ->setDesclassificarNacional(isset($data['desclassificar_nacional'])? 
                    $data['desclassificar_nacional'] : $row->getDesclassificarNacional())
                ->setMotivoDesclassificadoNacional(isset($data['motivo_desclassificado_nacional'])? 
                    $data['motivo_desclassificado_nacional'] : $row->getMotivoDesclassificadoNacional()) 
            
                ->setClassificarFase2Nacional(isset($data['classificar_fase2_nacional'])? 
                    $data['classificar_fase2_nacional'] : $row->getClassificarFase2Nacional())
                ->setDesclassificarFase2Nacional(isset($data['desclassificar_fase2_nacional'])? 
                    $data['desclassificar_fase2_nacional'] : $row->getDesclassificarFase2Nacional())
                ->setMotivoDesclassificadoFase2Nacional(isset($data['motivo_desclassificado_fase2_nacional'])? 
                    $data['motivo_desclassificado_fase2_nacional'] : $row->getMotivoDesclassificadoFase2Nacional())

                ->setClassificadoOuroNacional(isset($data['classificado_ouro_nacional'])?
                    $data['classificado_ouro_nacional'] : $row->getClassificadoOuroNacional())
                ->setClassificadoPrataNacional(isset($data['classificado_prata_nacional'])?
                    $data['classificado_prata_nacional'] : $row->getClassificadoPrataNacional())
                ->setClassificadoBronzeNacional(isset($data['classificado_bronze_nacional'])?
                    $data['classificado_bronze_nacional'] : $row->getClassificadoBronzeNacional())
                
                ->setClassificarFase3Nacional(isset($data['classificar_fase3_nacional'])? 
                    $data['classificar_fase3_nacional'] : $row->getClassificarFase3Nacional())

                ->setDesclassificarFase3Nacional(isset($data['desclassificar_fase3_nacional'])? 
                    $data['desclassificar_fase3_nacional'] : $row->getDesclassificarFase3Nacional())
                ->setMotivoDesclassificadoFase3Nacional(isset($data['motivo_desclassificado_fase3_nacional'])? 
                    $data['motivo_desclassificado_fase3_nacional'] : $row->getMotivoDesclassificadoFase3Nacional())
            ;
            $row->save();
            $this->createEnterpriseProgramaRankLog($row);
            DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->commit();
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    protected function createEnterpriseProgramaRankLog($rowRank) 
    {
        $enterpriseProgramaRankLog = new Model_EnterpriseProgramaRankLog();
        $enterpriseProgramaRankLog->createEnterpriseProgramaRankLog(
            array(
                'enterprise_programa_rank_id' => $rowRank->getId(),
                'enterprise_id_key' => $rowRank->getEnterpriseIdKey(),
                'user_id' => $rowRank->getUserId(),
                'programa_id' => $rowRank->getProgramaId(),
                'classificar' => $rowRank->getClassificar(),
                'desclassificar' => $rowRank->getDesclassificar(),
                'classificado_verificacao' => $rowRank->getClassificadoVerificacao(),
                'desclassificado_verificacao' => $rowRank->getDesclassificadoVerificacao(),
                'motivo_desclassificado_verificacao' => $rowRank->getMotivoDesclassificadoVerificacao()
            )
        );
    }

    protected function _filterInputEnterpriseProgramaRank($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
            ),
            array( //validates
                'enterprise_id_key' => array('allowEmpty' => true),
                'user_id' => array('allowEmpty' => true),
                'programa_id' => array('allowEmpty' => true),
                'classificar' => array('allowEmpty' => true),
                'desclassificar' => array('allowEmpty' => true),
                'justificativa' => array('allowEmpty' => true),
                'classificado_verificacao' => array('allowEmpty' => true),
                'desclassificado_verificacao' => array('allowEmpty' => true),
                'motivo_desclassificado_verificacao' => array('allowEmpty' => true),
                'classificado_ouro' => array('allowEmpty' => true),
                'classificado_prata' => array('allowEmpty' => true),
                'classificado_bronze' => array('allowEmpty' => true),
                'desclassificado_final' => array('allowEmpty' => true),
                'motivo_desclassificado_final' => array('allowEmpty' => true),
                
                'classificar_nacional' => array('allowEmpty' => true),
                'desclassificar_nacional' => array('allowEmpty' => true),
                'motivo_desclassificado_nacional' => array('allowEmpty' => true),
                
                'classificar_fase2_nacional' => array('allowEmpty' => true),
                'desclassificar_fase2_nacional' => array('allowEmpty' => true),
                'motivo_desclassificado_fase2_nacional' => array('allowEmpty' => true),

                'classificado_ouro_nacional' => array('allowEmpty' => true),
                'classificado_prata_nacional' => array('allowEmpty' => true),
                'classificado_bronze_nacional' => array('allowEmpty' => true),
                
                'classificar_fase3_nacional' => array('allowEmpty' => true),
                'desclassificar_fase3_nacional' => array('allowEmpty' => true),
                'motivo_desclassificado_fase3_nacional' => array('allowEmpty' => true),
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

    public function deleteEnterpriseProgramaRank($enterpriseProgramaRankRow)
    {   
        DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->beginTransaction();
        try {
            
            $enterpriseProgramaRankRow->delete();
            DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->commit();
            
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_EnterpriseProgramaRank::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    
}