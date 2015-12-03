<?php
/**
 * 
 * Model_ExecutionPontuacao
 *
 */
class Model_ExecutionPontuacao
{

    /** @var DbTable_ExecutionPontuacao **/
    protected $dbTable;
    protected $ExecutionPontuacaoLog;


    public function __construct($start=true)
    {
        $this->dbTable = new DbTable_ExecutionPontuacao();
        $this->ExecutionPontuacaoLog = new Model_ExecutionPontuacaoLog();
    }

    public function createExecutionPontuacao($data, $blocoDoQuestionario)
    {        
        DbTable_ExecutionPontuacao::getInstance()->getAdapter()->beginTransaction();
        
        try {
            
            $eRow = DbTable_ExecutionPontuacao::getInstance()->createRow();
            $currentBlockIdNegocios = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
            switch ($blocoDoQuestionario) 
            {
                
                 //case Model_Devolutive::BLOCO_NEGOCIOS:
                 case $currentBlockIdNegocios:
                      $eRow 
                        ->setExecutionId($data['executionId'])
                        ->setNegociosTotal($data['negociosTotal'])    
                        ;
                      break;

            }
            $eRow->save();
            
            DbTable_ExecutionPontuacao::getInstance()->getAdapter()->commit();
            return array(
                'status' => true, 'row' => $eRow
            );
        } catch (Vtx_UserException $e) {
            DbTable_ExecutionPontuacao::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_ExecutionPontuacao::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
        
    }
    
    public function updateExecutionPontuacao($executionId, $data, $blocoDoQuestionario)
    {
        DbTable_ExecutionPontuacao::getInstance()->getAdapter()->beginTransaction();
        
        try {
            
            $eRow = $this->getRowByExecutionId($executionId);
                       
            $now = date('Y-m-d H:i:s');
            $currentBlockIdNegocios = Zend_Registry::get('configDb')->qstn->currentBlockIdNegocios;
            switch ($blocoDoQuestionario) 
            {
                 case $currentBlockIdNegocios: //Model_Devolutive::BLOCO_NEGOCIOS:
                     $this->ExecutionPontuacaoLog->createByExecutionPontuacao($eRow);

                      $eRow
                        ->setNegociosTotal($data['negociosTotal'])    
                        ->setUpdatedAt($now)
                        ; 
                      $eRow->save();
                  break;                         
                      
             } //fim switch
            
            DbTable_ExecutionPontuacao::getInstance()->getAdapter()->commit();
            return array(
                'status' => true, 'row' => $eRow
            );
        } catch (Vtx_UserException $e) {
            DbTable_ExecutionPontuacao::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_ExecutionPontuacao::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }

    }
    

    /**
     * retorna fetchRow
     * @param type $executionId
     * @return type
     */
    public function getRowByExecutionId($executionId=0)
    {       
        $objResult = $this->dbTable->fetchRow(array('ExecutionId = ?' => $executionId));
        return $objResult;
    }
    
    
    /**
     * funcao que grava pontuacao dos criterios para devolutiva
     * 
     * @param object $objDadosPontuacao
     */
    public function gravaPontuacaoParaBlocoGestaoEmpresa($objDadosPontuacao, $arrPuntuation)
    {
        $modelExecPontuacao = new Model_ExecutionPontuacao();
                
    }    
    
    
}