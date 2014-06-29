<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if (!Application_Model_Account::is_authenticated ())
        {
            $redirector = $this->_helper->getHelper('Redirector');
            $redirector->gotoSimple('index', 'account');
            return;
        }

        $this->view->title = "";
        $this->view->headTitle($this->view->title, 'PREPEND');
    }

    public function utilsAction()
    {
        if (!Application_Model_Account::is_authenticated ())
        {
            $redirector = $this->_helper->getHelper('Redirector');
            $redirector->gotoSimple('index', 'account');
            return;
        }

        $this->view->title = "utils";
        $this->view->headTitle($this->view->title, 'PREPEND');
    }


}

