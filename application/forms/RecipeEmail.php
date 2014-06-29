<?php

class Application_Form_RecipeEmail extends Application_Form_Recipe
{
    public function init()
    {
        $this->setName('account');
        $this->setMethod('post');

        $el = new Zend_Form_Element_Hidden('id_recipe');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Text('email');
        $el->setLabel('e-mail')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress')
              ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'e-mail');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Submit('submit');
        $el->setAttrib('id', 'submitbutton')
              ->setDecorators($this->button_decorators);
        $this->addElement ($el);
    }


}

