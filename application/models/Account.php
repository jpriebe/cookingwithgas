<?php 
class Application_Model_Account extends Application_Model_RecipeObj 
{
    protected static $_table_name = 'account';
    protected static $_id_name = 'id_account';
    
    protected $_id_account_group;
    protected $_email;
    protected $_password;
    protected $_fname;
    protected $_lname;
    protected $_confirmed;
    protected $_confirmation_key;
    protected $_unlock_key;
    protected $_url_key;
    
    public function get_id_account_group () { return $this->_id_account_group; }    
    public function get_email () { return $this->_email; }
    public function get_fname () { return $this->_fname; }
    public function get_lname () { return $this->_lname; }

    public function get_confirmed () { return $this->_confirmed; }
    public function get_confirmation_key () { return $this->_confirmation_key; }
    public function get_unlock_key () { return $this->_unlock_key; }
    public function get_url_key () { return $this->_url_key; }

    public static function load ($id)
    {
        $o = Application_Model_RecipeObj::_load (new Application_Model_Account(), self::$_table_name, self::$_id_name, $id);

        $o->_confirmed = ($o->_confirmed == 1) ? true : false;

        return $o;
    }

    public static function instantiate ($params, $id=null)
    {
        if (isset ($params['password']))
        {
            $params['password'] = self::encrypt_password ($params['password']);
        }
        return Application_Model_RecipeObj::_instantiate (new Application_Model_Account(), $params, self::$_table_name, self::$_id_name, $id);
    }


    public function change_password ($new_password)
    {
        $this->_password = self::encrypt_password ($new_password);
    }

    protected function insert ()
    {
        return $this->_insert (self::$_table_name, self::$_id_name);
    }
    
    protected function update ()
    {
        return $this->_update (self::$_table_name, self::$_id_name);
    }

    public function delete ()
    {
        return $this->_delete (self::$_table_name, self::$_id_name);
    }

    protected function gather_data ()
    {
        return array (
            'id_account' => $this->_id,
            'id_account_group' => $this->_id_account_group,
            'email' => $this->_email,
            'password' => $this->_password,
            'fname' => $this->_fname,
            'lname' => $this->_lname,
            'confirmed' => ($this->_confirmed ? 1 : 0),
            'confirmation_key' => $this->_confirmation_key,
            'unlock_key' => $this->_unlock_key,
            'url_key' => $this->_url_key,
        );

    }
    
    /**
     * Checks whether a particular password matches the user's password
     *
     * @param string $test_password
     * @return bool true if a match, false otherwise
     */
    public function check_password ($test_password)
    {
        $test_password = self::encrypt_password($test_password);
        if ($test_password == $this->_password)
        {
            return true;
        }
        return false;
    }
    
    /**
     * Loads all accounts for a particular account group
     *
     * @param int $id_account_group
     * @return array array of Application_Model_Account objects
     */
    public static function load_all ($id_account_group)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = "SELECT " . self::$_id_name . " FROM " . self::$_table_name . " WHERE id_account_group = ?";

        $retval = array  ();
        $xary = $db->fetchAll($sql, $id_account_group);

        foreach ($xary as $row)
        {
            $o = self::load ($row[self::$_id_name]);
            if ($o)
            {
                $retval[] = $o;
            }
        }

        return $retval;
    }


    /**
     * Loads a given user by e-mail address
     *
     * @param string $email
     * @return Application_Model_Account
     */
    public static function load_by_email ($email)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $id = (int)$id;
        
        $sql = "SELECT " . self::$_id_name . " FROM " . self::$_table_name . " WHERE email = ?";
        if (!($xary = $db->fetchRow($sql, $email)))
        {
            return null;
        }
        
        if (!($o = self::load ($xary[self::$_id_name])))
        {
            return null;
        }
        
        return $o;
    }

    private static function encrypt_password ($pass)
    {
        return sha1 ($pass);
    }

    /**
     * Generates a "remember key" to be put into a cookie for a particular user account
     *
     * @param int $id_account
     * @return string
     */
    public static function generate_remember_key ($id_account)
    {
        $config = Zend_Registry::get('config');
        $secret_key = $config->account->remember->secret_key;

        $key = sha1 ($id_account . $secret_key);

        return $key;
    }

    /**
     * Checks whether a user is authenticated, either via an
     * active session or through a 'remember' cookie
     *
     * @param int $id_account_group - optionally checks the account group to make sure that the user has rights on content owned by this group
     * @return bool true if user is authenticated (and has access to the account group's content, if specified)
     */
    public static function is_authenticated ($id_account_group = null)
    {
        if (!isset ($_SESSION['account'])
            || !($_SESSION['account'] instanceof Application_Model_Account))
        {
            $config = Zend_Registry::get('config');
            $cname = $config->account->remember->cookie_name;
            if (isset ($_COOKIE[$cname]))
            {
                list ($id_account, $key) = split (':', $_COOKIE[$cname]);

                if (($key == self::generate_remember_key ($id_account))
                    && ($a = Application_Model_Account::load ($id_account)))
                {
                    $_SESSION['account'] = $a;
                }
                else
                {
                    $cpath = $config->account->remember->cookie_path;
                    setcookie ($cname, "", time() - 3600, $cpath);
                    return false;
                }
            }
            else
            {
                return false;
            }

        }

        if ($id_account_group === null)
        {
            return true;
        }
        
        $a = $_SESSION['account'];
        if ($a->get_id_account_group() != $id_account_group)
        {
            return false;
        }

        return true;
    }
} 
