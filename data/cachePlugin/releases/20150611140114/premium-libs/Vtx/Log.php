<?php
/*
	EMERG   = 0;  // Emergency: system is unusable
	ALERT   = 1;  // Alert: action must be taken immediately
	CRIT    = 2;  // Critical: critical conditions
	ERR     = 3;  // Error: error conditions
	WARN    = 4;  // Warning: warning conditions
	NOTICE  = 5;  // Notice: normal but significant condition
	INFO    = 6;  // Informational: informational messages
	DEBUG   = 7;  // Debug: debug messages
*/
/**
 * 
 * Vtx_Log
 * @uses Zend_Log
 * @author tsouza
 *
 */
class Vtx_Log extends Zend_Log
{
    CONST LOG_TYPE_GENERAL = 1;
    CONST LOG_TYPE_USER_LOGGED = 2;
    CONST LOG_TYPE_ADMIN_LOGGED = 3;

    /**
     * @param $lty_id Tipo do Log / Tabela log_type / 1 Geral / 2 Usuário logado / 3 Administrador logado 
     * (non-PHPdoc)
     * @see Zend_Log::log()
     */
    public function log($message, $priority, $extras = null)
    {
    	$data = array(
    		'lty_id' => 1,
    		'log_ip' => $_SERVER['REMOTE_ADDR'],
    		'log_userAgent' => $_SERVER['HTTP_USER_AGENT'],
    	);
    	/*
    	//Envio de e-mail em caso de erro crítico, Emergency ou Alerta
    	if ($priority <= Zend_Log::CRIT) {
            $strEmails = Zend_Registry::get('config')->erroEmergencialAlerta;
            if ($strEmails != '') {
                $emails = explode(',', Zend_Registry::get('config')->erroEmergencialAlerta);
            }
            
            if(!empty($emails)) {
                try {
                    Inter_Util_Controller_Action_Helper_InterMail::send(
                        $emails,
                        'Erro Emergencial Comunidade',
                        '_messages/eml_mailSend.phtml',
                        array(
                            'msg_message' => $additional_message
                        ),
                        '_messages/eml_mailSend.phtml'
                    );
                }
                catch (Exception $e) {
                    //var_dump($e->getMessage());
                }
            }
    	}
    	*/
    	return parent::log($message, $priority, $data);
    }
}