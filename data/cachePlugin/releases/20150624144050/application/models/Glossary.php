<?php
/**
 * 
 * Model_Glossary
 * @uses  
 * @author gersonlv
 *
 */
class Model_Glossary
{
    public $dbTable_Glossary = "";
    
    function __construct() {
        $this->dbTable_Glossary = new DbTable_Glossary();
    }
    
    function add($data)
    {
        $tbGlossary = new DbTable_Glossary();
        $data = $this->_filterInputQuestion($data)->getUnescaped();
        $row = $tbGlossary->createRow()
            ->setTerm($data['term'])
            ->setDescription($data['description'])
            ->setTermLen(strlen($data['term']));
        $id = $row->save();       
        return $row;
    }
    function edit($data)
    {
        $dataId = $data['id'];
        $tbGlossary = new DbTable_Glossary();
        $data = $this->_filterInputQuestion($data)->getUnescaped();
        
        $row = $tbGlossary->update(
            array(
                'Term' => $data['term'],
                'Description' => $data['description'],
                'TermLen' => strlen($data['term'])
            ), 
            array('Id = ?' => $dataId)
        );
        return $row;
    }
    
    function delete($id)
    {
        // fazer validação de relacionamento.
        $tbGlossary = new DbTable_Glossary();
        $row = $tbGlossary->find($id)
            ->current()
            ->delete();
        return $row;
    }
    
    function getGlossaryById($Identify)
    {
        $tbGlossary = new DbTable_Glossary();
        $objResultGlossary = $tbGlossary->fetchRow(array('Id = ?' => $Identify));
        return $objResultGlossary;
    }
    
    function getGlossaryRowById($Identify)
    {
        $tbGlossary = new DbTable_Glossary();
        $objResultGlossary = $tbGlossary->fetchRow(array('Id = ?' => $Identify));
        return $objResultGlossary;
    }

    function getAll($where = null, $orderBy = null, $count = null, $offset = null, $filter = null)
    {
        $query = $this->dbTable_Glossary->select();
        
        if ($where) {
            $query->where($where);
        }
        
        if (!$orderBy) {
            $orderBy = 'Term ASC';
        }
        $query->order($orderBy);

        if (isset($filter['term']) && $filter['term']) {
            $query->where('Term LIKE (?)', '%'.$filter['term'].'%');
        }
        return Zend_Paginator::factory($query)
            ->setItemCountPerPage($count? $count : null)
            ->setCurrentPageNumber($offset? $offset: 1);
        //return $this->dbTable_User->fetchAll($where = null, $order = null, $count = null, $offset = null);
    }
    
    protected function _filterInputQuestion($params)
    {
        $input = new Zend_Filter_Input(
            array( //filters
                '*' => array('StripTags', 'StringTrim'),
                'term' => array(
                    array('Alnum', array('allowwhitespace' => true))
                ),
                'description' => array(array('HtmlEntities')),
            ),
            array( //validates
                'term' => array('NotEmpty'),
                'description' => array('NotEmpty'),
            ),
            $params,
            array('presence' => 'required')
        );

        if ($input->hasInvalid() || $input->hasMissing()) {
            throw new Vtx_UserException(
                Model_ErrorMessage::getFirstMessage($input->getMessages())
            );
        }

        return $input;
    }
    
    public function getHtmlWord($text)
    {
        $glossaries = $this->getAll(null, 'TermLen DESC');
        $terms = array();
        $glossaryDescriptions = '';
        
        $replaceCallback = function($matches) {
            global $glossaryId;
            $expression = '';
            $words = explode(' ', $matches[0]);
            foreach ($words as $word) {
                $expression .= ' ' . $word[0] . '<i></i>' . substr($word, 1);
            }
            return  "<a href=\"#\" title=\"{$glossaryId}\" class=\"tp\" onclick=\"return false\">"
                . trim($expression) .
                "</a>";
        };
        
        foreach ($glossaries as $glossary) {
            $word = $glossary->getTerm();

            $expr = "|($word)|Ui";
            if (preg_match($expr, $text)) {
                global $glossaryId;
                $glossaryId = $glossary->getId();
                $description = $glossary->getDescription();
                $text = preg_replace_callback($expr, $replaceCallback, $text);
                $glossaryDescriptions .= '<div id="gloss_' . $glossaryId . '" class="hide">' . $description .'</div>';
            }
        }
        return $text . $glossaryDescriptions;   
    }

    public function highlight($txt,$words){
        if (!is_array($words))
            $words=preg_split("/[^[:alpha:]]+/",$words);
        $words=array_unique($words);
        $repl=array();
        for ($i=0; $i<sizeof($words); ++$i)
            $repl[$i]="<span class=\"hi\">".$words[$i]."</span>";
        return str_ireplace($words,$repl,$txt);
    }
    
    /*
public function highlight($haystack, $needle) {
     if (strlen($haystack) < 1 || strlen($needle) < 1) {return $haystack;}
    preg_match_all("/$needle+/i", $haystack, $match);
    $exploded = preg_split("/$needle+/i",$haystack);
    $replaced = "";
    foreach($exploded as $e)
            foreach($match as $m)
            if($e!=$exploded[count($exploded)-1]) {$replaced .= $e . "<font style=\"background-color:yellow\">" . $m[0] . "</font>";} else {$replaced .= $e;}
    return $replaced;
}    */
}