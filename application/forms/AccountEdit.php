<?php

class Application_Form_AccountEdit extends Application_Form_Recipe
{
    private $_editing = false;

    public function __construct ($editing = false)
    {
        $this->_editing = $editing;

        parent::__construct ();
    }


    public function init()
    {
        $this->setName('account');
        $this->setMethod('post');
        
        $el = new Zend_Form_Element_Hidden('id_account');  
        $this->addElement ($el); 

        $el = new Zend_Form_Element_Hidden('id_account_group');  
        $this->addElement ($el); 

        $el = new Zend_Form_Element_Text('fname');  
        $el->setLabel('first name') 
               ->setRequired(true) 
               ->addFilter('StripTags') 
               ->addFilter('StringTrim') 
               ->addValidator('NotEmpty')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'first name');
        $this->addElement ($el); 
               
        $el = new Zend_Form_Element_Text('lname'); 
        $el->setLabel('last name') 
              ->setRequired(true) 
              ->addFilter('StripTags') 
              ->addFilter('StringTrim') 
              ->addValidator('NotEmpty')
              ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'last name');
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

        $el = new Zend_Form_Element_Password('password'); 
        $el->setLabel('password') 
              ->setRequired(!$this->_editing) 
              ->addFilter('StripTags') 
              ->addFilter('StringTrim')
              ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'password');
        $this->addElement ($el);

        $v = new Zend_Validate_Identical ($_POST['password']);
        $v->setMessage ("Passwords don't match", 'notSame');
        $el = new Zend_Form_Element_Password('password2'); 
        $el->setLabel('confirm password') 
              ->setRequired(!$this->_editing) 
              ->addFilter('StripTags') 
              ->addFilter('StringTrim')
              ->setDecorators($this->element_decorators)
              ->addValidator($v);  
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'confirm password');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Submit('save'); 
        $el->setLabel ('save')
            ->setValue ('save')
            ->setDecorators($this->button_decorators);
        $this->addElement ($el);
    } 
}

