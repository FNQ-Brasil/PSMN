<?php

class Vtx_Util_Mail
{
    
	public static function send($to, $from, $subject, $message, $bcc = null)
	{        
        $config = Zend_Registry::get("config");
		$mailConfig = $config->mail;

		if ($mailConfig->smtp) {
			$transport = new Zend_Mail_Transport_Smtp($mailConfig->host, $mailConfig->smtpconfig->toArray());
		} else {
			// Parâmetros para o POSTFIX, o -f não tem espaço (-femail@email.com)
			$transport = new Zend_Mail_Transport_Sendmail('-f' . $mailConfig->mailfrom);
		}

		Zend_Mail::setDefaultTransport($transport);
        
        $mail = new Zend_Mail('UTF-8');

        $mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
        $mail->addHeader('Content-type','text/html');
        $mail
            ->addTo($to)
            ->setFrom($from? $from : $mailConfig->mailfrom, isset($mailConfig->mailfromname)? $mailConfig->mailfromname : null);
        
        if ($bcc) {
            $mail->addBcc($bcc);
        }
        
        $mail->setSubject($subject)
            ->setBodyHtml($message, 'UTF-8');
        
     //   var_dump($mailConfig->mailfrom, $mailConfig->smtpconfig->toArray());
        if ($mail->send()) {
            return true; 
        }
        /*
        if (mail($emailCopy, $subject, stripslashes($message), $headers)) 
        {
            return true;
        }
        */
        return false;
	}
}