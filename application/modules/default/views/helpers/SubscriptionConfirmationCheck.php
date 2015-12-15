<?php

class View_Helper_SubscriptionConfirmationCheck extends Vtx_View_Helper_Abstract{

    protected $modelUserLocality;
    protected $modelECAC;
    protected $modelQuestionnaire;

    function __construct() {
        $this->modelUserLocality = new Model_UserLocality();
        $this->modelECAC = new Model_EnterpriseCategoryAwardCompetition();
        $this->modelQuestionnaire = new Model_Questionnaire();
    }

    public function subscriptionConfirmationCheck(){

        $isCandidate = Zend_Auth::getInstance()->getIdentity()->getRole() == 'coo-er-tiv-';
        $userId = Zend_Auth::getInstance()->getIdentity()->getUserId();

        if($isCandidate) {
            $userLocality = $this->modelUserLocality->getUserLocalityByUserId($userId);
            $enterpriseId = $userLocality->getEnterpriseId();
            $year = $this->modelQuestionnaire->getCurrentExecution()->getCompetitionId();

            $hasVerifiedECAC = $this->modelECAC->enterpriseHasVerifiedECAC($enterpriseId);
            $hasCurrentECAC = $this->modelECAC->getECACByEnterpriseIdAndYear($enterpriseId, $year) != null;

            return $hasCurrentECAC ? $hasVerifiedECAC : true;
        }

        return true;
    }

}   