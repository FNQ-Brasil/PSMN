<?php
/**
 * 
 * Model_BlockEnterpreneurGrade
 *
 */
class Model_BlockEnterpreneurGrade
{

    public $tbBlockEnterpreneurGrade = "";
    
    function __construct() {
        $this->tbBlockEnterpreneurGrade = new DbTable_BlockEnterpreneurGrade();
    }

    function getBlockById($Id)
    {
        return $this->tbBlockEnterpreneurGrade->fetchRow(array('Id = ?' => $Id));
    }

    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
      
        
        return $this->tbBlockEnterpreneurGrade->fetchAll($where, $order, $count, $offset);
    }

    
   /**
    * Executa procedure que calcula Pontuacao Caracteristica Empreendedora
    * 
    * @param type $QuestionnaireId
    * @param type $BlockId
    * @param type $UserId
    * @param type $CompetitionId
    */
   public function execProcPontuacaoGrade($QuestionnaireId, $BlockId, $UserId, $CompetitionId  )
   {
       
       $res = $this->getAll(array('UserId' => 2));
      
       
//       $query = " 
//                    call p_pontuacao_grade ( ".$QuestionnaireId.", #50
//                                             ".$BlockId.",  # 60
//                                             ".$UserId.",  #UserId eh diferente de EnterpriseId # 2
//                                             ".$CompetitionId." #2013
//                                            ) 
//                ";
//       
//       $res = $this->tbBlockEnterpreneurGrade->query($query);
       
       return $res;
       
       //var_dump ($res);       
   }
    
   
//    public function getId($limit = 100, $Id)
//    {
//        
//        $query = $this->select()
//            ->from(
//                array('beg' => 'BlockEnterpreneurGrade')
//            )
//            ->where('beg.Id = ?', $Id)
//            ->limit($limit) ;
//        
//        $objResult = $this->fetchAll($query);
//	
//        return $objResult;
//                
//    }    
    
    /**
     * 
     * @param array $data
     * @return boolean
     */
    public function saveUpdateGrade(Array $data)
    {
        
        try {
            $grade = $this->fetchRow(array(
                                          'Id = ?' => $data['Id'], 
                                          'EnterpreneurFeatureId = ?' => $data['EnterpreneurFeatureId'], 
                                          'UserId = ?' => $data['UserId'], 
                                          'QuestionnaireId = ?' => $data['QuestionnaireId'], 
                                          'BlockId = ?' => $data['BlockId']
                                    ));

            $grade->setEnterpreneurFeatureId($data['EnterpreneurFeatureId']); 
            $grade->setDescription($data['Description']); 
            $grade->setPoints($data['Points']);
            $grade->setUpdatedAtd(date('Y-m-d H:i:s'));

            $grade->save();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }    
    
    
    public function saveInsertGrade($data) 
    {
        
        DbTable_BlockEnterpreneurGrade::getInstance()->getAdapter()->beginTransaction();
        
        try{
              
                $grade = DbTable_BlockEnterpreneurGrade::getInstance()->createRow()
                    ->setEnterpreneurFeatureId($data['EnterpreneurFeatureId'])
                    ->setCompetitionId($data['CompetitionId'])
                    ->setUserId($data['UserId'])
                    ->setQuestionnaireId($data['QuestionnaireId'])
                    ->setBlockId($data['BlockId'])
                    ->setDescription($data['Description'])
                    ->setPoints($data['Points']);
                    
                $grade->save();
          
            DbTable_Resource::getInstance()->getAdapter()->commit();
            return $grade;

        } catch (Exception $e) {
            DbTable_Resource::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
    
        }
        
        return true;
    }//end function
    

}