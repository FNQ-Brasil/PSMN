<?php
/**
 * 
 * Model_GroupEnterprise
 * @uses  
 * @author mcianci
 *
 */
class Model_GroupEnterprise
{
    
    public $dbTable_GroupEnterprise = "";

    public function __construct() 
    {
        $this->dbTable_GroupEnterprise = new DbTable_GroupEnterprise();
    }

    public function getAllGroupEnterpriseByGroupId($groupId,$count=null,$offset=null,$order=null,$filter=null)
    {
        $query = $this->dbTable_GroupEnterprise
                ->select()
                ->setIntegrityCheck(false);
        $query->from(
                array('GE' => 'GroupEnterprise'),
                array('GroupId','EnterpriseId',
                    new Zend_Db_Expr('(SELECT count(1) FROM GroupEnterprise as GE WHERE GE.GroupId = G.Id) as GroupToEnterprise')
                    ),
                null
            )
            ->join(
                array('G' => 'Group'), 
                'GE.GroupId = G.Id',
                array('Description','Name')
            )
            ->join(
                array('E' => 'Enterprise'), 
                'GE.EnterpriseId = E.Id',
                array('*')
            );
        
        if (!$order) {
            $order = 'Id ASC';
        }
        $query->where('GroupId = ?', $groupId);
        $query->order($order);
        
        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
    }

    public function deleteGroup($groupRow)
    {   
        DbTable_Group::getInstance()->getAdapter()->beginTransaction();
        try {
            
            $groupRow->delete();
            DbTable_Group::getInstance()->getAdapter()->commit();
            
            return array(
                'status' => true
            );
        } catch (Vtx_UserException $e) {
            DbTable_Group::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_Group::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
    public function setGroupToEnterprise($groupId,$enterpriseId,$checked)
    {
        DbTable_GroupEnterprise::getInstance()->getAdapter()->beginTransaction();
        try {
            
            if ( $checked == 0 ) {
                $whereDelete = array('EnterpriseId = ?' => $enterpriseId);
                DbTable_GroupEnterprise::getInstance()->delete($whereDelete);
            }

            if ( $checked == 1 ) {
                $groupEntRow = DbTable_GroupEnterprise::getInstance()->createRow()
                    ->setGroupId($groupId)
                    ->setEnterpriseId($enterpriseId);
                $groupEntRow->save();
            }

            DbTable_GroupEnterprise::getInstance()->getAdapter()->commit();

            return array(
                'status' => true
            );
        
        } catch (Vtx_UserException $e) {
            DbTable_GroupEnterprise::getInstance()->getAdapter()->rollBack();
            return array(
                'status' => false, 'messageError' => $e->getMessage()
            );
        } catch (Exception $e) {
            DbTable_GroupEnterprise::getInstance()->getAdapter()->rollBack();
            throw new Exception($e);
        }
    }
    
}