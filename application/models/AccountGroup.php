<?php 
class Application_Model_AccountGroup extends Application_Model_RecipeObj 
{
    protected static $_table_name = 'account_group';
    protected static $_id_name = 'id_account_group';
    
    protected $_email;

    public function get_email () { return $this->_email; }

    public static function load ($id)
    {
        $o = Application_Model_RecipeObj::_load (new Application_Model_AccountGroup(), self::$_table_name, self::$_id_name, $id);
        return $o;
    }

    public static function instantiate ($params, $id=null)
    {
        return Application_Model_RecipeObj::_instantiate (new Application_Model_AccountGroup(), $params, self::$_table_name, self::$_id_name, $id);
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
            'id_account_group' => $this->_id,
            'email' => $this->_email,
        );
    }
} 
