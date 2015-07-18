<?php
/**
 * 
 * Model_Group
 * @uses  
 * @author mcianci
 *
 */
class Model_Group
{
    
    public $dbTable_Group = "";

    public function __construct() 
    {
        $this->dbTable_Group = new DbTable_Group();
    }
    
    public function createGroup($data)
    {
        DbTable_Group::getInstance()->getAdapter()->beginTransaction();
        
        try {
            
            $data = $this->_filterInputGroup($data)->getUnescaped();
            
            $verifyName = DbTable_Group::getInstance()->fetchRow(array(
                'Name = ?' => $data['name']
            ));

            if ($verifyName) {
                return array(
                    'status' => false, 
                    'messageError' => 'O Grupo ('.$data['name'].') já esta uso.'
                    );
            }
            
            $row = $this->dbTable_Group->createRow()
                ->setName($data['name'])
                ->setDescription($data['description']);
            
            $row->save();
            
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
    
    public function updateGroup($groupRow,$data)
    {
        
        DbTable_Group::getInstance()->getAdapter()->beginTransaction();
        try {
            
            $data = $this->_filterInputGroup($data)->getUnescaped();

            $groupRow
                ->setName($data['name'])
                ->setDescription($data['description']);

            $groupRow->save();
        
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
    
    protected function _filterInputGroup($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                'name' => array(),
                'description' => array(),
            ),
            array( //validates
                'name' => array('NotEmpty', 'messages' => array('O Nome não pode ser vazio.')),
                'description' => array('NotEmpty', 'messages' => array('A Descrição não pode ser vazia.')),
            ),
            $params,
            array('presence' => 'required')
        );

        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }

        return $input;
    }
    
    public function getGroupById($id)
    {
        $where = null;
        if ($id) {
            $where = array('Id = ?' => $id);
        }
        return $this->dbTable_Group->fetchRow($where);
    }
    
    public function get($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_Group->fetchRow($where, $order, $count, $offset);
    }

    public function getAllGroups(
            
            $where=null,$order=null,$count=null,$offset=null,$filter = null)
    {
        $query = $this->dbTable_Group
                    ->select()
                    ->distinct()
                    ->setIntegrityCheck(false);
        $query->from(
                    array('G' => 'Group'), 
                    array('Id','Description','Name',
                    new Zend_Db_Expr('(SELECT count(1) FROM GroupEnterprise as GE WHERE GE.GroupId = G.Id) as GroupToEnterprise')
                    )
            );
        if ($where) {
            $query->where($where);
        }
        
        if (!$order) {
            $orderBy = 'Name ASC';
        }
        $query->order($orderBy);

        if (isset($filter['name']) && $filter['name']) {
            $query->where('Name LIKE (?)', '%'.$filter['name'].'%');
        }
        
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
    
}