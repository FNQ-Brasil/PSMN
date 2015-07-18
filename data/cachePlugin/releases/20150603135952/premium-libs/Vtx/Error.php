<?php

/**
 * 
 * Vtx_Error
 * @author tsouza
 *
 */
class Vtx_Error
{   
    public static function handle($errno, $errstr, $errfile, $errline)
    {
        /*if (!error_reporting()){
            return;
        }*/
        $error = $errstr . " in $errfile:$errline errorn:". $errno;
        if (Zend_Registry::isRegistered('logger')) {
            Zend_Registry::get('logger')->log($error, 0);
        }
        throw new Exception($error);
    }

    public static function set()
    {
        set_error_handler('Vtx_Error::handle');
    }
}