<?php

class DbTable_President extends Vtx_Db_Table_Abstract
{
    protected $_name = 'President';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Enterprise' => array(
            'columns' => 'EnterpriseId',
            'refTableClass' => 'Enterprise',
            'refColumns' => 'Id'
        )
    );
    
    
    public function migrateUser($fetch = 'all') {
            set_time_limit(0);
            $query = $this->select()
            ->distinct()
            ->setIntegrityCheck(false)
            ->from(
                array('P' => 'President'),
                array('EnterpriseId','Name','NickName','Cpf','Email')
            )
            ->join(
                array('S' => 'Senhas'), 
                'P.EnterpriseId = S.EnterpriseId',
                array('Password')
            );
            $i = array();
            for ($index = 1; $index <= 1; $index++) {
                $i[$index] = $index;
            }
            $query
            ->where("P.EnterpriseId in (?)", array($i));

            
       /*
        echo '<pre>';
        echo $query; 
        die;
         
        */
        $objResult = $this->fetch($query, $fetch);
		return $objResult;
        
        
    }
}
