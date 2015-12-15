<?php

class Vtx_Util_Formatting
{
	public static function realToDecimal($value)
	{
		if ($value <= 0)
        {
			return;
		}
        //$realToDecimal = $value;
        $str = str_replace(',','.',preg_replace('#[^\d\,]#is','',$value));
        $realToDecimal = number_format($str, 2, '.','');
		return $realToDecimal;
	}
    
	public static function decimalToReal($value)
	{
		if ($value <= 0)
        {
			return;
		}
        $value = number_format($value, 2, ',','.');
		return $value;
	}  
    
	public static function toDecimalPontuacao($value)
	{
		if ($value <= 0)
        {
			return;
		}
        $value = number_format($value, 4, '.',',');
		return $value;
	}      
    
    public static function maskFormat($val, $mask)
	{
        $maskared = '';
        $k = 0;
        
        for ($i = 0; $i<=strlen($mask)-1; $i++) {
            if ($mask[$i] == '#') {
                if(isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if(isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
	}
    
    public static function unmaskCnpjCpf($doc) {
        return preg_replace('/[^0-9]/', '', $doc);
    }
    
    
    /**
     * round (,2) and double
     * 
     * @param scalar
     * @return double
     */
    public static function roundAndDouble($num)
    {
        //return round((double)$num,4);
        return $num;
    }
        
    /**
     * formata protocolo para geracao devolutiva MPE.
     * 
     * @param type $idProtocolo
     * @param type $dataProtocolo
     * @return string
     */
    public static function protocoloMPE($idProtocolo, $dataProtocolo)
    {
        $dformat = date("Ymd", strtotime($dataProtocolo));
        
        $protocolo = $dformat.$idProtocolo;
        
        return $protocolo;
    }
    
    /**
     * formata protocolo para geracao devolutiva SESCOOP
     * 
     * @param int $idProtocolo.
     * @param date $dataProtocolo
     * @return string
     */
    public static function protocoloSESCOOP($idProtocolo, $dataProtocolo)
    {
        $dformat = date("Ymd", strtotime($dataProtocolo));
        
        $protocolo = $dformat.$idProtocolo;
        
        return $protocolo;
    }    
    
    /**
     * formata protocolo para geracao devolutiva PSMN
     * 
     * @param int $idProtocolo
     * @param date $dataProtocolo
     * @return string
     */
    public static function protocoloPSMN($idProtocolo, $dataProtocolo)
    {
        $dformat = date("Ymd", strtotime($dataProtocolo));
        
        $protocolo = $dformat.$idProtocolo;
        
        return $protocolo;
    }      
    /**
     * contador de palavras
     * 
     * @param string $string.
     * @return integer
     */
    public static function contadorPalavras($string) {
        // Return the number of words in a string.
        $string= str_replace("&#039;", "'", $string);
        $t= array(' ', "\t", '=', '+', '-', '*', '/', '\\', ',', '.', ';', ':', '[', ']', '{', '}', '(', ')', '<', '>', '&', '%', '$', '@', '#', '^', '!', '?', '~'); // separators
        $string= str_replace($t, " ", $string);
        $string= trim(preg_replace("/\s+/", " ", $string));
        $num= 0;
        if (mb_strlen($string, "UTF-8")>0) {
            $word_array= explode(" ", $string);
            $num= count($word_array);
        }
        return $num;
    }
}
    