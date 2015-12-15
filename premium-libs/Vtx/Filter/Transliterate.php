<?php

/**
 * 
 * Vtx_Filter_Transliterate
 * @author tsouza
 * @return Zend_Db_Table
 *
 */
class Vtx_Filter_Transliterate implements Zend_Filter_Interface
{

    public function filter($string)
    {
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ$ßàáâãäåæ@çèéêë&amp;'
                . 'ìíîïðñòóôõöøùúûüýýþÿŔŕ°ºª,.;:\|/"^~*%# ()[]{}=!?`‘’'
                . "'";

        $b = 'aaaaaaaceeeeiiiidnoooooouuuuybssaaaaaaaaceeeee'
                . 'iiiidnoooooouuuuyybyRrooa--------------------------'
                . '-';

        $string = strtolower(strtr($string, $a, $b));

        // Evita hífens repetidos
        return preg_replace('/--+/', '-', $string);
    }

}