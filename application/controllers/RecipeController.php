<?php

class RecipeController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_config = Zend_Registry::get('config');
    }

    public function indexAction()
    {
        // action body
    }

    public function recentAction()
    {
        $this->view->title = "recent";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if (!($a = $_SESSION['account']))
        {
            return;
        }

        $xary = Application_Model_Recipe::load_recent ($a->get_id());

        $this->view->recipes = $xary;
    }

    public function browseAction()
    {
        $this->view->title = "browse";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if (!($a = $_SESSION['account']))
        {
            return;
        }

        $xary = Application_Model_Recipe::load_all ($a->get_id_account_group());

        $this->view->recipes = $xary;
    }

    public function browsebytagAction()
    {
        $this->view->title = "browse by tag";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if (!Application_Model_Account::is_authenticated ())
        {
            return;
        }
        $a = $_SESSION['account'];

        if ($id_tag = $this->getRequest()->getParam('id_tag'))
        {
            $xary = Application_Model_Recipe::load_all_by_tag ($id_tag, $a->get_id_account_group());
            $this->view->recipes = $xary;
            $this->view->viewmode = 'recipes';
        }
        else
        {
            $xary = Application_Model_Recipe::load_all_tags ($a->get_id_account_group());
            $this->view->tags = $xary;
            $this->view->viewmode = 'tags';
        }
    }

    public function searchAction()
    {
        $this->view->title = "search";
        $this->view->headTitle($this->view->title, 'PREPEND');

        $form = new Application_Form_RecipeSearch ();
        $form->setAction($this->_helper->url('search'));

        if (!Application_Model_Account::is_authenticated ())
        {
            return;
        }

        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();

            $valid = $form->isValid($postData);            
            if (!$valid) return;

            $values = $form->getValues();

            $a = $_SESSION['account'];
            $this->view->search_results = Application_Model_Recipe::search ($a->get_id_account_group(), $values['search_terms'], $values['search_by']);
        }
        else if ($search_by = $this->getRequest()->getParam('search_by'))
        {
            $this->view->form = $form;

            $form->setDefaults (array ('search_by' => split (',', $search_by)));
        }
        else
        {
            return;
        }
    }

    public function addAction()
    {
        $this->view->title = "add new";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if (!Application_Model_Account::is_authenticated ())
        {
            return;
        }

        $form = new Application_Form_RecipeEdit ();
        $form->setAction($this->_helper->url('add'));
        $this->view->form = $form;

        $a = $_SESSION['account'];
        $xary = Application_Model_Recipe::load_all_tags ($a->get_id_account_group());
        $this->view->tags = $xary;

        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();

            $valid = $form->isValid($postData);            
            if (!$valid) return;

            $values = $form->getValues();

            if ($this->_config->demo_mode)
            {
                $form->addError ("Cannot add a recipe in demo mode.");
                return;
            }

            if ($r = $this->save_recipe ($a, $values))
            {
                $redirector = $this->_helper->getHelper('Redirector');
                $redirector->gotoSimple('view', 'recipe', null, array ('id_recipe' => $r->get_id ()));
                return;
            }

            $form->addErrorMessage ("failed to save recipe");
        }

    }

    public function viewAction()
    {
        $this->view->title = "view";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if (!($id = $this->getRequest()->getParam('id_recipe')))
        {
            return;
        }
        if (!($r = Application_Model_Recipe::load ($id)))
        {
            return;
        }
        if (!Application_Model_Account::is_authenticated ($r->get_id_account_group ()))
        {
            return;
        }
        $a = $_SESSION['account'];

        $r->record_access ($a->get_id ());

        $this->view->recipe = $r;
    }

    public function printallAction()
    {
        if (!Application_Model_Account::is_authenticated ())
        {
            return;
        }
        $a = $_SESSION['account'];
        $xary = Application_Model_Recipe::load_all ($a->get_id_account_group());

        require_once ('recipe_card_generator.php');
        $g = new recipe_card_generator ();
        $pdf = $g->generate_pdf ($xary);
        $this->view->pdf = $pdf;
    }

    public function printAction()
    {
        $this->view->title = "view";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if (!($id = $this->getRequest()->getParam('id_recipe')))
        {
            return;
        }
        if (!($r = Application_Model_Recipe::load ($id)))
        {
            return;
        }
        if (!Application_Model_Account::is_authenticated ($r->get_id_account_group ()))
        {
            return;
        }

        require_once ('recipe_card_generator.php');
        $g = new recipe_card_generator ();
        $pdf = $g->generate_pdf (array ($r));
        $this->view->pdf = $pdf;
    }


    public function emailAction()
    {
        $this->view->title = "e-mail";
        $this->view->headTitle($this->view->title, 'PREPEND');
        
        if (!($id = $this->getRequest()->getParam('id_recipe')))
        {
            return;
        }
        if (!($r = Application_Model_Recipe::load ($id)))
        {
            return;
        }
        if (!Application_Model_Account::is_authenticated ($r->get_id_account_group ()))
        {
            return;
        }

        $form = new Application_Form_RecipeEmail ($r);
        $form->setAction($this->_helper->url('email'));
        $this->view->form = $form;

        $a = $_SESSION['account'];

        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();

            $valid = $form->isValid($postData);            
            if (!$valid) return;

            $values = $form->getValues();

            if ($this->_config->demo_mode)
            {
                $form->addError ("Cannot e-mail in demo mode.");
                return;
            }

            require_once ('recipe_mailer.php');
            if (recipe_mailer::send_recipe ($r, $a, $values['email']))
            {
                $redirector = $this->_helper->getHelper('Redirector');
                $redirector->gotoSimple('view', 'recipe', null, array ('id_recipe' => $r->get_id ()));
                return;
            }

            $form->addErrorMessage ("failed to send recipe");
        }

        $form->setDefaults (array ('id_recipe' => $id));
    }

    public function editAction()
    {
        $this->view->title = "edit";
        $this->view->headTitle($this->view->title, 'PREPEND');
        
        if (!($id = $this->getRequest()->getParam('id_recipe')))
        {
            return;
        }
        if (!($r = Application_Model_Recipe::load ($id)))
        {
            return;
        }
        if (!Application_Model_Account::is_authenticated ($r->get_id_account_group ()))
        {
            return;
        }

        $a = $_SESSION['account'];

        $r->record_access ($a->get_id ());

        $form = new Application_Form_RecipeEdit ($r);
        $form->setAction($this->_helper->url('edit'));
        $this->view->form = $form;

        $xary = Application_Model_Recipe::load_all_tags ($a->get_id_account_group());
        $this->view->tags = $xary;
        $this->view->recipe = $r;

        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();

            $valid = $form->isValid($postData);            
            if (!$valid) return;

            $values = $form->getValues();

            if ($this->_config->demo_mode)
            {
                $form->addError ("Cannot edit a recipe in demo mode.");
                return;
            }

            if ($r = $this->save_recipe ($a, $values))
            {
                $redirector = $this->_helper->getHelper('Redirector');
                $redirector->gotoSimple('view', 'recipe', null, array ('id_recipe' => $r->get_id ()));
                return;
            }

            $form->addErrorMessage ("failed to save recipe");
        }

        $xary = array (
            'id_recipe' => $r->get_id (),
            'title' => $r->get_title (),
            'source' => $r->get_source (),
            'yield' => $r->get_yield (),
            'ingredients' => $r->get_ingredients (),
            'directions' => $r->get_directions (),
            'notes' => $r->get_notes (),
        );

        $i = 0;
        foreach ($r->get_tags() as $t)
        {
            $i++;
            $xary["tag$i"] = $t->get_tag ();
        }

        $form->setDefaults ($xary);
    }

    private function save_recipe ($a, $values)
    {
        $tags = array ();
        $i = 0;
        while (true)
        {
            $i++;
            if (!isset ($values['tag' . $i]))
            {
                break;
            }
            if ($values['tag' . $i])
            {
                $tags[] = $values['tag' . $i];
            }
        }

        $o = Application_Model_Recipe::instantiate (array (
            'id_recipe' => $values['id_recipe'],
            'id_account_group' => $a->get_id_account_group (),
            'title' => $values['title'],
            'ingredients' => $values['ingredients'],
            'directions' => $values['directions'],
            'yield' => $values['yield'],
            'source' => $values['source'],
            'notes' => $values['notes'],
            'tags' => $tags,
        ));

        if (!$o->save ())
        {
            return false;
        }

        return $o;
    }

    public function confirmdeleteAction()
    {
        $this->view->title = "delete";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if ($id_recipe = $this->getRequest()->getParam('id_recipe'))
        {
            $this->view->recipe = Application_Model_Recipe::load ($id_recipe);
        }
    }

    public function deleteAction()
    {
        $r = null;
        if ($id_recipe = $this->getRequest()->getParam('id_recipe'))
        {
            $r = Application_Model_Recipe::load ($id_recipe);
        }
        if (!$r)
        {
            return;
        }

        if (!Application_Model_Account::is_authenticated ($r->get_id_account_group ()))
        {
            return;
        }

        $this->view->title = "delete";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if ($this->_config->demo_mode)
        {
            $this->view->message = "Cannot delete a recipe in demo mode.";
            return;
        }

        if ($r->delete ())
        {
            $this->view->message = "Successfully deleted.";
        }
        else
        {
            $this->view->message = "Failed to delete recipe.";
        }

    }

    public function importAction()
    {
        if (!Application_Model_Account::is_authenticated ())
        {
            return;
        }

        $this->view->title = "import";
        $this->view->headTitle($this->view->title, 'PREPEND');

        $form = new Application_Form_RecipeImport ();
        $form->setAction($this->_helper->url('import'));
        $this->view->form = $form;

        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $valid = $form->isValid($postData);            
            if (!$valid) return;

            if ($this->_config->demo_mode)
            {
                $form->addError ("Cannot import in demo mode.");
                return;
            }

            $values = $form->getValues();

            try 
            {
                libxml_use_internal_errors (true);
                $xml = new SimpleXMLElement ($values['xml']);
            }
            catch (Exception $e)
            {
                $form->addErrorMessage ($e->getMessage ());
                return;
            }

            $count = 0;
            foreach ($xml->recipe as $r)
            {
                $tags = array ();
                foreach ($r->tags->tag as $t)
                {
                    $tags[] = (string)($t);
                }
                $o = Application_Model_Recipe::instantiate (array (
                    'id_account_group' => $_SESSION['account']->get_id_account_group (),
                    'title' => (string)($r->title),
                    'source' => (string)($r->source),
                    'yield' => (string)($r->yield),
                    'ingredients' => (string)($r->ingredients),
                    'directions' => (string)($r->directions),
                    'notes' => (string)($r->notes),
                    'tags' => $tags,
                ));
                if (!$o->save ())
                {
                    $form->addErrorMessage ("failed to save recipe '" . $r->title . "'.");
                    return;
                }
                $count++;
            }

            $this->view->success_message = "Imported $count "
                . (($count == 1) ? 'record' : 'records')
                . '.';
        }
    }

    public function exportAction()
    {
        if (!($a = $_SESSION['account']))
        {
            return;
        }

        $xary = Application_Model_Recipe::load_all ($a->get_id_account_group());

        $xml = new SimpleXMLElement ('<?xml version="1.0" encoding="utf-8"?><recipes />');

        foreach ($xary as $r)
        {
            $rnew = $xml->addChild ('recipe');
            $rnew->addChild ('title', self::xmlsc ((string)$r->get_title ()));
            $rnew->addChild ('source', self::xmlsc ((string)$r->get_source ()));
            $rnew->addChild ('yield', self::xmlsc ((string)$r->get_yield ()));
            $rnew->addChild ('ingredients', self::xmlsc ((string)$r->get_ingredients ()));
            $rnew->addChild ('directions', self::xmlsc ((string)$r->get_directions ()));
            $rnew->addChild ('notes', self::xmlsc ((string)$r->get_notes ()));

            $tnew = $rnew->addChild ('tags');

            foreach ($r->get_tags () as $t)
            {
                $tnew->addChild ('tag', self::xmlsc ((string)$t->get_tag ()));
            }
        }

        header ('Content-type: text/xml');
        print $xml->asXML();
        exit;
    }

    private static function xmlsc ($str)
    {
        return str_replace('&#039;', '&apos;', htmlspecialchars($str, ENT_QUOTES));
    }


}

