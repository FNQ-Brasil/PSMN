<?php

class Model_AppraiserUser
{
    protected $table;

    function __construct() {
        $this->table = new DbTable_AppraiserUser();
    }

    public function changeStatus($userId, $status, $responsibleId){
        $this->checkAppraiserEnterpriseCount($status,$userId);

        $appraiserUser = $this->table->getByUserId($userId);
        if($appraiserUser){
            $appraiserUser = $this->table->updateAppraiserUser($appraiserUser, $status, $responsibleId);
        } else {
            $appraiserUser = $this->table->createAppraiserUser($userId, $status, $responsibleId);
        }
        return $appraiserUser;
    }

    private function checkAppraiserEnterpriseCount($status, $userId){
        if($status != 'able') {
            $appraiserEnterprises = DbTable_AppraiserEnterprise::getInstance()->getByUserIdAndProgramaId($userId,date('Y'));
            if(count($appraiserEnterprises) > 0) throw new Exception('o avaliador já possui empresas associadas');
        }
    }

    public function getAllBy($userName=null, $userLogin=null, $userCpf=null, $userRole=null, $appraiserUf=null,
                             $appraiserStatus=null, $regionalId=null, $limit=null, $page=1){

        $query = $this->table->getAllBy($userName, $userLogin, $userCpf, $userRole, $appraiserUf, $appraiserStatus, $regionalId);
        return Zend_Paginator::factory($query)->setItemCountPerPage($limit)->setCurrentPageNumber($page);
    }

    public function getStatuses(){
        return array(
            'able' => 'Aprovado',
            'unable' => 'Reprovado'
        );
    }
}