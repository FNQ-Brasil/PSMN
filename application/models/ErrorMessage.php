<?php

class Model_ErrorMessage
{
	public static function toHtml( $ErrorArray )
	{
		$returnValue = "";
		foreach ($ErrorArray as $Error) {
			$returnValue .= $Error["mensagem"] . "<br>";
		}
		return $returnValue;
	}
    
    public static function getFirstMessage($messages)
    {
        foreach($messages as $mHolder) {
            return current($mHolder);
        }
    }
}