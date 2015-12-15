<?php

class Model_Contact
{
    function add($data)
    {
        $tbContacts = new DbTable_Contact();
        $row = $tbContacts->createRow()
            ->setValue($data['description'])
            ->setPlus($data['plus'])
            ->setValidation($data['validation'])
            ->setContactTypeId($data['contact_type_id'])
            ->setEnterpriseId($data['cooperative_id'])
            ->setUserId($data['user_id']);
            
        $id = $row->save();
        
        return $row;
    }
    
    function getContacts()
    {
        $tbContact = new DbTable_Contact();
        return $tbContact->fetchAll();
    }
    
    function getContactTypes()
    {
        $tbContactTypes = new DbTable_ContactType();
        return $tbContactTypes->fetchAll();
    }
    
}