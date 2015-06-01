<?php

class Model_ContactType
{
    function add($data)
    {
        $tbContactType = new DbTable_ContactType();
        $row = $tbContactType->createRow()
            ->setDescription($data['description']);
        $id = $row->save();       
        return $row;
    }
    function edit($data)
    {
        $tbContactType = new DbTable_ContactType();
        $row = $tbContactType->update(
                array('Description' => $data['description']), 
                array('id = ?' => $data['id'])
                );

        return $row;
    }
    
    function delete($id)
    {
        // fazer validação de relacionamento.
        $tbContactType = new DbTable_ContactType();
        $row = $tbContactType->find($id)
            ->current()
            ->delete();
        return $row;
    }
    
    function getContactTypes()
    {
        $tbContactTypes = new DbTable_ContactType();
        return $tbContactTypes->fetchAll();
    }
    
    function getContactType($Identify)
    {
        $tbContacttype = new DbTable_ContactType();
        $objResultContactType = $tbContacttype->fetchRow("Id = $Identify");
        return $objResultContactType;
    }

    
}