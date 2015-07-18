<?php

class DbTable_EnterpriseRow extends Vtx_Db_Table_Row_Abstract
{
    public function getEnterpriseUserId()
    {
        $userLocality = $this->findUserLocality()->current();
        return ($userLocality)? $userLocality->getUserId() : null;
    }
    
    public function getFormattedCnpj()
    {
        return Vtx_Util_Formatting::maskFormat($this->getCnpj(), '##.###.###/####-##');
    }
    
    public function getCurrentStatus() {
        if ( $this->getStatus() == 'A' ) {
            return true;
        }
        return false;
    }
    
    public function getHasEligibility() {
        $modelEnterprise = new Model_Enterprise();
        return $modelEnterprise->hasEligibilityRules($this->getIdKey());
    }

    public function getPresidentRow()
    {
        $President = new Model_President();
        return $President->getPresidentByEnterpriseId($this->getId());
    }
    
    public function getCompanyHistoryString()
    {
        $txtCompany = $this->getCompanyHistory();
        $txtCompany2 = str_replace(array("\r\n", "\r", "\n"), "<br />", $txtCompany); 
        $explode = explode('<br />', $txtCompany2);
        $string = '';
        foreach ($explode as $key => $resumo) {
            $string .= ' '.$resumo;
        }
        $reticencias = (strlen($string) > 250)?'(...)':'.';
        return substr($string, 0, 250).$reticencias;
        //$txtCompany2 = wordwrap($txtCompany, $txtCompanyWidth, "<br />\n",false);
    }
    
} 