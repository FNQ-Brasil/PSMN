<?php
/**
 * Description of QueueController
 *
 * @author everton silva
 */
class Cli_QueueController extends Vtx_Action_Abstract 
{
    /**
     *
     * @var Model_EmailQueue
     */
    protected $emailQueue;
    
    
    public function init()
    {
        
        $this->emailQueue = new Model_EmailQueue(); 
        
    }

    public function indexAction() 
    {
        //
    }
    
    /**
     * Action executada via linha comando.
     * Faz disparos de mensagens de email na fila.
     * 
     * $ cd /public
     * $ sudo php cli.php -e development -a cli.queue.disparaemailemespera -l 10
     * 
     */
    public function disparaemailemesperaAction()
    {
        //desabilita layout
        $this->_helper->layout()->disableLayout();  
        
        //desabilita view
        //$this->_helper->viewRenderer->setNoRender();
        
        //phpinfo();        
        
        
        $limit = isset($_SERVER['argv'])? $_SERVER['argv'][6] : 50;
        //echo "\nlimit: ".$limit;
        
        $this->view->toptext = "\n------inicio execucao------\n";
        
        //Recupera msgs emails em estado de espera, prontas para disparo              
        $all = $this->emailQueue->getAll($limit);
        $this->view->all = $all;
        
        //faz disparos e atualiza status da mensagem
        $this->view->print_tela = $this->emailQueue->enviaMsgeAtualizaStatus($all);
           
        $this->view->headertext = "\n------fim execucao---------\n\n";
    }    
    
    
    /**
     * @test
     * cria uma entrada na tabela EmailQueue
     */
    public function createqueueAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $to = "to6";
        $from = "from6";
        $subject = "subject6";
        $message = "message6";
        $bcc = "bcc6";
        
        //insere com statuQueue = ESPERA
        $this->emailQueue->setEmailQueue($to, $from, $subject, $message, $bcc);
    }
    
    
    /**
     * @test
     * 
     */
    public function disparaemailAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        echo "\nvai! disparaemail";
    }
    
    
} //end controller