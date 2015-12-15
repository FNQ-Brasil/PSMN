<?php
/**
 * 
 * Model_Block
 * @uses  
 * @author mcianci
 *
 */
class Model_Position
{
    public $dbTable_Position = "";
    
    function __construct() {
        $this->dbTable_Position = new DbTable_Position();
    }
    
    function add($data)
    {
        $tbPosition = new DbTable_Position();
        $row = $tbPosition->createRow()
            ->setDescription($data['description']);
        $id = $row->save();       
        return $row;
    }
    function edit($data)
    {
        $tbPosition = new DbTable_Position();
        $row = $tbPosition->update(
                array('Description' => $data['description']), 
                array('id = ?' => $data['id'])
                );
        return $row;
    }
    
    function delete($id)
    {
        // fazer validação de relacionamento.
        $tbPosition = new DbTable_Position();
        $row = $tbPosition->find($id)
            ->current()
            ->delete();
        return $row;
    }
    
    function getPositions()
    {
        $tbPositions = new DbTable_Position();
        return $tbPositions->fetchAll();
    }
    
    function getPosition($Identify)
    {
        $tbPosition = new DbTable_Position();
        $objResultPosition = $tbPosition->fetchRow("Id = $Identify");
        return $objResultPosition;
    }
    
    public function getAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->dbTable_Position->fetchAll($where, $order, $count, $offset);
    }
    
}