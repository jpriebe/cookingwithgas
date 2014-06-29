<?php

class Application_Form_RecipeImport extends Application_Form_Recipe
{

    public function init()
    {
        $this->setName('recipeimport');
        $this->setMethod('post');

        //$this->setAttrib('enctype', 'multipart/form-data');
        
        // would like to use a file upload here, but it doesn't seem to work
        // with JQuery Mobile, which wants to use AJAX to post everything
        $el = new Zend_Form_Element_Textarea('xml');
        $el->setLabel('xml')
               ->setRequired(true)
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('placeholder', 'paste XML here');
        $el->setAttrib('class', 'field');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Submit('submit'); 
        $el->setDecorators($this->button_decorators);
        $el->setAttrib('id', 'submitbutton'); 
        $this->addElement ($el);
    } 
}

