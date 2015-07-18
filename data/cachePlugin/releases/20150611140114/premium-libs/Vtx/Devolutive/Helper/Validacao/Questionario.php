<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Questionario
 *
 * @author ever
 */
class Vtx_Devolutive_Helper_Validacao_Questionario 
{

    
    /**
     * faz validacao das questoes respondidas em um questionario
     * 
     */
    public static function verificaQuestoesRespondidas(Model_Questionnaire $obj, $questionnaire_id, $user_id)
    {
        /**
         * verifica se todas as questoes foram respondidas
         * É um requisito para que a devolutiva seja gerada
         */
        $return = true;
        
        $questoesRespondidas = $obj->isFullyAnswered($questionnaire_id, $user_id);
        
        if (!$questoesRespondidas) {
            $return = false;
        }              
        
        return $return;
    }       

    
    
    
    
    
    
}

?>
