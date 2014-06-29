<?php

class Application_Form_AccountLogin extends Application_Form_Recipe
{
    public function init()
    {
        $this->setName('account');
        $this->setMethod('post');

        $el = new Zend_Form_Element_Text('email');
        $el->setLabel('e-mail')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('EmailAddress')
              ->setDecorators($this->element_decorators);
        $el->setAttrib('placeholder', 'e-mail');
        $el->setAttrib('class', 'field');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Password('password');
        $el->setLabel('password')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'password');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Checkbox('remember');
        $el->setLabel('remember me')
                ->setAttrib('class', 'field')
                ->setDecorators($this->checkbox_decorators);
        $this->addElement ($el);

        $el = new Zend_Form_Element_Submit('submit');
        $el->setAttrib('id', 'submitbutton')
              ->setDecorators($this->button_decorators);
        $this->addElement ($el);
    }


}

