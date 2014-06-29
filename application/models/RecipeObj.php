<?php
abstract class Application_Model_RecipeObj
{
    protected static $_db = null;

    protected static $_table_name = '';
    protected static $_id_name = '';

    protected $_id = null;

    public function get_id () { return $this->_id; }

    abstract protected function gather_data ();
    abstract protected function insert ();
    abstract protected function update ();
    abstract public function delete ();

    public static function load ($id) {}
    public static function instantiate ($params, $id=null) {}

    protected static function _load ($o, $table_name, $id_name, $id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $id = (int)$id;

        $sql = "SELECT * FROM " . $table_name . " WHERE " . $id_name . " = ?";
        if (!$xary = $db->fetchRow ($sql, $id))
        {
            return null;
        }

        self::apply_params ($o, $id_name, $xary);

        return $o;
    }


    protected static function _instantiate ($o, $params, $table_name, $id_name, $id)
    {
        if ($id)
        {
            $o = self::_load ($o, $table_name, $id_name, $id);

            if (!$o)
            {
                return null;
            }
        }

        self::apply_params ($o, $id_name, $params);

        return $o;
    }


    protected static function apply_params ($o, $id_name, $params)
    {
        if (!is_array ($params))
        {
            return;
        }

        foreach ($params as $k => $v)
        {
            if ($k == $id_name)
            {
                $k = 'id';
            }

            $pname = '_' . $k;
            if (property_exists ($o, $pname))
            {
                $o->$pname = $v;
            }
        }
    }

    public function save ()
    {
        if ($this->_id)
        {
            return $this->update ();
        }

        return $this->insert ();
    }


    protected function _insert ($table_name)
    {
        $data = $this->gather_data();

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ($db->insert($table_name, $data) === false)
        {
            return false;
        }

        $this->_id = $db->lastInsertId ();
        return true;
    }


    protected function _update ($table_name, $id_name)
    {
        $data = $this->gather_data();

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ($db->update($table_name, $data, "$id_name=" . $this->_id) === false)
        {
            return false;
        }

        return true;
    }


    protected function _delete ($table_name, $id_name)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ($db->delete($table_name, $id_name . " = " . (int)$this->_id) === false)
        {
            return false;
        }

        return true;
    }
}

