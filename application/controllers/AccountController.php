<?php

class AccountController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_config = Zend_Registry::get('config');
        
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    public function indexAction()
    {
        $this->view->title = "cookingwithgas";
        $this->view->headTitle($this->view->title, 'PREPEND');
    }

    public function listAction()
    {
        $this->view->title = "list accounts";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if (!Application_Model_Account::is_authenticated ())
        {
            return;
        }
        $a = $_SESSION['account'];

        $this->view->accounts = Application_Model_Account::load_all ($a->get_id_account_group ());
    }

    public function confirmdeleteAction()
    {
        $this->view->title = "delete";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if ($id_account = $this->getRequest()->getParam('id_account'))
        {
            $this->view->account = Application_Model_Account::load ($id_account);
        }
    }

    public function deleteAction()
    {
        $acct = null;
        if ($id_account = $this->getRequest()->getParam('id_account'))
        {
            $acct = Application_Model_Account::load ($id_account);
        }
        if (!$acct)
        {
            return;
        }

        if (!Application_Model_Account::is_authenticated ($acct->get_id_account_group ()))
        {
            return;
        }

        $this->view->title = "delete";
        $this->view->headTitle($this->view->title, 'PREPEND');

        if ($this->_config->demo_mode)
        {
            $this->view->message = "Cannot delete a user in demo mode.";
            return;
        }


        if ($acct->delete ())
        {
            $redirector = $this->_helper->getHelper('Redirector');
            $redirector->gotoSimple('list', 'account');
            return;
        }
        else
        {
            $this->view->message = "Failed to delete account.";
        }

    }

    public function editAction ()
    {
        $this->view->title = "edit account";
        $this->view->headTitle($this->view->title, 'PREPEND');

        $form = new Application_Form_AccountEdit (true);
        $form->setAction($this->_helper->url('edit'));
        $this->view->form = $form;

        if (!($id = $this->getRequest()->getParam('id_account')))
        {
            return;
        }
        if (!($acct = Application_Model_Account::load ($id)))
        {
            return;
        }
        $this->view->account = $acct;

        if (!Application_Model_Account::is_authenticated ($acct->get_id_account_group()))
        {
            return;
        }

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data))
            {
                $a = $_SESSION['account'];
                $values = $form->getValues();
                $xary = array (
                    'id_account' => $values['id_account'],
                    'id_account_group' => $a->get_id_account_group (),
                    'fname' => $values['fname'],
                    'lname' => $values['lname'],
                    'email' => $values['email'],
                );
                if ($values['password'])
                {
                    $xary['password'] = $values['password'];
                }

                $o = Application_Model_Account::instantiate ($xary);

                if ($this->_config->demo_mode)
                {
                    $form->addError ("Cannot edit a user in demo mode.");
                    return;
                }

                if ($o->save ())
                {
                    $redirector = $this->_helper->getHelper('Redirector');
                    $redirector->gotoSimple('list', 'account');
                    return;
                }
            }
        }

        $xary = array (
            'id_account' => $id,
            'id_account_group' => $acct->get_id_account_group (),
            'fname' => $acct->get_fname (),
            'lname' => $acct->get_lname (),
            'email' => $acct->get_email (),
        );
        $form->setDefaults ($xary);
    }

    public function loginAction()
    {
        $this->view->title = "login";
        $this->view->headTitle($this->view->title, 'PREPEND');

        $form = new Application_Form_AccountLogin ();
        $form->setAction($this->_helper->url('login'));
        $this->view->form = $form;

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data))
            {
                if (($o = Application_Model_Account::load_by_email ($form->getValue ('email')))
                    && $o->get_confirmed ()
                    && $o->check_password ($form->getValue('password')))
                {
                    $_SESSION['account'] = $o;

                    // set a special "remember me cookie", good for one year
                    if (isset ($data['remember']) && $data['remember'])
                    {
                        $id = $o->get_id ();
                        $key = Application_Model_Account::generate_remember_key ($id);
                        $cname = $this->_config->account->remember->cookie_name;
                        $cpath = $this->_config->account->remember->cookie_path;
                        $clifespan = $this->_config->account->remember->cookie_lifespan;
                        setcookie ($cname, "$id:$key", time() + 86400 * $clifespan, $cpath);
                    }

                    $this->_redirector->gotoSimple('index', 'index');
                }
                else
                {
                    $form->addError("Invalid username/password.");
                }
            }
        }
    }


    public function changepasswordAction ()
    {
        $this->view->title = "change password";
        $this->view->headTitle($this->view->title, 'PREPEND');

        $form = new Application_Form_AccountChangePassword ();

        $form->setAction($this->_helper->url('changepassword'));
        $this->view->form = $form;

        if (!Application_Model_Account::is_authenticated ())
        {
            return;
        }

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            if ($form->isValid ($data))
            {
                $a = $_SESSION['account'];
                if (!$a->check_password ($form->getValue('old_password')))
                {
                    $form->addError ("Wrong password provided.");
                }

                if ($this->_config->demo_mode)
                {
                    $form->addError ("Cannot change password in demo mode.");
                    return;
                }

                $a->change_password ($form->getValue ('password'));
                $a->save ();

                $this->view->success = true;
            }
        }
    }


    public function manageAction()
    {
        $this->view->title = "manage accounts";
        $this->view->headTitle($this->view->title, 'PREPEND');
    }


    public function registerAction()
    {
        $form = new Application_Form_AccountEdit ();
        $form->setAction($this->_helper->url('register'));
        $this->view->form = $form;

        // are we adding a new user to an existing account group via the
        // add user form, or are we adding a brand new user via the registration
        // form?
        $new_registration = true;

        $data = $this->getRequest()->getParams();
        if (isset ($data['id_account_group']) && $data['id_account_group'])
        {
            $form->populate (array ('id_account_group' => $data['id_account_group']));

            $this->view->title = "add user";
            $form->save->setLabel('add user');

            $new_registration = false;
        }
        else
        {
            $this->view->title = "register";
            $form->save->setLabel('register');
        }

        $this->view->headTitle($this->view->title, 'PREPEND');

        if ($this->getRequest()->isPost())
        {
            // in order to save a new account via the registration
            // form, we must also create an account group; the registered
            // user can then add more accounts via the 'add' action
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data))
            {
                if ($this->_config->demo_mode)
                {
                    $form->addError ("Cannot register in demo mode.");
                    return;
                }

                try
                {
                    if (!($id_account_group = $form->getValue ('id_account_group')))
                    {
                        if (!$this->_config->open_registration)
                        {
                            $form->addError ("Open registration disabled.");
                            return;
                        }

                        $ag_data = array ();
                        $ag_data['email'] = $form->getValue ('email');
                        $ag = Application_Model_AccountGroup::instantiate ($ag_data);
                        $ag->save ();
                        $id_account_group = $ag->get_id ();
                    }

                    $a_data = array ();
                    $a_data['id_account_group'] = $id_account_group;
                    $a_data['lname'] = $form->getValue ('lname');
                    $a_data['fname'] = $form->getValue ('fname');
                    $a_data['email'] = $form->getValue ('email');
                    $a_data['password'] = $form->getValue ('password');

                    if ($new_registration)
                    {
                        $a_data['confirmation_key'] = uniqid ();
                    }
                    else
                    {
                        $a_data['confirmed'] = true;
                    }

                    $a = Application_Model_Account::instantiate ($a_data);

                    if ($a->save ())
                    {
                        if (!$new_registration)
                        {
                            $redirector = $this->_helper->getHelper('Redirector');
                            $redirector->gotoSimple('list', 'account');
                            return;
                        }
                        $this->view->email = $a->get_email();

                        $subject = $this->_config->account->register->email->subject;
                        $from = $this->_config->account->register->email->from;
                        $msg = $this->_config->account->register->email->msg;

                        $url = $this->_config->host_root;
                        $url .= $this->_helper->url('confirm');
                        $url .= "?id=" . $a->get_id ()
                                . "&key=" . $a->get_confirmation_key ();
                        $msg = "$msg\n\nClick this link to confirm your account:\n\n  "
                            . $url . "\n\n";

                        if (mail ($a->get_email (), $subject, $msg,
                            'From: ' . $from . "\r\n"))
                        {
                            $this->view->registration_success = true;
                        }
                        else
                        {
                            $form->addError ("Could not send confirmation e-mail");
                            $this->view->registration_success = false;
                        }
                    }

                }
                catch (Exception $e)
                {
                    $form->addError ($e->getMessage());
/*
                    if (preg_match ('#duplicate entry.+for key \'email\'#i', $e->getMessage()))
                    {
                        $form->addError("E-mail address is already in use.");
                    }
                    else
                    {
                        $form->addError ($e->getMessage());
                    }
*/
                    $form->populate($data);
                }

            }
        }
    }

    public function confirmAction()
    {
        // action body
        $this->view->title = "registration confirmation";
        $this->view->headTitle($this->view->title, 'PREPEND');
        
        $data = $this->getRequest()->getParams();
        
        if (!isset ($data['id']) || !$data['id']
            || !($o = Application_Model_Account::load ($data['id'])))
        {
            $this->view->confirmation_success = false;
            return;
        }

        if (!isset ($data['key']) || !$data['key']
            || ($o->get_confirmation_key() != $data['key']))
        {
            $this->view->confirmation_success = false;
            return;
        }

        try
        {
            $a = Application_Model_Account::instantiate (array ('confirmed' => true), $o->get_id ());
            $a->save ();
            $this->view->confirmation_success = true;
            $this->view->login_url .= $this->_helper->url('login');
        }
        catch (Exception $e)
        {
            $this->view->confirmation_success = false;
        }
    }

    public function logoutAction()
    {
        unset ($_SESSION['account']);

        $cname = $this->_config->account->remember->cookie_name;
        $cpath = $this->_config->account->remember->cookie_path;
        setcookie ($cname, "", time() - 3600, $cpath);

        $this->_redirector->gotoSimple('index', 'index');
    }
}
