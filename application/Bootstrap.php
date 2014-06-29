<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initConfig ()
    {
        // this gets the config into the registry, where controllers   
        // and models can access it...
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('config', $config);
    }

    protected function _initAutoload()
    {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath'  => APPLICATION_PATH));
        return $moduleLoader;
    }

    function _initViewHelpers()
    {
        Zend_Session::start ();

        // magical incantations to get the view so we can make some
        // application-wide settings for the title??? is this working???
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();

        $view->addHelperPath(APPLICATION_PATH . '/views/helpers', 'Recipe_View_Helper');

        $view->headTitle()->setSeparator(' - ');
        $view->headTitle('cookingwithgas');
    }
}

