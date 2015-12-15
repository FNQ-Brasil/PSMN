<?php

/**
 * Classe que verifica as etapas que o usuario terminou: sim ou nao.
 * 
 * 
 */

class View_Helper_TerminoEtapas extends Vtx_View_Helper_Abstract
{
    
    /**
     * checa se usuario terminou totalmente cada uma das etapas
     * 
     * @param int $enterpriseId
     * @param int $userId
     * @return array
     */
    public function terminoEtapas()
    {
        $etapas = $this->varEnterpriseRowAndUserId();
        $modelQuest = new Model_Questionnaire();
        $arrTerminoEtapas = $modelQuest->terminoEtapas($etapas['enterpriseId'], $etapas['userId']);

        return $arrTerminoEtapas;       
     }
     
    public function varEnterpriseRowAndUserId()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $key = $request->getParam('id_key', $request->getParam('enterprise-id-key', $request->getParam('enterprise_id_key')));

        if ($key) {
            //echo "-----1----"; 
            
            $modelEnterprise = new Model_Enterprise();
            
            $UserLocality = new Model_UserLocality();            
            $enterpriseIdKey = $key;
            
            $enterpriseRow = $modelEnterprise->getEnterpriseByIdKey($enterpriseIdKey);

            $enterpriseId = $enterpriseRow->getId();
            
            $userLocalityGetEnterprise = $UserLocality->getUserLocalityByEnterpriseId($enterpriseRow->getId());
            if (!$userLocalityGetEnterprise) {
                throw new Exception('Nenhum usuário relacionado nesta empresa.');
            }
            $userId = $userLocalityGetEnterprise->getUserId();           

        } else {
            $enterpriseRow = Zend_Auth::getInstance()->getIdentity()->getEnterpriseRow();
            $enterpriseId = $enterpriseRow['Id'];
            $userId = $enterpriseRow = Zend_Auth::getInstance()->getIdentity()->getUserId();
        }
        
        return array (
          'enterpriseId' => $enterpriseId,
          'userId' => $userId,
        );
   }
}