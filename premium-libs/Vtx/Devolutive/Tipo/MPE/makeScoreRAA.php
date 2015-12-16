<?php


/**
 * Description of makeScoreRAA
 *
 * @author vtx
 */
class Vtx_Devolutive_Tipo_MPE_makeScoreRAA 
{

    
    /**
     * 
     * 
     * @param type $questionnaireId
     * @param type $userId
     * @return boolean
     */
    public function makeScoreRAA($questionnaireId, $userId,$programaId)
    {    
        $this->Questionnaire = new Model_Questionnaire();
                
        
        $arrBlocksResult = $this->Questionnaire->getBlocksAutoavaliacao($questionnaireId);
        if (!$arrBlocksResult) {
            return false;
        }
            
        $governancaBlockId = $arrBlocksResult[0];
        $gestaoBlockId = $arrBlocksResult[1];

        $arrDataTab1 = $this->Questionnaire->getQuestionsPunctuationByBlock(
            $questionnaireId, $userId,$programaId, $governancaBlockId // questionario, usuario, primeiro bloco
        );

        $scorePart1 = 0;
        foreach($arrDataTab1 AS $dataTab1) {
            $scorePart1 = $scorePart1 + $dataTab1->getPontos();
        } 

        $arrDataTabs2 = $this->Questionnaire->getQuestionsPunctuationByBlock(
            $questionnaireId, $userId,$programaId, $gestaoBlockId // questionario, usuario, segundo bloco
        );

        $scorePart2 = 0;
        foreach($arrDataTabs2 AS $dataTabs2) {
            $scorePart2 = $scorePart2 + $dataTabs2->getPontos();
        } 

        $finalScore = ($scorePart1*0.25)+($scorePart2*0.75);
        return array(
            'IsgcScore' => $scorePart1,
            'IsgScore' => $scorePart2,
            'IsscFinalScore' => $finalScore
        );
    }
    
    
    
}

?>
