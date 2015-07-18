<?php

/**
 * Classe responsavel pela regras de geração da devolutiva de autoavaliacao.
 * 
 *
 * refatorado de Model_Devolutive
 * @author esilva
 */
class Vtx_Devolutive_Tipo_Autoavaliacao
{
    
    /** @var Model_Execution $execution **/
    protected $execution;
       
    /** @var Model_Devolutive $devolutive **/
    protected $devolutive;
    
    
    public function __construct(Model_Devolutive $devolutive) 
    {    
        $this->execution = new Model_Execution();
        $this->devolutive = $devolutive;
    }
    
    /**
     * 
     * // case 2 = Tipo do questionario autoavaliacao.
     */
    public function initTipo()
    {
         // case 2 = Tipo do questionario autoavaliacao.
         
         $result = false;
         
         $arrScore = $this->devolutive->makeScoreRAA($this->devolutive->getQuestionnaireId(), $this->devolutive->getUserId());
         
         if ( $arrScore ) {
                    
              //grava dados em Execution
              $this->execution->finishExecution($this->devolutive->getQuestionnaireId(), 
                                                      $this->devolutive->getUserId(), 
                                                      $this->devolutive->getArqPath(), 
                                                      $arrScore[2], 
                                                      $this->devolutive->getIsRA()
                                );
                    
               //faz geracao do pdf
               $result = $this->devolutive->makePdfDevolutiveAutoAvaliacao($this->devolutive->getQuestionnaireId(), 
                                                                           $this->devolutive->getUserId(), 
                                                                           $this->devolutive->getDirName(), 
                                                                           $this->devolutive->getPublicDir(), 
                                                                           $this->devolutive->getArqName(), 
                                                                           $this->devolutive->getIsRA()
                                           );                    
           } //end if
           
         return $result;
    }
    
         
} //end class

?>
