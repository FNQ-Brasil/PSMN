<?php
/**
 * 
 * Model_EmailQueue
 *
 */
class Model_EmailQueue
{
    protected $_messagesError = array(
        'EmailQueueFormError' => 'Erro no preenchimento do campo: ',
    );
    
    private $tbEmailQueue = "";
    
    
    const STATUS_QUEUE_E =  'ESPERA';
    const STATUS_QUEUE_D =  'DISPARADO';
    const STATUS_QUEUE_P =  'PROBLEMA';    
    

    public function __construct()
    {
        $this->tbEmailQueue = new DbTable_EmailQueue();
       // $this->Acl = Zend_Registry::get('acl');
        
        $this->tbEmailQueue->getStatusQueue();
    }

    public function createEmailQueue($data)
    {
        DbTable_EmailQueue::getInstance()->getAdapter()->beginTransaction();
        try {
            $eQueueRow = DbTable_EmailQueue::getInstance()->createRow()
                ->setFrom($data['From'])
                ->setTo($data['To'])
                ->setBcc($data['Bcc'])
                ->setSubject($data['Subject'])
                ->setMessage($data['Message'])
                ->setTypeQueue($data['TypeQueue'])
                ->setStatusQueue($data['StatusQueue']);    
            $eQueueRow->save();

            
            DbTable_EmailQueue::getInstance()->getAdapter()->commit();
            return array(
                'status' => true, 'row' => $eQueueRow
            );
        } catch (Vtx_UserException $e) {
            DbTable_EmailQueue::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_EmailQueue::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
        
    }
    
    public function updateEmailQueue($eQueueId, $data)
    {
        DbTable_EmailQueue::getInstance()->getAdapter()->beginTransaction();
        try {
            
            $eQueueRow = $this->getEmailQueueById($eQueueId);
                       
            $now = date('Y-m-d H:i:s');

            $eQueueRow
                ->setStatusQueue($data['StatusQueue'])
                ->setErrMsg($data['ErrMsg'])    
                ->setUpdatedAt($now);    
        
            $eQueueRow->save();

            
//            $userLogged = Zend_Auth::getInstance()->getIdentity();
//            $permissionEvaluationOfResponse = $this->Acl->isAllowed(
//                $userLogged->getRole(), 'management:questionnaire', 'evaluation-of-response'
//            );
            
            DbTable_EmailQueue::getInstance()->getAdapter()->commit();
            return array(
                'status' => true, 'row' => $eQueueRow
            );
        } catch (Vtx_UserException $e) {
            DbTable_EmailQueue::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_EmailQueue::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
        
    }
    

    /**
     * 
     * @return Array
     */
    public function getAll($l=1)
    {
        return $this->tbEmailQueue->getStatusQueue($l)->toArray();
    }

    /**
     * 
     * @param type $Id
     * @return type
     */
    public function getEmailQueueById($Id=0)
    {       
        $objResult = $this->tbEmailQueue->fetchRow(array('Id = ?' => $Id));
        return $objResult;
    }
    

    protected function _filterInputEmailQueue($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'answer_value' => array(
                    array('Alnum', array('allowwhitespace' => true))
                )
            ),
            array( //validates
                'alternative_id' => array('NotEmpty'),
                'answer_value' => array('allowEmpty' => true),
                'start_time' => array('allowEmpty' => true),
                'end_time' => array('allowEmpty' => true),
                'answer_date' => array('allowEmpty'=> true),
                'user_id' => array('NotEmpty'),
                'logged_user_id' => array('NotEmpty')
            ),
            $params,
            array('presence' => 'required')
        );
        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }
        return $input;
    }
    
    
    /**
     * cadastra uma mensagem de email para statusQueue ESPERA
     * 
     * 
     * @param type
     * @return
     */
    public function setEmailQueue ($to, $from, $subject, $message, $bcc='', $statusQueue = 'ESPERA')
    {               
        $data['To'] = $to;
        $data['From'] = $from;
        $data['Subject'] = $subject;
        $data['Message'] = $message;
        $data['Bcc'] = $bcc;
        
        $data['TypeQueue'] = "Admin";
        $data['StatusQueue'] = $statusQueue;           
        $result = $this->createEmailQueue($data);
        
        return $result;
    }    
    
    
    /**
     * Faz envio de msgs email com status queue ESPERA
     * 
     * @param array $data
     * @return boolean
     */
    public function sendEmailStatusQueue($data, $statusQueue)
    {   
        $resultSend = false;
        if ($statusQueue == 'ESPERA') {        
            $resultSend = Vtx_Util_Mail::send($data['To'],$data['From'],$data['Subject'],$data['Message'],$data['Bcc']);
        }
        
        return $resultSend;
    }    
    
    
    
    /**
     * recebe fetchall da tabela EmailQueue
     * 
     * 
     * @param type $all
     * @throws Exception
     */
    public function enviaMsgeAtualizaStatus($all)
    {
        $i = 1;
        $print_tela = "";
        foreach ($all as $campo) {
            /**
             * 
             * 1. executa disparo para cada mensagem
             * 2. update DISPARADO para cada mensagem
             * 
             */
            $IdEmailQueue = $campo['Id'];
            $data = array (
                'To' => $campo['To'],
                'From' => $campo['From'],
                'Subject' => $campo['Subject'],
                'Message' => $campo['Message'],
                'Bcc' => $campo['Bcc']                               
            );
            
            try {
                
                //faz disparo de email            
                $resultSend = $this->sendEmailStatusQueue($data, 'ESPERA');
                if (!$resultSend) {
                    throw new Exception ("Mensagem não disparada (Zend_Mail)");
                }
                
                //para testar catch, descomente linha abaixo
                //throw new Exception("Value must be 1 or below");
                
                // atualiza para DISPARADO                
                $this->updateMsg($IdEmailQueue, self::STATUS_QUEUE_D) ;
                
                //para pegar valores dos campos
                $print_tela .= "\n".$i." - Id: ".$campo['Id']. " -> Mensagem disparada";
                $print_tela .= "\n  - To: ".$campo['To'];
                $print_tela .= "\n  - Subject: ".$campo['Subject'];
                $print_tela .= "\n";
                
            } catch (Exception $e) {
            
                $code = $e->getCode();
                $message = $e->getMessage();
                $errMsg = "
                  code: ".$code." \n<br>\n<br>
                  message: ".$message."    
                ";
                $this->updateMsg($IdEmailQueue, self::STATUS_QUEUE_P, $errMsg) ;
                
                //para pegar valores dos campos
                $print_tela .= "\n".$i." - Id: ".$campo['Id']. " -> Mensagem nao disparada (Zend_Mail)";
                $print_tela .= "\n  - To: ".$campo['To'];
                $print_tela .= "\n  - Subject: ".$campo['Subject'];
                $print_tela .= "\n";
                               
            }
            
            $i++;
        }
        
        return $print_tela;
    }
    
    
    
    /**
     * Faz update de status no email queue
     * 
     * @param type $IdEmailQueue
     * @param type $status
     */
    public function updateMsg($IdEmailQueue, $status, $errMsg ='') 
    {
        $data['StatusQueue'] = $status;
        $data['ErrMsg'] = $errMsg;
        $this->updateEmailQueue($IdEmailQueue, $data);
    }
    
    
    
    
}