<?php

class Vtx_Util_String
{
    public static function hashMe($phrase, $salt = null)
    {
        $saltLength = 15;
        $key = '!@#$%^&*()_+=-{}][;";/?<>.,';

        $salt = ($salt == '')?
            substr(hash('sha512',uniqid(rand(), true).$key.microtime()), 0, $saltLength)
            : substr($salt, 0, $saltLength);

        return array(
            'sha' => hash('sha512', $salt . $key .  $phrase),
            'salt' => $salt
        );
    }
    
    public static function needToAnswer($userId, $questionnaireId, $programaId)
    {
        $dbTable_Questionnaire = new DbTable_Questionnaire();
        
        $questionnaireToAnswer = $dbTable_Questionnaire
            ->needToFinish($questionnaireId,$userId,$programaId)->toArray();
            
        $countQuestionnaireToAnswer = count($questionnaireToAnswer);
        $block = array();
        for ($index = 0; $index < $countQuestionnaireToAnswer; $index++) {
            $block[$questionnaireToAnswer[$index]['Block']][] = $questionnaireToAnswer[$index];
        }
        $controle = array();
        foreach($block as $key => $value) {
            if( !isset($controle[$key]) ) {
                $controle[$key]['bloco'] = $value[0]['Value'];
                $controle[$key]['qtd'] = count($block[$key]);
            }
        }
        $plural = ($countQuestionnaireToAnswer == 1)? 0 : 1;
        
        $html = '<li><a href="' . Zend_Controller_Front::getInstance()->getBaseUrl()
            . '/questionnaire/respond/index/qstn/' . $questionnaireId
            . '"> <div> <span><span>Falta' . ($plural? 'm' : '') .'</span>' . $countQuestionnaireToAnswer
            . '</span> </div> <div>quest' . ($plural? 'ões' : 'ão') .' para responder</div> </a></li>';

        foreach ($controle as $key => $value) {
            //$plural = ($value['qtd'] == 1)?'ão':'ões';
            
            // $html .= "<li><div> <span>{$value['qtd']}</span> </div> <div>Bloco {$value['bloco']}</div></li>";
        }
            
        return $html;
    }
    
    public static function replace_links( $text ) 
    {	
        $text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);

        $ret = ' ' . $text;

        // Replace Links with http://
        $ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);

        // Replace Links without http://
        $ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);

        // Replace Email Addresses
        $ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
        $ret = substr($ret, 1);

        return $ret;
    }
    
}