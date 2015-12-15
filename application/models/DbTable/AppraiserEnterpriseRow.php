<?php

class DbTable_AppraiserEnterpriseRow extends Vtx_Db_Table_Row_Abstract
{   
    public function getAnswers($etapa = 'estadual')
    {
		$tbApeEvaluation = DbTable_ApeEvaluation::getInstance();
        $select = $tbApeEvaluation->select()
            ->from(
                $tbApeEvaluation,
                array('AvaliacaoPerguntaId', 'Resposta', 'Linha1', 'Linha2')
            )
            ->where(
                'AppraiserEnterpriseId = ?', $this->getId()
            );
            //$sql = $select->__toString();
			//echo "$sql\n";exit;
        return $tbApeEvaluation->fetch($select, 'assoc');
    }
    
} 