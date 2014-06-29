<?php

class Application_Form_AccountChangePassword extends Application_Form_Recipe
{

    public function init()
    {
        $this->setName('change_password');
        $this->setMethod('post');
        
        $el = new Zend_Form_Element_Password('old_password'); 
        $el->setLabel('current password') 
              ->setRequired(true) 
              ->addFilter('StripTags') 
              ->addFilter('StringTrim')
              ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'current password');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Password('password'); 
        $el->setLabel('new password') 
              ->setRequired(true) 
              ->addFilter('StripTags') 
              ->addFilter('StringTrim')
              ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'new password');
        $this->addElement ($el);

        $v = new Zend_Validate_Identical ($_POST['password']);
        $v->setMessage ("Passwords don't match", 'notSame');
        $el = new Zend_Form_Element_Password('password2'); 
        $el->setLabel('confirm new password') 
              ->setRequired(true) 
              ->addFilter('StripTags') 
              ->addFilter('StringTrim')
              ->setDecorators($this->element_decorators)
              ->addValidator($v);  
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'confirm new password');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Submit('submit'); 
        $el->setDecorators($this->button_decorators);
        $el->setAttrib('id', 'submitbutton'); 
        $this->addElement ($el);
    } 
}

