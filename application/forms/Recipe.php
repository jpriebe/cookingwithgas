<?php

class Application_Form_Recipe extends Zend_Form
{
    public $element_decorators = array(
        'ViewHelper',
        'Errors',
        array('Label', array ('class' => 'ui-hidden-accessible')),
    );

    public $button_decorators = array(
        'ViewHelper',
    );

    public $checkbox_decorators = array(
        'ViewHelper',
        'Errors',
        array('Label', array ('placement' => 'append')),
        array(array('fieldset' => 'HtmlTag'), array('tag' => 'fieldset', 'data-role' => 'controlgroup')),
    );

    public $multicheckbox_decorators = array(
        'ViewHelper',
        'Errors',
        array(array('fieldset' => 'HtmlTag'), array('tag' => 'fieldset', 'data-role' => 'controlgroup')),
    );

    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div')),
            'Form',
        ));
    }

}

