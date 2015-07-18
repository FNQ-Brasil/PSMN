<?php

class View_Helper_HasCurrentCompetition extends Vtx_View_Helper_Abstract
{
    public function hasCurrentCompetition()
    {
        $modelECAC = new Model_EnterpriseCategoryAwardCompetition();
        $enterpriseRow = Zend_Auth::getInstance()->getIdentity()->getEnterpriseRow();
        if (!$enterpriseRow) {
            return false;
        }
        $enterpriseId = $enterpriseRow->getId();
        return $modelECAC->hasECAC($enterpriseId, Zend_Registry::get('configDb')->competitionId);
    }
}