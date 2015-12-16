<?php

class DbTable_AnswerAnnualResult extends Vtx_Db_Table_Abstract
{
    protected $_name = 'AnswerAnnualResult';
    protected $_id = 'Id';
    protected $_sequence = true;
    
    protected $_referenceMap = array(
	     'AnnualResult' => array(
             'columns' => array('AnnualResultId'),
             'refTableClass' => 'AnnualResult',
             'refColumns' => array('Id')
         ),
         'AnnualResultData' => array(
             'columns' => array('AnnualResultDataId'),
             'refTableClass' => 'AnnualResultData',
             'refColumns' => array('Id')
         )
    );
    protected $_dependentTables = array(
    );

    public function getAnswerAnnualResult($alternativeId, $userId)
    {
        $query = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('ARD' => 'AnnualResultData'),
                array('Year')
            )
            ->where('ARD.AlternativeId = ?', $alternativeId)
            ->order('AAN.Year');
                
        return $this->fetchRow($query);
    }
}