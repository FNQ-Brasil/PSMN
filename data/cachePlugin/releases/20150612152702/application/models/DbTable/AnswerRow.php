<?php

class DbTable_AnswerRow extends Vtx_Db_Table_Row_Abstract
{
    public function findParentQuestion()
    {
        return $this->findParentAlternative()->findParentQuestion();
    }

    public function findParentEnterprise()
    {
        return $this->findParentUser()->findParentUserLocality()->findParentEnterprise();
    }
}