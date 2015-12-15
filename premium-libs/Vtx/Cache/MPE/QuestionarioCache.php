<?php

/**
 * Metodos que instanciam Zend_Cache para fazer cache que areas do site
 *
 * @author esilva
 */
class Vtx_Cache_MPE_QuestionarioCache {

    /**
     * @var Zend_Cache_Core
     */
    protected $cache;
   
    public function __construct() 
    {
        //instancia Zend_Cache_Core        
        $this->cache = Zend_Registry::get('cache_FS');
    }


    /**
     * faz cache do bloco e criterios do bloco
     * 
     * @param int $blocoId
     * @param DbTable_QuestionnaireRow $dbtableQuestionnaireRow
     * @return mixed
     */
    public function BlocoECriterios($blocoId, DbTable_QuestionnaireRow $dbtableQuestionnaireRow)
    {        
        $nameCache = "mpe_bloco_".$blocoId;
        $blocoCacheModel = $this->cache->load($nameCache);
        
        $msgCache = "pegou do cache";
        
        if (!$blocoCacheModel) 
        {
            $blocoCacheModel = $dbtableQuestionnaireRow->getAllQuestionsByBlockIdAndCriterionsForView($blocoId);
            $this->cache->save($blocoCacheModel, $nameCache);
            $msgCache = "NÂO pegou do cache";
        }
        
        return $blocoCacheModel;
    }
    
    
    /**
     * Cache da questao e alternativas
     * 
     * @param int $questionId
     * @param Model_Alternative $modelAlternative
     * @return mixed
     * 
     * utilizado por listagem de questoes no front e geracao devolutiva.
     */
    public function alternativasEQuestoes($questionId, Model_Alternative $modelAlternative)
    {          
        $nameCache = 'alternatives_question_'.$questionId;
        
        $alternativesQuestionCache = $this->cache->load($nameCache);
        
        $origem = "--->alternatives vem do cache---";
        
        //recupera do cache
        if ($alternativesQuestionCache == false) 
        {                                                   
            $alternativesQuestionCache = $modelAlternative->getAllByQuestionId($questionId, true);
            
            $this->cache->save($alternativesQuestionCache, $nameCache);
            
            $origem = "--->alternatives NAO vem do cache---";
        }
        
        return $alternativesQuestionCache;
    }
    
    /**
     * 
     */
    public function alternative ($alternative_id, Model_Alternative $modelAlternative)
    {
        $nameCache = 'alternative_'.$alternative_id;
        
        $alternative = $this->cache->load($nameCache);
        
        $origem = "--->alternative vem do cache---";
        
        //recupera do cache
        if ($alternative == false) 
        {                                                   
            $alternative = $modelAlternative->getAlternativeById($alternative_id);
            
            $this->cache->save($alternative, $nameCache);
            
            $origem = "--->alternative NAO vem do cache---";
        }
        
        return $alternative;        
    }
    
    
    /**
     * cache para query executada na devolutiva
     * 
     * 
     */
    
    
}
