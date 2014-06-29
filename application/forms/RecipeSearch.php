<?php

class Application_Form_RecipeSearch extends Application_Form_Recipe
{

    public function init()
    {
        $this->setName('search');
        $this->setMethod('post');

        $el = new Zend_Form_Element_Text('search_terms');
        $el->setLabel('search terms')
               ->setRequired(true)
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'search terms');
        $this->addElement ($el);

        $el = new Zend_Form_Element_MultiCheckbox('search_by', array(
            'multiOptions' => array(
                'title' => 'Title',
                'tags' => 'Tags',
                'ingredients' => 'Ingredients',
                'directions' => 'Directions',
                'source' => 'Source',
            )
        ));
        $el->setAttrib('class', 'field')
                ->setDecorators($this->multicheckbox_decorators);
        $el->helper = 'formMultiCheckboxNoLabels';
        $this->addElement ($el);

        $el = new Zend_Form_Element_Submit('submit', 'search'); 
        $el->setDecorators($this->button_decorators);
        $el->setAttrib('id', 'submitbutton'); 
        $this->addElement ($el);
    } 
}

