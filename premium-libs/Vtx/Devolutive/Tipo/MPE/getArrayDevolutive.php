<?php


/**
 * 
 * cria arrays que sao utilizados na geracao da devolutiva
 * 
 * @author vtx
 */
class Vtx_Devolutive_Tipo_MPE_getArrayDevolutive {
 
    /** 
     * @var Model_Question 
     * 
     */
    protected $Question;


    public function getArrayDevolutiveReturn($questionnaireId, $userId, $programaId, $blockId = null) 
    {
            
        $this->Questionnaire = new Model_Questionnaire();
        $this->Alternative = new Model_Alternative();
        $this->Question = new Model_Question();
        
        
        try {
            
            $arrDevolutiveRAA = array();
            $arrCriteria = array();
            $arrBlocks = array();
            $arrQuestionnaire = array();
            $arrRadarData = array();
            
            // Definições do Questionário
            //@TODO - por em cache
            $questionnaireDefs = $this->Questionnaire->getQuestionnaireById($questionnaireId);
            
            $arrQuestionnaire['title'] = $questionnaireDefs->getTitle();
            $arrQuestionnaire['description'] = $questionnaireDefs->getDescription();
            $arrQuestionnaire['long_description'] = $questionnaireDefs->getLongDescription();
            $arrQuestionnaire['operation_beginning'] = Vtx_Util_Date::format_dma($questionnaireDefs->getOperationBeginning());
            $arrQuestionnaire['operation_ending'] = Vtx_Util_Date::format_dma($questionnaireDefs->getOperationEnding());

            // Definições da Questão
            $questionsDefs = $this->Question->getAllByQuestionnaireIdBlockId($questionnaireId, $blockId);
                       
            $cacheQuestion = new Vtx_Cache_MPE_QuestionarioCache();
            
             /**
              * - metodo criado para refatorar 2 metodos que faziam a mesma coisa e estavam dentro do foreach
              * - metodo que retorna todas alternativas de todas as questoes de um bloco que um Usuario respondeu
              * - a utilizacao deste metodo reduziu media de 25% a qtd de queries chamadas pelo BD.
              * - no meu exemplo reduziu de 320 para 235.
              * - chamada: $isAnswered = $this->Question->setaQuestionResp($QuestionRespDbTable, $questionId);
              * @author esilva
             */          
            $QuestionRespDbTable = $this->Question->getAnswerByBlockId($blockId, $userId, $programaId);
            
            //var_dump ($QuestionRespDbTable); exit;

            /**
             * 
             * Recupera dados de cada questao de um Bloco:
             * - enunciado questao 
             * - alternativas
             * - resposta
             * - resultado anual
             * 
             */
            foreach ($questionsDefs as $question_def) 
            {
                
                $idBlock = "";
                $idCriterion = "";
                   
                $questionId = $question_def->getId();
                $question_value = $question_def->getQuestao();

                // Grava a questão no array de devolutiva
                $arrDevolutiveRAA[$questionId]['designation'] = $question_def->getDesignacao();
                $arrDevolutiveRAA[$questionId]['value'] = $question_value;
                $arrDevolutiveRAA[$questionId]['text'] = $question_def->getTexto();
                
                // Verifica se existe Bloco válido e grava nos arrays de blocos e devolutiva
                $idBlock = $question_def->getBloco();
                if ($idBlock != "" && $idBlock != 0) {
                    $arrBlocks[$idBlock] = $question_def->getBlocoTitulo();
                    $arrDevolutiveRAA[$questionId]['block'] = $question_def->getBloco();
                }
                
                // Verifica se existe Critério válido e grava nos arrays de critérios e devolutiva
                $idCriterion = $question_def->getCriterio();
                if ($idCriterion != "" && $idCriterion != 0) {
                    $arrCriteria[$idCriterion] =  $idCriterion.". ".$question_def->getCriterioTitulo();
                    $arrDevolutiveRAA[$questionId]['criterion'] = $question_def->getCriterio();
                }
                
                $isAnswered['answerResult'] = null;
                //se empresa respondeu a questao
                //$isAnswered = $this->Question->isAnsweredByEnterprise($questionId,$userId, true);

                $isAnswered = $this->Question->setaQuestionResp($QuestionRespDbTable, $questionId);
                
                //var_dump('isAnswered',$isAnswered);
                
                if($isAnswered['status']) {
                    // Recupera a resposta 
                    //$answer = $this->Question->getQuestionAnswer($questionId,$userId);
                    //refatorado para otimizar queries executadas
                    $answer = $isAnswered['answerResult'];
                    
                    $alternative_id = $answer['alternative_id'];
                    
                    $arrDevolutiveRAA[$questionId]['alternative_id'] = $alternative_id;
                    $arrDevolutiveRAA[$questionId]['write_answer'] = (isset($answer['answer_value'])) ? $answer['answer_value'] : "";
                    
                    if (count($answer['annual_result']) > 0) {
                        $arrDevolutiveRAA[$questionId]['annual_result'] = $answer['annual_result'];
                        $arrDevolutiveRAA[$questionId]['annual_result_unit'] = $answer['annual_result_unit'];
                    } else {
                        $arrDevolutiveRAA[$questionId]['annual_result'] = "";
                        $arrDevolutiveRAA[$questionId]['annual_result_unit'] = "";
                    }
                    
                    // Recupera o feedback da alternativa escolhida
                    #$alternative =  $this->Alternative->getAlternativeById($alternative_id);
                    //recupera do cache
                    $alternative =  $cacheQuestion->alternative($alternative_id, $this->Alternative);
                                      
                    $arrDevolutiveRAA[$questionId]['alternative_designation'] = $alternative->getDesignation();
                    
                    $arrDevolutiveRAA[$questionId]['alternative_feedback'] = $alternative->getFeedbackDefault();
                    
                    // Recupera os comentarios 1 do avaliador da resolução da questão 
                    //REFACTORING
                    //$arrDevolutiveRAA[$questionId]['answer_feedback'] = $this->Question->getAnswerFeedback( $isAnswered['objAnswered']['AnswerIdValue'] );//$isAnswered['objAnswered']->getAnswerId());
                    $arrDevolutiveRAA[$questionId]['answer_feedback'] = $answer['AnswerFeedback'];
                    
                    // Recupera os comentarios 2 do avaliador da resolução da questão 
                    //REFACTORING
                    //$arrDevolutiveRAA[$questionId]['answer_feedback_improve'] = $this->Question->getAnswerFeedbackImprove($isAnswered['objAnswered']['AnswerIdValue']);                    
                    $arrDevolutiveRAA[$questionId]['answer_feedback_improve'] = $answer['AnswerFeedbackImprove'];
                    
                    //AdditionalInfo
                    $arrDevolutiveRAA[$questionId]['additional_info'] = $answer['AdditionalInfo'];
                                        
                }
                
                // Recupera as alternativas da questão
                //$alternativesDefs =  $this->Alternative->getAllByQuestionId($questionId);
                
                //recupera do cache
                $alternativesDefs = $cacheQuestion->alternativasEQuestoes($questionId, $this->Alternative);
                                
                //var_dump ('alternativesDefs: ', $alternativesDefs);
                //echo "<br><Br>";
                
                foreach ($alternativesDefs as $alternative_def) {
                   if (is_object($alternative_def)) {
                       $arr_alternative[$alternative_def->getDesignation()] = $alternative_def->getValue();
                    } else {
                       $arr_alternative[$alternative_def['Designation']] = $alternative_def['Value'];
                    }
                }
                
                $arrDevolutiveRAA[$questionId]['alternatives'] = $arr_alternative;
                    
            }

            return array($arrDevolutiveRAA, $arrBlocks, $arrCriteria, $arrQuestionnaire);
                
        } catch (Vtx_UserException $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Questionnaire::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
        
    }
    

    
    
    
}

?>
