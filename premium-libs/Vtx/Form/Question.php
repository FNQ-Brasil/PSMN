<?php

/**
 * 
 * Vtx_Form_Question 
 * @uses 
 * @author mmcianci
 *
 * Manter somente validações, codigos html somente na view.
 */
    class Vtx_Form_Question extends Zend_Form 
    {
        public function init()
        {  
            $this->setName('question');
            
            $designation = new Zend_Form_Element_Select('designation');
            $designation->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty');
            $this->addElement($designation);

            $question_type_id = new Zend_Form_Element_Select('question_type_id');
            $question_type_id->setRequired(true)
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
            $this->addElement($question_type_id);

            $value = new Zend_Form_Element_Text('value');
            $value->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty');
            $this->addElement($value);

            $supporting_text = new Zend_Form_Element_Text('supporting_text');
            $supporting_text->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');
            $this->addElement($supporting_text);

        }

        public function isValid($data)
        {
          if( isset($data['designation']) && !empty($data['designation']) )
          {
              $this->getElement($data['designation'])
                    ->setRequired(true)
                    ->addValidator('NotEmpty');
          }
          
          if( isset($data['question_type_id']) && !empty($data['question_type_id']) )
          {
              $this->getElement($data['question_type_id']);
                    //->addValidator('NotEmpty');
          }
          
          if( isset($data['value']) && !empty($data['value']) )
          {
              $this->getElement($data['value'])
                    ->setRequired(true)
                    ->addValidator('NotEmpty');
          }
          
          if( isset($data['supporting_text']) && !empty($data['supporting_text']) )
          {
              $this->getElement($data['supporting_text'])
                    ->setRequired(true)
                    ->addValidator('NotEmpty');
          }
          
          return parent::isValid($data);
        }
    }