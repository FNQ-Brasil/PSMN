<?php

class Vtx_Util_Date
{
    public static function format_dma($strDate)
    {
        $date = new Zend_Date($strDate, 'yyyy-MM-dd');
        return $date->get('dd/MM/yyyy');
    }

    public static function format_dateToTimeStamp($strDate)
    {
        $date = new Zend_Date($strDate, 'yyyy-MM-dd');
        return $date->getTimestamp();
    }
    
    public static function format_iso($strDate)
    {
        $date = new Zend_Date($strDate, 'dd/MM/yyyy');
        return $date->getIso();
    }
    
    public static function format($strDate, $formatReturn = 'YYYY-MM-dd HH:mm:ss')
    {
        $date = new Zend_Date($strDate, 'dd/MM/yyyy');
        return $date->get($formatReturn); //toString('YYYY-MM-dd HH:mm:ss'); 
    }
    
    public static function format_hora($strDate, $formatReturn = 'YYYY-MM-dd HH:mm:ss')
    {
        $date = new Zend_Date($strDate);
        return $date->get($formatReturn); //toString('YYYY-MM-dd HH:mm:ss'); 
    }
    
    public static function dateTimeSqlSrvToTimestamp($param)
    {
        if($param instanceof DateTime){
            return $param->getTimestamp();
        } else {
            return strtotime($param);
        }
    }
    
    /**
     * formata data para printar na devolutiva pdf
     * 
     * @param type $strDate
     * @return type
     */
    public static function format_date_devolutive($strDate)
    {
        return date("d/m/Y H:i", strtotime($strDate));
    }
    
}