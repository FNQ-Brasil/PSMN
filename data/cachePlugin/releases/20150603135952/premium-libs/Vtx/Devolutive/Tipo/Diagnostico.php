<?php

/**
 * Classe responsavel pela regras de geração da devolutiva de diagnostico.
 * 
 *
 * refatorado de Model_Devolutive
 * @author esilva
 */
class Vtx_Devolutive_Tipo_Diagnostico 
{
    
    /** @var Model_Execution $execution **/
    protected $execution;
    
    /** @var Model_Eligibility $execution **/
    protected $eligibility;
    
    /** @var Model_Devolutive $devolutive **/
    protected $devolutive;
    
    
    public function __construct(Model_Devolutive $devolutive) 
    {    
        $this->execution = new Model_Execution();
        $this->eligibility = new Model_Eligibility();
        $this->devolutive = $devolutive;
    }
    
    /**
     * 
     * // case 1 = Tipo do questionario diagnostico.
     */
    public function initTipo()
    {
                
         $this->execution->finishExecution($this->devolutive->getQuestionnaireId(), 
                                                  $this->devolutive->getUserId(), 
                                                  $this->devolutive->getArqPath(), 
                                                  null, 
                                                  $this->devolutive->getIsRA()
                                  );
         $this->eligibility->doAutoavaliacaoEligibility($this->devolutive->getQuestionnaireId(), $this->devolutive->getUserId());
                
         $result = $this->devolutive->makePdfDevolutiveAllBlocks( $this->devolutive->getQuestionnaireId(), 
                                                             $this->devolutive->getUserId(), 
                                                             $this->devolutive->getDirName(), 
                                                             $this->devolutive->getPublicDir(), 
                                                             $this->devolutive->getArqName(), 
                                                             $this->devolutive->getIsRA()
                                 );                
     
         return $result;
    }
    
} //end class

?>
