<?php

class DbTable_Alternative extends Vtx_Db_Table_Abstract
{
    protected $_name = 'Alternative';
    protected $_id = 'Id';
    protected $_sequence = true;

    protected $_referenceMap = array(
        'Question' => array(
          	'columns' => 'QuestionId',
            'refTableClass' => 'Question',
            'refColumns' => 'Id'
        ),
        'AlternativeType' => array(
          	'columns' => 'AlternativeTypeId',
            'refTableClass' => 'AlternativeType',
            'refColumns' => 'Id'
        ),
        'ParentAlternative' => array(
          	'columns' => 'ParentAlternativeId',
            'refTableClass' => 'Alternative',
            'refColumns' => 'Id'
        )
    );
    protected $_dependentTables = array(
        'DbTable_Alternative',
        'DbTable_AlternativeHistory',
        'DbTable_Answer'
    );

    public function getAnnualResultFields($alternativeId)
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
