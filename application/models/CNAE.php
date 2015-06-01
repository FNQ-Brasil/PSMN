<?php
/**
 * 
 * Model_CNAE
 *
 */
class Model_CNAE
{
    public $tbCNAE;

    public function __construct() {
        $this->tbCNAE = new DbTable_CNAE();
    }

    /**
     * 
     * - faz busca de CNAE
     * - utiliza fulltextsearch do MySQL no campo descricao da tabela CNAE
     * 
     * @param string $fullTextSearch
     */
    public function searchCNAE($fullTextSearch)
    {
        /**
            SELECT * FROM CNAE
            WHERE MATCH(descricao) AGAINST ('pesca')
            ;
        */

        $selectNumero = $this->tbCNAE->select()->where('numero = ?', $fullTextSearch);            
        $rowsNumero = $this->tbCNAE->fetchAll($selectNumero);  

        $selectDescricao = $this->tbCNAE->select()->where('MATCH (descricao) AGAINST (?)', $fullTextSearch);                 
        $rowsDescricao = $this->tbCNAE->fetchAll($selectDescricao);      
        
        $rows = array();
        
        if (count($rowsNumero) > 0) {
            $rows = $rowsNumero;
        }
        if (count($rowsDescricao) > 0) {
            $rows = $rowsDescricao;
        }
        
        //echo count($rows);
        
        return $rows;
    }    

}