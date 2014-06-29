<?php

class Application_Form_RecipeEdit extends Application_Form_Recipe
{
    private $_num_tags = 5;

    public function get_num_tags ()
    {
        return $this->_num_tags;
    }

    public function __construct ($r = null)
    {
        if ($r != null)
        {
            $ntags = count ($r->get_tags ());

            if ($ntags + 3 > $this->_num_tags)
            {
                $this->_num_tags = $ntags + 3;
            }
        }

        parent::__construct ();
    }

    public function init()
    {
        $this->setName('recipeedit');
        $this->setMethod('post');

        //$this->setAttrib('enctype', 'multipart/form-data');

        $el = new Zend_Form_Element_Hidden('id_recipe');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Text('title');
        $el->setLabel('title')
               ->setRequired(true)
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'title');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Text('yield');
        $el->setLabel('yield')
               ->addFilter('StringTrim')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'yield');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Text('source');
        $el->setLabel('source')
               ->addFilter('StringTrim')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'source');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Textarea('ingredients');
        $el->setLabel('ingredients')
               ->setRequired(true)
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'ingredients');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Textarea('directions');
        $el->setLabel('directions')
               ->setRequired(true)
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'directions');
        $this->addElement ($el);

        $el = new Zend_Form_Element_Textarea('notes');
        $el->setLabel('notes')
               ->addFilter('StringTrim')
               ->setDecorators($this->element_decorators);
        $el->setAttrib('class', 'field');
        $el->setAttrib('placeholder', 'notes');
        $this->addElement ($el);

        $xary = array ();
        for ($i = 0; $i < $this->_num_tags; $i++)
        {
            $i1 = $i + 1;
            $el = new Zend_Form_Element_Text('tag' . $i1);
            $el->addFilter('StringTrim')
                    ->setDecorators($this->element_decorators);
            if ($i == 0)
            {
                $el->setLabel ('tags');
            }
            else
            {
                $el->setLabel ($i1);
            }
            $el->setAttrib('placeholder', 'tag ' . $i1);

            $el->setAttrib('class', 'field');
            $this->addElement ($el);

            $xary[] = 'tag' . $i1;
        }

        //$this->addDisplayGroup ($xary, 'tags');

        $el = new Zend_Form_Element_Submit('submit'); 
        $el->setDecorators($this->button_decorators)
            ->setLabel ('save')
            ->setAttrib('id', 'submitbutton'); 
        $this->addElement ($el);
    } 
}

