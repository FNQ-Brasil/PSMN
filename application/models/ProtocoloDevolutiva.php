<?php
/**
 * 
 * @author esilva
 *
 * 
 * 
 */
class Model_ProtocoloDevolutiva
{
    
    protected $dbtable;

    
    public function __construct() 
    {
        $this->setDbtable(new DbTable_ProtocoloDevolutiva());
    }
    
    /**
     * 
     * No PSMN competitionId eh mesmo que programaId
     * 
     * gera protocolo para a devolutiva
     * 
     * insert na tabela ProtocoloDevolutiva
     * 
     * @param type $UserId
     * @param type $userIdLogado
     * @param type $ProgramaId
     * @return type
     */
    public function insertProtocolo($UserId, $userIdLogado, $programaId)
    {
        $competitionId = Zend_Registry::get('configDb')->competitionId;
        //$programaIdCurrent = Zend_Registry::get('configDb')->programaIdCurrent;
        $programaIdCurrent = $competitionId;
        settype($programaIdCurrent,'integer');        
        
        $protocolo = Vtx_Devolutive_Helper_BancoDados_Proc::protocoloDevolutiva($UserId, $userIdLogado, $programaIdCurrent);             
        
        return $protocolo;
    }
    /**
     * grava o caminho da devolutiva gerada
     * 
     * @param type $devolutivePath
     * @param type $idProtocolo
     * @return boolean
     */
    public function updateDevolutivaPath ($devolutivePath, $idProtocolo)
    {
        Vtx_Devolutive_Helper_BancoDados_Proc::gravaPathDevolutiva($devolutivePath, $idProtocolo);
        
        return true;
    }

    
    
     
    /**
     * funcao que faz a geracao e recuperacao do Protocolo da devolutiva
     * 
     * @author esilva
     * 
     * @param type $objView
     * @param type $objDevolutive
     * @param type $objUser
     * @param type $questionnaire_id
     * @param type $user_id
     * @param type $programaId
     * @param type $permissionEvaluationOfResponse
     * 
     */
    public function geracaoDoProtocolo( $objView, $objDevolutive, $objExecution ,$objUser, $questionnaire_id, 
                                        $user_id, $loggedUserId, $programaId, $permissionEvaluationOfResponse
                                      )
    {
        $this->loggedUserId = $loggedUserId;
        $this->view = $objView;
        $this->Execution = $objExecution;
        $this->Devolutive = $objDevolutive;
        $this->modelUser = $objUser;
        $this->modelProtocolo = $this;
               
        ///////////////////////////
        // CODIGO NOVO para protocolo devolutiva
        ///////////////////////////        
        
		
        $geraProtocolo = false;    
        
        if ($permissionEvaluationOfResponse) {
            $existsArchive = $this->Execution->getEvaluationPath($questionnaire_id, $user_id, $programaId);
        } else {
            $existsArchive = $this->Execution->getDevolutivePath($questionnaire_id, $user_id, $programaId);
        }
		
		
	

        //valida se deve gerar um protocolo
        if (!$existsArchive) {
            $geraProtocolo = true;
            //gerando protocolo para devolutiva
            $protocoloDevolutiva = $this->modelProtocolo->insertProtocolo($user_id, $this->loggedUserId, $programaId );
		 
   
            //este eh o numero de protocolo da devolutiva
            $this->Devolutive->setProtocoloIdDevolutiva($protocoloDevolutiva['lastId']);     
            
            $this->Devolutive->setProtocoloCreateAt($protocoloDevolutiva['createAt']);
            
            //este é a string protocolo que é printado na devolutiva
            $protocolo = Vtx_Util_Formatting::protocoloPSMN($this->Devolutive->getProtocoloIdDevolutiva(), $this->Devolutive->getProtocoloCreateAt());
			
		
            
            //pega o usuario logado que gerou a devolutiva, podendo ser o usuario, gestor ou admin
            $user = $this->modelUser->getUserById($this->loggedUserId);
            
            //JSON
            $this->view->protocoloId = $this->Devolutive->getProtocoloIdDevolutiva();
            $this->view->protocolo = $this->Devolutive->getProtocolo();
            $this->view->protocoloCreateAt = $this->Devolutive->getProtocoloCreateAt();            
            $this->view->userLogadoGerouDevolutiva = $user->getFirstName();

            $geraProtocolo = true;
			
            $this->Devolutive->setProtocolo($protocolo);
            
        } else {
            //caso seja uma devolutiva pdf ja gerada
            //recupera informacoes da devolutiva gerada na tabela ProtocoloDevolutiva

            //$objProtocoloDevolutiva = $this->modelProtocolo->getDbtable()->getProtocoloByUserId($userId, $programaId);
            $objProtocoloDevolutiva = $this->modelProtocolo->getDbtable()->getProtocoloByDevolutivePath($existsArchive);
            
            if (is_object($objProtocoloDevolutiva)) {
                
                $user = $this->modelUser->getUserById($objProtocoloDevolutiva->getUserIdLogado());
                //JSON
                $this->view->protocoloId = $objProtocoloDevolutiva->getId();
                $this->view->protocolo = Vtx_Util_Formatting::protocoloPSMN($objProtocoloDevolutiva->getId(), $objProtocoloDevolutiva->getCreateAt());
                
                $this->view->protocoloCreateAt = Vtx_Util_Date::format_date_devolutive($objProtocoloDevolutiva->getCreateAt());
                              
                $this->view->userLogadoGerouDevolutiva = $user->getFirstName();
                
                $geraProtocolo = true;
                
            } else {
                $this->view->protocoloId = "";
                $this->view->protocolo = "";
                $this->view->protocoloCreateAt = "";
                $this->view->userLogadoGerouDevolutiva = "";
            }
   
        }
        
        /////////////////////////////////  
        //fim codigo protocolo devolutiva
        /////////////////////////////////        
        return $geraProtocolo;      
    }    
    
    public function getDbtable() {
        return $this->dbtable;
    }

    public function setDbtable($dbtable) {
        $this->dbtable = $dbtable;
    }
    
    
}