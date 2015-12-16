<?php

class DbTable_AppraiserUser extends Vtx_Db_Table_Abstract {
    protected $_name = 'AppraiserUser';
    protected $_id = 'Id';
    protected $_sequence = true;

    public function createAppraiserUser($userId, $status, $responsibleId){
        $appraiserUser = $this->createRow()
            ->setUserId($userId)
            ->setStatus($status)
            ->setResponsibleId($responsibleId)
            ->setCreatedAt(new Zend_Db_Expr('NOW()'))
            ->setUpdatedAt(new Zend_Db_Expr('NOW()'));

        $appraiserUser->save();
        return $appraiserUser;
    }

    public function updateAppraiserUser($appraiserUser, $status, $responsibleId){
        $appraiserUser
            ->setStatus($status)
            ->setResponsibleId($responsibleId)
            ->setUpdatedAt(new Zend_Db_Expr('NOW()'));

        $appraiserUser->save();
        return $appraiserUser;
    }

    public function getAllBy($userName=null, $userLogin=null, $userCpf=null, $userRole=null, $appraiserUf=null, $appraiserStatus=null, $regionalId=null){
        if(!$userRole) $userRole = array(29,35);

        $ufCaseExpr = new Zend_Db_Expr("
            case
              when SaNeighborhoodState.Id is not null then SaNeighborhoodState.Uf
              when SaNeighborhoodState.Id is null and SaCityState.Id is not null then SaCityState.Uf
              when SaNeighborhoodState.Id is null and SaCityState.Id is null and SaState.Id is not null then SaState.Uf
            end as Uf
        ");

        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                'User',
                array('Id','FirstName','Surname','Login','Cpf',$ufCaseExpr))
            ->joinInner(
                'User_Role',
                'User_Role.UserId = User.Id',
                array())
            ->joinInner(
                'Role',
                'Role.Id = User_Role.RoleId',
                array('Role' => 'LongDescription'))
            ->joinLeft(
                'AppraiserUser',
                'AppraiserUser.UserId = User.Id',
                array('Status','UpdatedAt'))
            ->joinLeft(
                array('Responsible' => 'User'),
                'Responsible.Id = AppraiserUser.ResponsibleId',
                array('ResponsibleFirstName' => 'FirstName', 'ResponsibleSurname' => 'Surname'))
            ->joinLeft(
                'UserLocality',
                'UserLocality.UserId = User.Id',
                array())
            ->joinLeft(
                'Regional',
                'Regional.Id = UserLocality.RegionalId',
                array())
            ->joinLeft(
                'ServiceArea',
                'ServiceArea.RegionalId = Regional.Id',
                array())
            ->joinLeft(
                array('SaState' => 'State'),
                'SaState.Id = ServiceArea.StateId',
                array())
            ->joinLeft(
                array('SaCity' => 'City'),
                'SaCity.Id = ServiceArea.CityId',
                array())
            ->joinLeft(
                array('SaNeighborhood' => 'Neighborhood'),
                'SaNeighborhood.Id = ServiceArea.NeighborhoodId',
                array())
            ->joinLeft(
                array('SaCityState' => 'State'),
                'SaCityState.Id = SaCity.StateId',
                array())
            ->joinLeft(
                array('SaNeighborhoodCity' => 'City'),
                'SaNeighborhoodCity.Id = SaNeighborhood.CityId',
                array())
            ->joinLeft(
                array('SaNeighborhoodState' => 'State'),
                'SaNeighborhoodState.Id = SaNeighborhoodCity.StateId',
                array())
            ->group('User.Id')
        ;

        if($userName) $query->where("concat(User.FirstName,' ',User.Surname) like ?", "%$userName%");
        if($userLogin) $query->where("User.Login like ?", "%$userLogin%");
        if($userCpf) $query->where("User.Cpf like ?", "%$userCpf%");
        if($userRole) $query->where("User_Role.RoleId in (?)", $userRole);
        if($appraiserUf) $query->where("(SaNeighborhoodState.Id = ? or SaCityState.Id = ? or SaState.Id = ?)", $appraiserUf);

        if($appraiserStatus){
            $extraCondition = ($appraiserStatus == 'unable') ? 'OR AppraiserUser.Status is NULL' : '';
            $query->where("AppraiserUser.Status = ? $extraCondition", $appraiserStatus);
        }

        $query->order('User.FirstName ASC')->order('User.Surname ASC');

        return $query;
    }

    public function getByUserId($user_id){
        $query = $this->select()->from('AppraiserUser')->where('UserId = ?', $user_id);
        return $this->fetchRow($query);
    }
}