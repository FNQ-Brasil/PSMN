<?php
/**
 * 
 * Model_Execution
 * @uses  
 * @author mcianci
 *
 */
class Model_Execution
{

    private $tbUser = "";
    private $tbExecution = "";
    private $userSalt = "";
    
    
    function __construct() {
        $this->tbExecution = new DbTable_Execution();
    }

    public function getAll()
    {
        return DbTable_Execution::getInstance()->fetchAll();
    }

    public function getExecutionById($Id)
    {
        $objResultExecution = DbTable_Execution::getInstance()->fetchRow(array('Id = ?' => $Id));
        return $objResultExecution;
    }
    /**
     * 
     * Na estrutura da tabela Execution do PSMN nao tem a coluna ProgramaId. 
     * Esta coluna existe na tabela Execution do MPE e SESCOOP.
     * 
     * @param type $userId
     * @param type $programaId
     * @return type
     * 
     */
    public function getExecutionByUserAndPrograma($userId, $programaId)
    {
        
        /**
         * coluna ProgramaId comentado
         */
        $objResultExecution = $this->tbExecution->fetchRow(
            array(
                'UserId = ?' => $userId,
                'ProgramaId = ?' => $programaId
                )
        );
        return $objResultExecution;
    }    
    
    public function getExecutionByUserAndQuestionnaire($questionnaireId, $userId)
    {
        $objResultExecution = DbTable_Execution::getInstance()->fetchRow(array('UserId = ?' => $userId, 'QuestionnaireId = ?' => $questionnaireId));
        return $objResultExecution;
    }
    
    public function initExecution($questionnaireId, $userId)
    {
        $execution = $this->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);
        $programaId = Zend_Registry::get('configDb')->competitionId;
                
        if(!$execution) {
            $executionNew = DbTable_Execution::getInstance()->createRow()
                ->setUserId($userId)
                ->setQuestionnaireId($questionnaireId)
                ->setProgramaId($programaId)
                ->setStart(date('Y-m-d H:i:s'))
                ->setStatus('E');
            $executionNew->save();
        }
        return true;
    }
    
    /**
     * 
     * Faz controle de utilizacao do questionario.
     * (start, finish, DevolutivePath, EvaluationPath,FinalScore, AppraiserId, Progress).
     * Tabela Execution do banco dados.
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @param type $archivePath
     * @param type $finalScore
     * @param type $xxx
     * @return boolean
     */
    public function finishExecution($questionnaireId, $userId, $archivePath, $finalScore = null, $xxx = false)
    {
        //recupera execution do questionario
        $execution = $this->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);
        
        $devolutivePath = $execution->getDevolutivePath();
        $evaluationPath = $execution->getEvaluationPath();
        
        if($execution) {
            //grava finish date
            $execution 
                ->setFinish(date('Y-m-d H:i:s'))
                ->setStatus('F');
            
            if ($archivePath) {
                $execution->setDevolutivePath($archivePath);
            }

            if($finalScore != null) {
                $execution->setFinalScore($finalScore);
            }
            
            $execution->save();
        
        } else {
            return false;
        }
        
        return true;
    }
    
    public function cleanDevolutive($questionnaireId, $userId, $isRA = false)
    {
        $execution = $this->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);
        
        if ($execution and ($execution->getDevolutivePath() || $execution->getEvaluationPath())) {
                        
            if (!$isRA) {
                $arqPath = $execution->getDevolutivePath();
                $execution->setDevolutivePath(null);
            } else {
                $arqPath = $execution->getEvaluationPath();
                $execution->setEvaluationPath(null);
            }
            
            $directory = Zend_Registry::get('config')->paths->public;
            
            if(is_file($directory.$arqPath)) {
                unlink($directory.$arqPath);
            }
            
            //if(is_dir($directory)) {
            //    rmdir($directory);
            //}
            
            $execution->setFinish(null)
                ->setStatus('E')
                ->setFinalScore(null);
            $execution->save();
        }
        
        return true;
    }
    
    public function getStatus($questionnaireId, $userId) 
    {
        $execution = $this->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);        
    
        return $execution->getStatus();
    }
    
    public function getDevolutivePath($questionnaireId, $userId) 
    {
        $execution = $this->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);        
        if($execution) {
            return $execution->getDevolutivePath();
        } else {
            return false;
        }
    }
    
    public function getEvaluationPath($questionnaireId, $userId) 
    {
        $execution = $this->getExecutionByUserAndQuestionnaire($questionnaireId, $userId);        
        if($execution) {
            return $execution->getEvaluationPath();
        } else {
            return false;
        }
    }
}