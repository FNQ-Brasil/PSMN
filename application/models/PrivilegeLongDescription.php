<?php
/**
 * 
 * Model_PrivilegeLongDescription
 * @uses  
 *
 */
class Model_PrivilegeLongDescription
{
    private $tbPrivilegeLongDescription = "";

    function __construct() {
        $this->tbPrivilegeLongDescription = new DbTable_PrivilegeLongDescription();
    }
    
    function getAll()
    {
        return $this->tbPrivilegeLongDescription->fetchAll();
    }

    function getAnswerFeedbackById($Id)
    {
        $objResult = $this->tbPrivilegeLongDescription->fetchRow(array('Id = ?' => $Id));
        return $objResult;
    }

}