<?php


/**
 * faz chamadas a procedures de sistema
 *
 * @author esilva
 */
class Vtx_Devolutive_Helper_BancoDados_Proc {

    
    /**
     * processa execucao de procedures que calculam pontuacao a partir das 
     * respostas do Questionario de Empreendedorismo
     * 
     * @param type $QuestionnaireId
     * @param type $UserId
     * @param type $BlockId
     * @param type $CompetitionId
     * @return boolean
     */
    public static function processaCaracteristicaEmpreendedora($QuestionnaireId, $UserId, $BlockId, $programaId  )
    {
        //forma correta para executar uma proc com Zend/MySQL
        $db = Zend_Registry::get('db');
        $sql = "CALL p_pontuacao_grade (?, ?, ?, ?)";
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        $params = array($QuestionnaireId, $BlockId, $UserId, $programaId);
        $stmt->execute($params);
        /* $stmt->fetch();*/
        $db->closeConnection();
                
        return true; 
    }       
    

    /**
     * MPE, SESCOOP, PSMN
     * OBS: apenas no PSMN competionId é o ProgramaId. Isso nao acontece no MPE e SESCOOP
     * Para esta funcao nao ha qq impacto.
     * 
     * faz insert de dados para protocolo da devolutiva
     * 
     * @param type $UserId
     * @param type $userIdLogado
     * @param type $ProgramaId
     * @param type $DevolutivePath
     * @return boolean
     */
    public static function protocoloDevolutiva ($UserId,$userIdLogado,$ProgramaId)
    {
        //forma correta para executar uma proc com Zend/MySQL
        $db = Zend_Registry::get('db');
        
        $sql = "CALL p_insertProtocolDevolutive ('INSERT',$UserId, $userIdLogado, $ProgramaId, '', @lastId, @createAt);";
        
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        
        //$params = array ($UserId, $userIdLogado, $ProgramaId);        
        //$stmt->execute($params);
        $stmt->execute();
        
        $stmt = $db->query("SELECT @lastId as lastId, DATE_FORMAT( @createAt , '%d/%m/%Y %H:%i' ) as createAt ;");

        //print_r($stmt->fetch());
        $result = $stmt->fetch();
        
        $db->closeConnection();
                
        return $result;
    }       
    
    /**
     * MPE, SESCOOP
     * atualiza dados da devolutiva gerada para um determinado numero de protocolo
     * 
     * @param type $devolutivePath
     * @param type $idProtocolo
     * @return boolean
     */
    public static function gravaPathDevolutiva ($devolutivePath, $idProtocolo)
    {        
        $db = Zend_Registry::get('db');
                
        $sql = "UPDATE ProtocoloDevolutiva SET DevolutivePath = ? WHERE Id = ?";
        
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        
        $params = array($devolutivePath, $idProtocolo);
        
        $stmt->execute($params);
        
        $db->closeConnection();
        
        return true;
        
    }
    
    
}
